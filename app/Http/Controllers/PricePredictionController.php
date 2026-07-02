<?php

namespace App\Http\Controllers;

use App\Services\GeminiPredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PricePredictionController extends Controller
{
    protected GeminiPredictionService $geminiService;

    public function __construct(GeminiPredictionService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    // Sirve para pintar la interfaz visual.
    // FIX: ahora traemos los productos y mercados reales de la base de datos
    // y se los pasamos a la vista, en vez de tenerlos hardcodeados en el Blade
    // con slugs que no coincidían con el seeder.
    public function index()
    {
        $productos = DB::table('products')
            ->orderBy('name')
            ->get(['id', 'name']);

        $mercados = DB::table('market_points')
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('predecir', compact('productos', 'mercados'));
    }

    // FUNCIÓN DE PROCESAMIENTO: recibe directamente los IDs reales que el
    // usuario seleccionó, sin ningún mapeo de strings intermedio.
    public function generate(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => ['required', 'integer', 'exists:products,id'],
                'market_point_id' => ['required', 'integer', 'exists:market_points,id'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Producto o mercado inválido: ' . $e->getMessage(),
            ], 422);
        }

        $productId = $validated['product_id'];
        $marketPointId = $validated['market_point_id'];

        try {
            $analysis = $this->geminiService->predictPrice($productId, $marketPointId);

            DB::table('price_predictions')->insertGetId([
                'product_id' => $productId,
                'market_point_id' => $marketPointId,
                'predicted_price' => $analysis['prediction']['predicted_price'],
                'confidence_score' => $analysis['prediction']['confidence_score'],
                'scenario_input' => json_encode($analysis['scenario_input']),
                'model_explanation' => json_encode($analysis['prediction']['explanation']),
                'valid_for_date' => now()->addDay()->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json($analysis['prediction']); // Enviamos el JSON plano

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}