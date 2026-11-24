<?php

use App\Http\Controllers\AnimalController;
use App\Http\Controllers\BajaGanadoController;
use App\Http\Controllers\PesajeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas del Hato Ganadero, Expedientes, Pesajes y Bajas
|--------------------------------------------------------------------------
| Control individual de aretes, bitácoras de pesajes (GDP) y defunciones.
*/

Route::middleware(['auth'])->group(function () {
    // Módulo de Ganado e Ingresos
    Route::prefix('compras')->group(function () {
        Route::get('/registro-ganado', [AnimalController::class, 'index'])->name('compras.ganado.index');
        Route::get('/nuevo-ganado', [AnimalController::class, 'nuevoGanado'])->name('compras.nuevo.ganado');
        Route::post('/registro-ganado', [AnimalController::class, 'store'])->name('compras.ganado.store');
    });

    // Expediente del Animal
    Route::get('/ganado/perfil', [AnimalController::class, 'perfil'])->name('compras.ganado.perfil');

    // Control de Pesajes del Animal (GDP)
    Route::post('/ganado/pesaje', [PesajeController::class, 'store'])->name('ganado.pesaje.store');
    Route::get('/ganado/{id}/pesajes/historial', [PesajeController::class, 'historial'])->name('ganado.pesaje.historial');

    // Registro de Bajas y Mortalidad
    Route::get('/ganado/bajas', [BajaGanadoController::class, 'index'])->name('ganado.bajas.index');
    Route::post('/ganado/bajas', [BajaGanadoController::class, 'store'])->name('ganado.bajas.store');
});
