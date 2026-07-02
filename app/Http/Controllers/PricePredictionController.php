<?php

namespace App\Http\Controllers;

use App\Services\GeminiPredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PricePredictionController extends Controller
{
    protected GeminiPredictionService $geminiService;

    public function __construct(GeminiPredictionService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    // NUEVA FUNCIÓN: Sirve para pintar la interfaz visual
    public function index()
    {
        return view('predecir');
    }

    // FUNCIÓN DE PROCESAMIENTO: Ahora recibe parámetros de AJAX de forma dinámica
    public function generate(Request $request)
{
    // AHORA recibimos los nombres exactos que envía el JS (producto y mercado)
    $productoNombre = $request->input('producto'); 
    $mercadoNombre = $request->input('mercado');

    // Mapeo simple: Si tu base de datos requiere IDs numéricos, 
    // debes convertir el nombre (ej: "papa_imilla") a ID aquí.
    // Ejemplo rápido (ajústalo según tus IDs reales):
    $mapeoProductos = ['papa_imilla' => 1, 'tomate_perita' => 2, 'mani_colorado' => 3];
    $mapeoMercados = ['mercado_campesino' => 1, 'mayorista_el_morro' => 2];

    $productId = $mapeoProductos[$productoNombre] ?? 1;
    $marketPointId = $mapeoMercados[$mercadoNombre] ?? 1;

    try {
        $analysis = $this->geminiService->predictPrice($productId, $marketPointId);

        // Guardar en DB (tu lógica actual está bien)
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