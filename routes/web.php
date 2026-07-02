<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PricePredictionController;
use App\Http\Controllers\HistoryController;

// Raíz -> Dashboard (sin login)
Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/dashboard', function () {
    return view('dashboard');
});

// Vista del formulario de predicción
Route::get('/predecir', [PricePredictionController::class, 'index'])->name('prediccion.index');
Route::post('/predecir-json', [PricePredictionController::class, 'generate'])->name('prediccion.calcular');

// Historial de predicciones
Route::get('/historial', [HistoryController::class, 'index'])->name('prediccion.historial');
Route::get('/prediccion/{id}', [HistoryController::class, 'show'])->name('prediccion.detalle');
