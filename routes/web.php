<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PricePredictionController;

// 1. Pantalla de Bienvenida por defecto
Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// Role-specific login routes
Route::get('/login/productor', [LoginController::class, 'showLoginForm'])->defaults('role', 'productor')->name('login.productor');
Route::post('/login/productor', [LoginController::class, 'login'])->defaults('role', 'productor')->name('login.productor.post');

Route::get('/login/comerciante', [LoginController::class, 'showLoginForm'])->defaults('role', 'comerciante')->name('login.comerciante');
Route::post('/login/comerciante', [LoginController::class, 'login'])->defaults('role', 'comerciante')->name('login.comerciante.post');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

//
// 2. Vista web del formulario
Route::get('/predecir', [PricePredictionController::class, 'index'])->name('prediccion.index');

// 3. CAMBIAR AQUÍ: De Route::get a Route::post para que acepte el envío del formulario
Route::post('/predecir-json', [PricePredictionController::class, 'generate'])->name('prediccion.calcular');
