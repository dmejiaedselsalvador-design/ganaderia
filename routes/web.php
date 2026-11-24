<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Ganadería Taurino
|--------------------------------------------------------------------------
| Rutas base: Dashboard principal, perfil de usuario y autenticación.
| Las rutas operativas se gestionan de forma modular por tema en:
| - routes/admin.php
| - routes/ganado.php
| - routes/proveedores.php
| - routes/corrales.php
| - routes/nutricion.php
| - routes/calidad_carne.php
| - routes/sanidad.php
| - routes/simulador.php
| - routes/ventas.php
*/

Route::middleware(['auth'])->group(function () {
    // Dashboard principal del rancho
    Route::get('/', [DashboardController::class, 'index'])->name('welcome');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil de Usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Preferencia de Tema Visual (Claro, Metal, Dark) en Caché
    Route::post('/cambiar-tema', [DashboardController::class, 'cambiarTema'])->name('tema.cambiar');
});

require __DIR__ . '/auth.php';
