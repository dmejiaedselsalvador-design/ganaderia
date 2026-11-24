<?php

use App\Http\Controllers\SimuladorEngordaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas del Simulador Financiero y Calculadora de Engorda
|--------------------------------------------------------------------------
| Proyecciones económicas en Pesos Mexicanos (MXN).
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/simulador-engorda', [SimuladorEngordaController::class, 'index'])->name('simulador.index');
});
