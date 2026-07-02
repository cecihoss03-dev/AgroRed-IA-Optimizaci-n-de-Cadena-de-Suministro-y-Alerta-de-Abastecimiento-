<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PricePredictionController;

// 1. Pantalla de Bienvenida por defecto
Route::get('/', function () {
    return view('welcome');
});

// 2. Vista web del formulario
Route::get('/predecir', [PricePredictionController::class, 'index'])->name('prediccion.index');

// 3. CAMBIAR AQUÍ: De Route::get a Route::post para que acepte el envío del formulario
Route::post('/predecir-json', [PricePredictionController::class, 'generate'])->name('prediccion.calcular');