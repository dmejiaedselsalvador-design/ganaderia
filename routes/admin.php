<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas de Administración y Usuarios
|--------------------------------------------------------------------------
| Gestión de roles, usuarios del sistema y permisos de acceso.
*/

Route::middleware(['auth', 'role:admin|super-admin'])->prefix('admin')->group(function () {
    Route::get('/crear-usuario', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/crear-usuario', [RegisteredUserController::class, 'store'])->name('store');
    Route::get('/lista-usuarios', [ProfileController::class, 'index'])->name('usuarios.lista');
});
