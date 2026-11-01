<?php
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
   // return view('ventas.index');
   return response()->json(['message' => 'Bienvenido a la pagina de ventas']);
})->name('ventas.index');
