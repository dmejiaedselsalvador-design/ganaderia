<?php

use App\Http\Controllers\VentaGanadoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Módulo de Ventas de Ganado
|--------------------------------------------------------------------------
| Rutas con prefijo 'ventas' y nombre 'ventas.*' (definido en RouteServiceProvider)
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/', [VentaGanadoController::class, 'index'])->name('index');
    Route::get('/crear', [VentaGanadoController::class, 'create'])->name('create');
    Route::post('/store', [VentaGanadoController::class, 'store'])->name('store');
    Route::get('/{id}', [VentaGanadoController::class, 'show'])->name('show');
});
