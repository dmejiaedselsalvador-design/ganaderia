<?php

use App\Http\Controllers\Construccion\ConstruccionDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Planta Construcción
|--------------------------------------------------------------------------
| Prefijo 'construccion', nombre 'construccion.*'
| Middleware aplicado en RouteServiceProvider: web, auth, division:construccion
*/

Route::get('/', [ConstruccionDashboardController::class, 'index'])->name('dashboard');

// Módulos en preparación (cada uno exige su permiso construccion.<modulo>)
Route::get('/modulo/{modulo}', [ConstruccionDashboardController::class, 'modulo'])
    ->whereIn('modulo', ['proyectos', 'presupuestos', 'materiales', 'personal'])
    ->name('modulo');
