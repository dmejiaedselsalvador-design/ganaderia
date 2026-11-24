<?php

use App\Http\Controllers\AdelantoController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BajaGanadoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PesajeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Proveedor\DeudoresProveedorController;
use App\Http\Controllers\Proveedor\FacturasProveedorController;
use App\Http\Controllers\ProveedorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Ganadería Taurino
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Dashboard & Perfil
    Route::get('/', [DashboardController::class, 'index'])->name('welcome');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Administración de Usuarios (Roles)
    Route::middleware(['role:admin|super-admin'])->prefix('admin')->group(function () {
        Route::get('/crear-usuario', [RegisteredUserController::class, 'create'])->name('register');
        Route::post('/crear-usuario', [RegisteredUserController::class, 'store'])->name('store');
        Route::get('/lista-usuarios', [ProfileController::class, 'index'])->name('usuarios.lista');
    });

    // Módulo de Ganado y Compras
    Route::prefix('compras')->group(function () {
        Route::get('/registro-ganado', [AnimalController::class, 'index'])->name('compras.ganado.index');
        Route::get('/nuevo-ganado', [AnimalController::class, 'nuevoGanado'])->name('compras.nuevo.ganado');
        Route::post('/registro-ganado', [AnimalController::class, 'store'])->name('compras.ganado.store');
    });

    Route::get('/ganado/perfil', [AnimalController::class, 'perfil'])->name('compras.ganado.perfil');

    // Control de Pesajes del Animal (GDP)
    Route::post('/ganado/pesaje', [PesajeController::class, 'store'])->name('ganado.pesaje.store');
    Route::get('/ganado/{id}/pesajes/historial', [PesajeController::class, 'historial'])->name('ganado.pesaje.historial');

    // Registro de Bajas y Mortalidad
    Route::get('/ganado/bajas', [BajaGanadoController::class, 'index'])->name('ganado.bajas.index');
    Route::post('/ganado/bajas', [BajaGanadoController::class, 'store'])->name('ganado.bajas.store');

    // Módulo de Proveedores
    Route::prefix('proveedores')->group(function () {
        Route::get('/', [ProveedorController::class, 'index'])->name('proveedores.index');
        Route::get('/create', [ProveedorController::class, 'create'])->name('proveedor.create');
        Route::post('/', [ProveedorController::class, 'store'])->name('proveedor.nuevo');
        Route::get('/edit/{id}', [ProveedorController::class, 'edit'])->name('proveedor.editar');
        Route::put('/update/{id}', [ProveedorController::class, 'update'])->name('proveedor.update');

        // Facturas y Liquidaciones de Proveedores
        Route::prefix('facturas')->group(function () {
            Route::get('/', [FacturasProveedorController::class, 'index'])->name('proveedores.facturas.index');
            Route::get('/crear', [FacturasProveedorController::class, 'crearFactura'])->name('proveedores.facturas.crear');
            Route::post('/store', [FacturasProveedorController::class, 'storeFactura'])->name('proveedores.facturas.ganado.store');
        });

        Route::get('/factura/{id}/liquidar', [FacturasProveedorController::class, 'liquidar'])->name('proveedores.facturas.liquidar');
        Route::post('/factura/liquidar/{id}/pago', [FacturasProveedorController::class, 'liquidarFactura'])->name('proveedores.facturas.liquidar.pago');
        Route::get('/factura/{id}/liquidar/exito', [FacturasProveedorController::class, 'liquidarExito'])->name('proveedores.facturas.liquidar.exito');
        Route::get('/factura/{id}/liquidar/pdf', [FacturasProveedorController::class, 'generarPdf'])->name('proveedores.facturas.liquidar.generarPdf');

        // Deudores
        Route::get('/deudores/lista', [DeudoresProveedorController::class, 'index'])
            ->name('proveedores.deudores.lista');
        // Alias para compatibilidad con vistas históricas que usan 'provedores.*' (con una sola 'e')
        Route::get('/deudores/lista-alias', [DeudoresProveedorController::class, 'index'])
            ->name('provedores.deudores.lista');
    });

    // Módulo de Adelantos y Préstamos
    Route::prefix('adelantos')->group(function () {
        Route::get('/proveedores', [AdelantoController::class, 'index'])->name('adelantos.index');
        Route::get('/proveedores/{proveedor}', [AdelantoController::class, 'create'])->name('adelantos.proveedores.index');
        Route::post('/proveedores/{proveedor}', [AdelantoController::class, 'store'])->name('adelantos.proveedores.store');
    });
});

require __DIR__ . '/auth.php';
