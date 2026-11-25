<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Planta Ganadería - Dashboard
|--------------------------------------------------------------------------
| Middleware aplicado en RouteServiceProvider: web, auth, division:ganaderia
*/

Route::get('/ganaderia', [DashboardController::class, 'index'])->name('welcome');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
