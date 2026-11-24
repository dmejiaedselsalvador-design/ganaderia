<?php

use App\Http\Controllers\NutricionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas de Nutrición, Raciones y Consumos de Alimento
|--------------------------------------------------------------------------
| Prefijo: 'nutricion' y Nombre: 'nutricion.*'
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/', [NutricionController::class, 'index'])->name('index');
    Route::post('/racion', [NutricionController::class, 'storeRacion'])->name('racion.store');
    Route::post('/consumo', [NutricionController::class, 'storeConsumo'])->name('consumo.store');
});
