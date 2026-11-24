<?php

use App\Http\Controllers\CorralLoteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas de Corrales y Lotes de Engorda (Feedlot Management)
|--------------------------------------------------------------------------
| Prefijo: 'corrales' y Nombre: 'corrales.*'
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/', [CorralLoteController::class, 'index'])->name('index');
    Route::post('/', [CorralLoteController::class, 'store'])->name('store');
    Route::get('/{id}', [CorralLoteController::class, 'show'])->name('show');
    Route::post('/{id}/asignar', [CorralLoteController::class, 'asignarGanado'])->name('asignar.ganado');
    Route::post('/{corralId}/remover/{ganadoId}', [CorralLoteController::class, 'removerGanado'])->name('remover.ganado');
});
