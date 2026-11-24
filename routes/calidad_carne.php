<?php

use App\Http\Controllers\CalidadCarneController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas de Calidad de Carne y Tipificación en Canal
|--------------------------------------------------------------------------
| Metodología de la Dra. María Salud Rubio Lozano (UNAM / Engormix)
| Prefijo: 'calidad-carne' y Nombre: 'calidad.carne.*'
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/', [CalidadCarneController::class, 'index'])->name('index');
    Route::post('/', [CalidadCarneController::class, 'store'])->name('store');
});
