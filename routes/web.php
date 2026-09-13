<?php

use App\Http\Controllers\AdelantoController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Proveedor\DeudoresProveedorController;




use App\Http\Controllers\Proveedor\FacturasProveedorController;
use App\Http\Controllers\ProveedorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/




Route::middleware('auth')->group(function () {
    Route::get('/',[DashboardController::class,'index' ])->name('welcome');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/admin/crear-usuario', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/admin/crear-usuario', [RegisteredUserController::class, 'store'])->name('store');
    Route::get('/admin/lista-usuarios', [ProfileController::class, 'index'])->name('usuarios.lista');

});

Route::middleware(['auth'])->group(function () {
    Route::get('/compras/registro-ganado', [AnimalController::class, 'index'])->name('compras.ganado.index');
    Route::get('/compras/nuevo-ganado',[AnimalController::class,'nuevoGanado'])->name('compras.nuevo.ganado');
    Route::get('/ganado/perfil', [AnimalController::class, 'perfil'])->name('compras.ganado.perfil');
    Route::post('compras/registro-ganado',[AnimalController::class, 'store'])->name('compras.ganado.store');

    Route::get('proveedores',[ProveedorController::class,'index'])->name('proveedores.index');
    Route::get('proveedores/create',[ProveedorController::class, 'create'])->name('proveedor.create');
    Route::post('proveedores',[ProveedorController::class, 'store'])->name('proveedor.nuevo');
    Route::get('proveedor/edit/{id}',[ProveedorController::class, 'edit'])->name('proveedor.editar');
    Route::put('proveedor/update/{id}', [ProveedorController::class, 'update'])->name('proveedor.update');
    Route::get('proveedores/facturas',[FacturasProveedorController::class, 'index'])->name('proveedores.facturas.index');
    Route::get('proveedores/factura/{id}/liquidar',[FacturasProveedorController::class, 'liquidar'])->name('proveedores.facturas.liquidar');
     Route::get('proveedores/factura/{id}/liquidar/pdf',[FacturasProveedorController::class, 'generarPdf'])->name('proveedores.facturas.liquidar.generarPdf');
     Route::post('proveedor/factura/liquidar/{id}/pago',[FacturasProveedorController::class, 'liquidarFactura'])->name('proveedores.facturas.liquidar.pago');

    Route::get('proveedores/facturas/crear',[FacturasProveedorController::class, 'crearFactura'])->name('proveedores.facturas.crear');
    Route::post('proveedores/facturas/store',[FacturasProveedorController::class, 'storeFactura'])->name('proveedores.facturas.ganado.store');
   // Route::get('proveedores/facturas/editar/{id}',[FacturasProveedor
   Route::get('proveedores/deudores/lista',[DeudoresProveedorController::class, 'index'])->name('provedores.deudores.lista');



  Route::get('/adelantos/proveedores/{proveedor}',[AdelantoController::class,'create'])->name('adelantos.proveedores.index');
  Route::post('/adelantos/proveedores/{proveedor}',[AdelantoController::class,'store'])->name('adelantos.proveedores.store');
});



require __DIR__.'/auth.php';
