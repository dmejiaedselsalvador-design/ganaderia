<?php

use App\Http\Controllers\AdelantoController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;


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

Route::get('/', function () {
    return view('welcome');
})->middleware(['auth','role:admin'])->name('welcome');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/crear-usuario', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/admin/crear-usuario', [RegisteredUserController::class, 'store'])->name('store');
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
    Route::get('proveedores/factura/{id}/liqudar',[FacturasProveedorController::class, 'liquidar'])->name('proveedores.facturas.liquidar');
     Route::get('proveedores/factura/{id}/liqudar/pdf',[FacturasProveedorController::class, 'generarPdf'])->name('proveedores.facturas.liquidar.generarPdf');

    Route::get('proveedores/facturas/crear',[FacturasProveedorController::class, 'crearFactura'])->name('proveedores.facturas.crear');
    Route::post('proveedores/facturas/store',[FacturasProveedorController::class, 'storeFactura'])->name('proveedores.facturas.ganado.store');
   // Route::get('proveedores/facturas/editar/{id}',[FacturasProveedor


  Route::get('/adelantos/proveedores/{proveedor}',[AdelantoController::class,'create'])->name('adelantos.proveedores.index');
  Route::post('/adelantos/proveedores/{proveedor}',[AdelantoController::class,'store'])->name('adelantos.proveedores.store');
});



require __DIR__.'/auth.php';
