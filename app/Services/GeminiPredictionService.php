<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class GeminiPredictionService
{
    protected string $apiKey;
    protected string $endpoint;

    public function __construct()
    {
        // Trae la API Key que guardamos en config/services.php
        $this->apiKey = config('services.gemini.key') ?? '';

        if (empty($this->apiKey)) {
            throw new Exception(
                'GEMINI_API_KEY no está configurada. ' .
                'Por favor, configura tu clave API en el archivo .env'
            );
        }

        // FIX: gemini-1.5-flash-latest fue retirado por Google (error 404 NOT_FOUND).
        // El modelo vigente equivalente en la familia actual es gemini-2.5-flash.
        // Puedes sobreescribirlo en config/services.php -> 'gemini.model' si Google
        // vuelve a rotar los nombres, sin tener que tocar este servicio.
        $model = config('services.gemini.model', 'gemini-2.5-flash');
        $this->endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
    }

    public function predictPrice(int $productId, int $marketPointId): array
    {
        // 1. Buscar los nombres del Producto y del Mercado (ej: Papa Imilla en El Morro)
        $product = DB::table('products')->where('id', $productId)->first();
        $market = DB::table('market_points')->where('id', $marketPointId)->first();

        if (!$product || !$market) {
            throw new Exception("Producto o Punto de Mercado no encontrado en la base de datos.");
        }

        // 2. Verificar si hay bloqueos de carreteras activos hoy
        $activeBlockages = DB::table('road_blockages')
            ->join('access_routes', 'road_blockages.access_route_id', '=', 'access_routes.id')
            ->whereNull('road_blockages.resolved_at')
            ->select('road_blockages.segment_name', 'road_blockages.cause', 'road_blockages.severity', 'access_routes.name as route_name')
            ->get();

        // 3. Obtener los últimos precios registrados de ese producto en ese mercado
        $priceHistory = DB::table('price_history')
            ->where('product_id', $productId)
            ->where('market_point_id', $marketPointId)
            ->orderBy('recorded_date', 'desc')
            ->limit(5)
            ->get();

        // 4. Juntar todo en un array limpio (contexto para la IA)
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
                'has_active_blockages' => $activeBlockages->isNotEmpty(),
                'active_events' => $activeBlockages,
            ],
            'recent_price_history' => $priceHistory,
        ];

        // 5. Redactar el prompt con el contexto boliviano
        $promptText = "Eres un analista de mercado agrícola en Sucre, Bolivia. 
        Tu tarea es predecir el precio para mañana.
       DATOS DISPONIBLES:
       - Producto: {$product->name}
       - Mercado: {$market->name}
       - ¿Hay bloqueos o derrumbes?: " . ($activeBlockages->isNotEmpty() ? "SÍ. Detalles: " . json_encode($activeBlockages) : "NO hay bloqueos o derrumbes reportados.") . "
       - Historial reciente: " . json_encode($priceHistory) . "

       INSTRUCCIONES ESTRICTAS:
       1. Si no hay bloqueos o derrumbes activos, basa tu predicción ÚNICAMENTE en el historial de precios reciente.
       2. Si hay bloqueos o derrumbes, explica cómo afectan específicamente a este producto y mercado.
       3. No inventes bloqueos ni situaciones que no estén en los DATOS DISPONIBLES arriba.
       4. Si los datos son insuficientes, sé honesto en la explicación.
       5. Responde solo en JSON." . json_encode($scenarioInput, JSON_PRETTY_PRINT);

        // 6. Enviar la petición HTTP a Gemini pidiendo un formato JSON estricto
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post("{$this->endpoint}?key={$this->apiKey}", [
                    // FIX: "contents" debe ser un ARRAY de objetos { role, parts },
                    // no un único objeto. La estructura anterior (sin el array
                    // envolvente y sin "role") es inválida para la REST API actual
                    // y puede provocar errores de payload silenciosos o 400.
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
                                    'description' => 'Breve análisis del impacto del bloqueo o derrumbe en Sucre.',
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
            Log::error('Gemini API error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'endpoint' => $this->endpoint,
            ]);
            throw new Exception("Error al conectar con Gemini: " . $response->body());
        }

        // 7. Decodificar la respuesta estructurada de la IA
        $rawText = $response->json('candidates.0.content.parts.0.text');

        if (empty($rawText)) {
            // Puede pasar si Gemini bloquea la respuesta por safety filters,
            // o si el modelo devolvió una estructura inesperada.
            $finishReason = $response->json('candidates.0.finishReason');
            Log::error('Gemini respondió sin texto utilizable', [
                'finish_reason' => $finishReason,
                'raw_response' => $response->json(),
            ]);
            throw new Exception("Gemini no devolvió contenido utilizable (finishReason: {$finishReason}).");
        }

        // Limpieza defensiva: aunque pedimos JSON puro vía responseMimeType,
        // dejamos el strip de ```json ... ``` por si el modelo los añade igual.
        $cleanJson = trim($rawText);
        $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $cleanJson);

        $resultData = json_decode($cleanJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('No se pudo decodificar el JSON de Gemini', [
                'raw_text' => $rawText,
                'json_error' => json_last_error_msg(),
            ]);
            throw new Exception("Gemini devolvió un JSON inválido: " . json_last_error_msg());
        }

        // Validación mínima de las claves esperadas por el controlador
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
}