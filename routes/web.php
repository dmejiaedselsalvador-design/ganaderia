<?php

use App\Http\Controllers\PlantaController;
use App\Http\Controllers\PreferenciasController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Grupo Taurino
|--------------------------------------------------------------------------
| Rutas comunes a todas las plantas: portal de entrada, selección/cambio de
| planta, perfil, preferencias (tema) y autenticación.
|
| Las rutas de cada planta se registran en App\Providers\RouteServiceProvider:
| - Ganadería:    routes/ganaderia.php, ganado.php, proveedores.php, corrales.php,
|                 nutricion.php, calidad_carne.php, sanidad.php, simulador.php, ventas.php
| - Construcción: routes/construccion.php
| - Administración (usuarios y roles): routes/admin.php
*/

// Preferencia de tema (también disponible en la pantalla de login, sin sesión)
Route::post('/preferencias/tema', [PreferenciasController::class, 'tema'])->name('tema.cambiar');

Route::middleware(['auth'])->group(function () {
    // Entrada principal: decide a qué planta enviar al usuario
    Route::get('/', [PlantaController::class, 'inicio'])->name('inicio');

    // Selector y cambio de planta activa
    Route::get('/plantas', [PlantaController::class, 'selector'])->name('plantas.selector');
    Route::post('/plantas/cambiar', [PlantaController::class, 'cambiar'])->name('plantas.cambiar');

    // Perfil de Usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
