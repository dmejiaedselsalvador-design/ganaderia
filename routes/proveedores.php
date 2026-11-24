<?php

use App\Http\Controllers\AdelantoController;
use App\Http\Controllers\Proveedor\DeudoresProveedorController;
use App\Http\Controllers\Proveedor\FacturasProveedorController;
use App\Http\Controllers\ProveedorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas de Proveedores, Guías de Compra, Deudores y Adelantos
|--------------------------------------------------------------------------
| Gestión comercial de vendedores de ganado, liquidaciones y anticipos.
*/

Route::middleware(['auth'])->group(function () {
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
        Route::get('/', [AdelantoController::class, 'index'])->name('adelantos.index');
        Route::get('/proveedores', [AdelantoController::class, 'index'])->name('adelantos.proveedores.general');
        Route::get('/crear/{proveedor?}', [AdelantoController::class, 'create'])->name('adelantos.crear');
        Route::get('/proveedores/{proveedor}', [AdelantoController::class, 'create'])->name('adelantos.proveedores.index');
        Route::post('/store', [AdelantoController::class, 'store'])->name('adelantos.store');
        Route::post('/proveedores/{proveedor}', [AdelantoController::class, 'store'])->name('adelantos.proveedores.store');
        Route::post('/{id}/pagar', [AdelantoController::class, 'pagar'])->name('adelantos.pagar');
    });
});
