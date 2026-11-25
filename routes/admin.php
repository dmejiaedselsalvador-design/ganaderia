<?php

use App\Http\Controllers\Admin\RoleController;
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
    Route::patch('/usuarios/{user}/estado', [UserController::class, 'toggleEstado'])->middleware('can:usuarios.baja')->name('usuarios.estado');
    Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->middleware('can:usuarios.eliminar')->name('usuarios.destroy');

    // Compatibilidad con enlaces anteriores
    Route::get('/lista-usuarios', fn () => redirect()->route('usuarios.index'))->name('usuarios.lista');
    Route::get('/crear-usuario', fn () => redirect()->route('usuarios.create'))->name('register');

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
