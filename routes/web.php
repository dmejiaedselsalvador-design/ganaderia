<?php

use App\Http\Controllers\AyudaController;
use App\Http\Controllers\Auth\CambioClaveController;
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
    // Cambio obligatorio de clave temporal (primer ingreso)
    Route::get('/cambiar-clave', [CambioClaveController::class, 'show'])->name('password.cambiar');
    Route::put('/cambiar-clave', [CambioClaveController::class, 'update'])->name('password.cambiar.guardar');

    // Entrada principal: decide a qué planta enviar al usuario
    Route::get('/', [PlantaController::class, 'inicio'])->name('inicio');

    // Selector y cambio de planta activa
    Route::get('/plantas', [PlantaController::class, 'selector'])->name('plantas.selector');
    Route::post('/plantas/cambiar', [PlantaController::class, 'cambiar'])->name('plantas.cambiar');

    // Perfil de Usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Cerrar mis sesiones en otros equipos (Mi perfil)
    Route::delete('/profile/sesiones', [\App\Http\Controllers\Admin\SesionController::class, 'cerrarMisOtras'])->name('profile.sesiones');

    // Barra lateral contraída / expandida (se recuerda en la sesión)
    Route::post('/preferencias/sidebar', [PreferenciasController::class, 'sidebar'])->name('preferencias.sidebar');

    // Botón de Ayuda (?): preguntas con captura de pantalla
    Route::post('/ayuda/preguntas', [AyudaController::class, 'store'])->middleware('throttle:10,1')->name('ayuda.store');
    Route::post('/ayuda/vistas', [AyudaController::class, 'marcarVistas'])->name('ayuda.vistas');
    Route::get('/ayuda/preguntas/{pregunta}/captura', [AyudaController::class, 'captura'])->name('ayuda.captura');

    // (Eliminación de la propia cuenta deshabilitada: solo administración puede eliminar usuarios)
});

require __DIR__ . '/auth.php';
