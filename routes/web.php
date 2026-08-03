<?php

use App\Http\Controllers\AnimalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;

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
    Route::get('/compras/registro-ganado', [AnimalController::class, 'index'])->name('compras.index');

});

Route::get('/test-moneda', function () {
    // Probamos con una cantidad de ejemplo (ej. Venta de ganado)
    $montoEjemplo = 500;

    return formatoPesos($montoEjemplo);
});

require __DIR__.'/auth.php';
