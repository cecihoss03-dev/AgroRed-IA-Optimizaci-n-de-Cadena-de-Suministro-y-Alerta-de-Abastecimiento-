<?php

namespace App\Http\Controllers;

use App\Services\GeminiPredictionService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected GeminiPredictionService $geminiService;

    public function __construct(GeminiPredictionService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function index()
    {
        $since = now()->subDays(7);

        // Estadísticas de los últimos 7 días
        $stats = [
            'avg_price' => (float) DB::table('price_predictions')
                ->where('created_at', '>=', $since)
                ->avg('predicted_price'),

            'avg_confidence' => (float) DB::table('price_predictions')
                ->where('created_at', '>=', $since)
                ->avg('confidence_score'),

            'predictions_count' => DB::table('price_predictions')
                ->where('created_at', '>=', $since)
                ->count(),

            'active_blockages' => DB::table('road_blockages')
                ->whereNull('resolved_at')
                ->count(),
        ];

        // Últimas 5 predicciones, con nombres de producto/mercado ya resueltos
        $recentPredictions = DB::table('price_predictions')
            ->join('products', 'products.id', '=', 'price_predictions.product_id')
            ->join('market_points', 'market_points.id', '=', 'price_predictions.market_point_id')
            ->select(
                'price_predictions.id',
                'products.name as product_name',
                'market_points.name as market_name',
                'price_predictions.predicted_price',
                'price_predictions.confidence_score',
                'price_predictions.created_at'
            )
            ->orderByDesc('price_predictions.created_at')
            ->limit(5)
            ->get();

        // Estado de rutas en tiempo real (búsqueda en vivo vía Gemini + Google Search).
        // Si falla, no tumbamos el dashboard: caemos a estado 'unknown'.
        try {
            $liveRoute = $this->geminiService->getDashboardRouteStatus();
        } catch (\Exception $e) {
            $liveRoute = [
                'status' => 'unknown',
                'label' => 'No se pudo consultar el estado de rutas en este momento.',
                'timestamp' => now()->format('d/m/Y H:i'),
                'sources' => [],
            ];
        }

        return view('dashboard', compact('stats', 'recentPredictions', 'liveRoute'));
    }
}