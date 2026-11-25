<?php

use App\Http\Controllers\AyudaController;
use App\Http\Controllers\Admin\AuditoriaController;
use App\Http\Controllers\Admin\DatosPruebaController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SesionController;
use App\Http\Controllers\Admin\SolicitudClaveController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Administración: Usuarios, Roles y Permisos (común a todas las plantas)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('admin')->group(function () {
    // Usuarios
    Route::get('/usuarios', [UserController::class, 'index'])->middleware('can:usuarios.ver')->name('usuarios.index');
    Route::get('/usuarios/crear', [UserController::class, 'create'])->middleware('can:usuarios.crear')->name('usuarios.create');
    Route::post('/usuarios', [UserController::class, 'store'])->middleware('can:usuarios.crear')->name('usuarios.store');
    Route::get('/usuarios/{user}/editar', [UserController::class, 'edit'])->middleware('can:usuarios.editar')->name('usuarios.edit');
    Route::put('/usuarios/{user}', [UserController::class, 'update'])->middleware('can:usuarios.editar')->name('usuarios.update');
    Route::post('/usuarios/{user}/clave-temporal', [UserController::class, 'claveTemporal'])->middleware('can:usuarios.editar')->name('usuarios.clave-temporal');
    Route::patch('/usuarios/{user}/estado', [UserController::class, 'toggleEstado'])->middleware('can:usuarios.baja')->name('usuarios.estado');
    Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->middleware('can:usuarios.eliminar')->name('usuarios.destroy');

    // Compatibilidad con enlaces anteriores
    Route::redirect('/lista-usuarios', '/admin/usuarios')->name('usuarios.lista');
    Route::redirect('/crear-usuario', '/admin/usuarios/crear')->name('register');

    // Solicitudes de cambio de clave (desde "¿Olvidó su contraseña?")
    Route::middleware('can:usuarios.solicitudes_clave')->group(function () {
        Route::get('/solicitudes-clave', [SolicitudClaveController::class, 'index'])->name('solicitudes.index');
        Route::post('/solicitudes-clave/{solicitud}/aprobar', [SolicitudClaveController::class, 'aprobar'])->name('solicitudes.aprobar');
        Route::post('/solicitudes-clave/{solicitud}/denegar', [SolicitudClaveController::class, 'denegar'])->name('solicitudes.denegar');
        Route::patch('/usuarios/{user}/bloqueo-solicitudes', [SolicitudClaveController::class, 'bloqueo'])->name('solicitudes.bloqueo');
    });

    // Preguntas enviadas desde el botón de Ayuda
    Route::middleware('can:ayuda.responder')->group(function () {
        Route::get('/preguntas', [AyudaController::class, 'index'])->name('ayuda.index');
        Route::put('/preguntas/{pregunta}', [AyudaController::class, 'responder'])->name('ayuda.responder');
        Route::patch('/preguntas/{pregunta}/cerrar', [AyudaController::class, 'cerrar'])->name('ayuda.cerrar');
    });

    // Sesiones activas: ver quién está conectado y expulsar (Súper Administrador y Dueño; se valida en el controlador)
    Route::get('/sesiones', [SesionController::class, 'index'])->name('sesiones.index');
    Route::delete('/sesiones/todas', [SesionController::class, 'expulsarTodos'])->name('sesiones.todas');
    Route::delete('/sesiones/usuario/{user}', [SesionController::class, 'expulsarUsuario'])->name('sesiones.usuario');
    Route::delete('/sesiones/{sesion}', [SesionController::class, 'expulsar'])->name('sesiones.expulsar');

    // Datos de prueba: cargar / eliminar la simulación (solo Súper Administrador, se valida en el controlador)
    Route::get('/datos-prueba', [DatosPruebaController::class, 'index'])->name('datos-prueba.index');
    Route::post('/datos-prueba', [DatosPruebaController::class, 'cargar'])->name('datos-prueba.cargar');
    Route::delete('/datos-prueba', [DatosPruebaController::class, 'eliminar'])->name('datos-prueba.eliminar');

    // Bitácora de auditoría
    Route::get('/auditoria', [AuditoriaController::class, 'index'])->middleware('can:auditoria.ver')->name('auditoria.index');

    // Roles y permisos
    Route::middleware('can:roles.gestionar')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/crear', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}/editar', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });
});
