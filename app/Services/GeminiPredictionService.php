<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class GeminiPredictionService
{
    protected string $apiKey;
    protected string $model;
    protected string $endpoint;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');

        // Modelo vigente (gemini-1.5-flash-latest fue retirado por Google).
        $this->model = config('services.gemini.model', 'gemini-2.5-flash');
        $this->endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";
    }

    public function predictPrice(int $productId, int $marketPointId): array
    {
        // 1. Buscar los nombres del Producto y del Mercado
        $product = DB::table('products')->where('id', $productId)->first();
        $market = DB::table('market_points')->where('id', $marketPointId)->first();

        if (!$product || !$market) {
            throw new Exception("Producto o Punto de Mercado no encontrado en la base de datos.");
        }

        // 2. Bloqueos manuales registrados en la base de datos (respaldo /
        // fuente administrable a mano, por ejemplo si un productor reporta
        // algo directamente en el sistema).
        $manualBlockages = DB::table('road_blockages')
            ->join('access_routes', 'road_blockages.access_route_id', '=', 'access_routes.id')
            ->whereNull('road_blockages.resolved_at')
            ->select(
                'road_blockages.segment_name',
                'road_blockages.cause',
                'road_blockages.severity',
                'road_blockages.reported_at',
                'access_routes.name as route_name'
            )
            ->get();

        // Nombres de las rutas de acceso relevantes, para orientar la búsqueda en vivo
        $routeNames = DB::table('access_routes')->pluck('name')->all();

        // 3. NUEVO: condiciones de ruta EN TIEMPO REAL vía Google Search grounding.
        // Esto reemplaza la dependencia exclusiva del bloqueo sembrado estático,
        // que nunca se marcaba como resuelto y por eso salía siempre en la respuesta.
        $liveConditions = $this->fetchLiveRoadConditions($routeNames, $market->name);

        // 4. Últimos precios registrados de ese producto en ese mercado
        $priceHistory = DB::table('price_history')
            ->where('product_id', $productId)
            ->where('market_point_id', $marketPointId)
            ->orderBy('recorded_date', 'desc')
            ->limit(5)
            ->get();

        // 5. Contexto completo para la IA
        $scenarioInput = [
            'analized_at' => now()->toIso8601String(),
            'product' => [
                'name' => $product->name,
                'category' => $product->category,
                'unit' => $product->unit_of_measure,
            ],
            'market' => [
                'name' => $market->name,
                'type' => $market->type,
            ],
            'logistics' => [
                'manual_reports' => [
                    'has_active_blockages' => $manualBlockages->isNotEmpty(),
                    'active_events' => $manualBlockages,
                ],
                'live_search_report' => $liveConditions,
            ],
            'recent_price_history' => $priceHistory,
        ];

        // 6. Prompt final: combina datos de la BD + hallazgos de búsqueda en vivo
        $promptText = "Actúa como un analista experto en economía agrícola y logística de Chuquisaca, Bolivia. " .
            "Analiza el siguiente escenario de mercado y predice el precio para mañana.\n\n" .
            "IMPORTANTE sobre las fuentes de bloqueos:\n" .
            "- 'manual_reports' son reportes cargados manualmente en el sistema (pueden estar desactualizados).\n" .
            "- 'live_search_report' es el resultado de una búsqueda web en tiempo real (fecha: {$liveConditions['searched_at']}) " .
            "sobre bloqueos, derrumbes o cortes de ruta ACTUALES hacia Sucre. Dale más peso a esta fuente por ser más reciente.\n" .
            "- Si 'live_search_report' indica que NO hay bloqueos activos, no inventes ni asumas bloqueos antiguos como si siguieran vigentes.\n" .
            "- Si ambas fuentes están vacías o sin novedades, asume condiciones normales de logística y bájale la confianza de la predicción en consecuencia.\n\n" .
            "Datos del escenario actual en formato JSON:\n" . json_encode($scenarioInput, JSON_PRETTY_PRINT);

        // 7. Petición estructurada a Gemini (sin tools, con responseSchema)
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post("{$this->endpoint}?key={$this->apiKey}", [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $promptText],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'responseSchema' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'predicted_price' => [
                                    'type' => 'NUMBER',
                                    'description' => 'Precio estimado en Bolivianos (Bs).',
                                ],
                                'confidence_score' => [
                                    'type' => 'NUMBER',
                                    'description' => 'Confianza del modelo de 0.00 a 1.00.',
                                ],
                                'explanation' => [
                                    'type' => 'STRING',
                                    'description' => 'Breve análisis del impacto logístico actual en Sucre.',
                                ],
                            ],
                            'required' => ['predicted_price', 'confidence_score', 'explanation'],
                        ],
                    ],
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new Exception("No se pudo conectar con Gemini (timeout o red): " . $e->getMessage());
        }

        if ($response->failed()) {
            Log::error('Gemini API error (predictPrice)', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception("Error al conectar con Gemini: " . $response->body());
        }

        $rawText = $response->json('candidates.0.content.parts.0.text');

        if (empty($rawText)) {
            $finishReason = $response->json('candidates.0.finishReason');
            Log::error('Gemini respondió sin texto utilizable (predictPrice)', [
                'finish_reason' => $finishReason,
                'raw_response' => $response->json(),
            ]);
            throw new Exception("Gemini no devolvió contenido utilizable (finishReason: {$finishReason}).");
        }

        $cleanJson = trim($rawText);
        $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $cleanJson);

        $resultData = json_decode($cleanJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('No se pudo decodificar el JSON de Gemini (predictPrice)', [
                'raw_text' => $rawText,
                'json_error' => json_last_error_msg(),
            ]);
            throw new Exception("Gemini devolvió un JSON inválido: " . json_last_error_msg());
        }

        foreach (['predicted_price', 'confidence_score', 'explanation'] as $key) {
            if (!array_key_exists($key, $resultData)) {
                throw new Exception("La respuesta de Gemini no incluye el campo requerido '{$key}'.");
            }
        }

        return [
            'scenario_input' => $scenarioInput,
            'prediction' => $resultData,
        ];
    }

    /**
     * Consulta a Gemini con la herramienta google_search activada para obtener
     * condiciones de ruta EN TIEMPO REAL (bloqueos, derrumbes, cortes) hacia
     * el mercado destino. No usa responseSchema porque Gemini 2.5 no permite
     * combinar tools con salida estructurada en la misma llamada.
     *
     * Se cachea por 15 minutos para no disparar una búsqueda nueva en cada
     * clic y mantener el costo/latencia bajo control.
     */
    protected function fetchLiveRoadConditions(array $routeNames, string $marketName): array
    {
        $cacheKey = 'live_road_conditions:' . md5(implode('|', $routeNames) . '|' . $marketName);

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($routeNames, $marketName) {
            $rutas = implode(', ', $routeNames) ?: 'las principales rutas de acceso a Sucre';
            $today = now()->format('d/m/Y');

            $searchPrompt = "Busca noticias de HOY ({$today}) sobre bloqueos de carreteras, derrumbes, paros o " .
                "cortes de ruta que afecten el acceso a la ciudad de Sucre, Chuquisaca, Bolivia, " .
                "especialmente en estas rutas: {$rutas}, con destino final {$marketName}. " .
                "Responde en texto plano y breve (máximo 5 líneas): indica si hay bloqueos activos ahora mismo, " .
                "en qué tramo, la causa si se conoce, y desde cuándo. Si no encuentras ninguna noticia reciente " .
                "de bloqueos, dilo explícitamente diciendo que no se detectaron bloqueos activos.";

            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])
                    ->timeout(30)
                    ->post("{$this->endpoint}?key={$this->apiKey}", [
                        'contents' => [
                            [
                                'role' => 'user',
                                'parts' => [
                                    ['text' => $searchPrompt],
                                ],
                            ],
                        ],
                        'tools' => [
                            ['google_search' => new \stdClass()],
                        ],
                    ]);
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::warning('No se pudo obtener condiciones de ruta en vivo (timeout/red)', [
                    'error' => $e->getMessage(),
                ]);

                return $this->emptyLiveConditions('No se pudo consultar la búsqueda en tiempo real (error de red).');
            }

            if ($response->failed()) {
                Log::warning('Gemini API error (fetchLiveRoadConditions)', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                // No tumbamos toda la predicción si falla solo la búsqueda en vivo;
                // seguimos con el resto del contexto disponible.
                return $this->emptyLiveConditions('No se pudo consultar la búsqueda en tiempo real (error de API).');
            }

            $summaryText = $response->json('candidates.0.content.parts.0.text') ?? '';

            // Extraer las fuentes citadas por el grounding, si Google las devuelve
            $sources = [];
            $chunks = $response->json('candidates.0.groundingMetadata.groundingChunks') ?? [];
            foreach ($chunks as $chunk) {
                if (!empty($chunk['web']['uri'])) {
                    $sources[] = [
                        'title' => $chunk['web']['title'] ?? null,
                        'uri' => $chunk['web']['uri'],
                    ];
                }
            }

            return [
                'searched_at' => now()->toIso8601String(),
                'summary' => trim($summaryText) ?: 'Sin información disponible en la búsqueda en tiempo real.',
                'sources' => $sources,
            ];
        });
    }

    protected function emptyLiveConditions(string $reason): array
    {
        return [
            'searched_at' => now()->toIso8601String(),
            'summary' => $reason,
            'sources' => [],
        ];
    }
}