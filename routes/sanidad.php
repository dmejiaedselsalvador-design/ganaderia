<?php

use App\Http\Controllers\SanidadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas de Sanidad, Bioseguridad y Periodos de Retiro
|--------------------------------------------------------------------------
| Prefijo: 'sanidad' y Nombre: 'sanidad.*'
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/', [SanidadController::class, 'index'])->name('index');
    Route::post('/', [SanidadController::class, 'store'])->name('store');
});
