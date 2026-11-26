<?php

use App\Http\Controllers\Ganaderia\AtajoController;
use App\Http\Controllers\Ganaderia\CampoController;
use App\Http\Controllers\Ganaderia\ClienteController;
use App\Http\Controllers\Ganaderia\CorreccionController;
use App\Http\Controllers\Ganaderia\PesoObjetivoController;
use App\Http\Controllers\Ganaderia\CompraCostosController;
use App\Http\Controllers\Ganaderia\ConfiguracionController;
use App\Http\Controllers\Ganaderia\DocumentoController;
use App\Http\Controllers\Ganaderia\FichaGanadoController;
use App\Http\Controllers\Ganaderia\GastoController;
use App\Http\Controllers\Ganaderia\InventarioController;
use App\Http\Controllers\Ganaderia\PlanAlimentacionController;
use App\Http\Controllers\Ganaderia\ReporteController;
use App\Http\Controllers\Ganaderia\SanidadAvanzadaController;
use App\Http\Controllers\PesajeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Ganadería · Módulos de gestión profesional
|--------------------------------------------------------------------------
| Middleware aplicado en RouteServiceProvider: web, auth, division:ganaderia
| Cada grupo exige su permiso.
*/

// Ficha completa del animal, historial de corrales, fotos y carga masiva de pesajes
Route::middleware('can:ganaderia.ganado')->group(function () {
    Route::get('/ganado/{id}/ficha', [FichaGanadoController::class, 'show'])->name('ganado.ficha');
    Route::put('/ganado/{id}/ficha', [FichaGanadoController::class, 'update'])->name('ganado.ficha.update');
    Route::post('/ganado/{id}/mover', [FichaGanadoController::class, 'mover'])->name('ganado.mover');
    Route::post('/ganado/{id}/fotos', [FichaGanadoController::class, 'subirFoto'])->name('ganado.fotos.store');
    Route::get('/ganado/fotos/{foto}', [FichaGanadoController::class, 'verFoto'])->name('ganado.fotos.ver');
    Route::delete('/ganado/fotos/{foto}', [FichaGanadoController::class, 'eliminarFoto'])->name('ganado.fotos.destroy');
    Route::post('/ganado/pesajes/importar', [PesajeController::class, 'importar'])->name('ganado.pesajes.importar');
    Route::get('/campo', [CampoController::class, 'index'])->name('campo.index');
    Route::put('/ganado/pesos-objetivo', [PesoObjetivoController::class, 'update'])->name('ganado.pesos-objetivo');
});

// Costos completos de la guía de compra (flete, comisión, trámites, destare)
Route::middleware('can:ganaderia.proveedores')->group(function () {
    Route::get('/proveedores/factura/{id}/costos', [CompraCostosController::class, 'edit'])->name('compras.costos.edit');
    Route::put('/proveedores/factura/{id}/costos', [CompraCostosController::class, 'update'])->name('compras.costos.update');
});

// Corrección de registros mal capturados (motivo obligatorio, queda en la bitácora de correcciones)
Route::middleware('can:registros.corregir')->prefix('correcciones')->name('correcciones.')->group(function () {
    Route::get('/', [CorreccionController::class, 'index'])->name('index');
    Route::put('/pesajes/{pesaje}', [CorreccionController::class, 'pesaje'])->name('pesaje');
    Route::put('/servidos/{consumo}', [CorreccionController::class, 'servido'])->name('servido');
    Route::put('/tratamientos/{tratamiento}', [CorreccionController::class, 'tratamiento'])->name('tratamiento');
    Route::put('/bajas/{baja}', [CorreccionController::class, 'baja'])->name('baja');
    Route::put('/ventas/{venta}', [CorreccionController::class, 'venta'])->name('venta');
    Route::put('/compra/{id}', [CorreccionController::class, 'compra'])->name('compra');
});

// Alta rápida de catálogos desde cualquier formulario ("+ Nuevo")
Route::post('/atajos/{tipo}', [AtajoController::class, 'store'])->name('atajos.store');

// Documentos adjuntos (guías REEMO, certificados, CFDI...)
Route::post('/documentos', [DocumentoController::class, 'store'])->name('documentos.store');
Route::get('/documentos/{documento}', [DocumentoController::class, 'descargar'])->name('documentos.descargar');
Route::delete('/documentos/{documento}', [DocumentoController::class, 'destroy'])->name('documentos.destroy');

// Gastos operativos
Route::middleware('can:ganaderia.gastos')->prefix('gastos')->name('gastos.')->group(function () {
    Route::get('/', [GastoController::class, 'index'])->name('index');
    Route::post('/', [GastoController::class, 'store'])->name('store');
    Route::get('/{gasto}/editar', [GastoController::class, 'edit'])->name('edit');
    Route::put('/{gasto}', [GastoController::class, 'update'])->name('update');
    Route::post('/{gasto}/anular', [GastoController::class, 'anular'])->name('anular');
});

// Clientes y cobranza
Route::middleware('can:ganaderia.clientes')->group(function () {
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/crear', [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
    Route::get('/clientes/{cliente}', [ClienteController::class, 'show'])->name('clientes.show');
    Route::get('/clientes/{cliente}/editar', [ClienteController::class, 'edit'])->name('clientes.edit');
    Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');
    Route::get('/cobranza', [ClienteController::class, 'cobranza'])->name('cobranza.index');
    Route::post('/ventas/{venta}/abonos', [ClienteController::class, 'abonar'])->name('ventas.abonar');
    Route::post('/abonos/{abono}/anular', [ClienteController::class, 'anularAbono'])->name('abonos.anular');
});

// Inventario de alimento y medicamentos
Route::middleware('can:ganaderia.inventario')->prefix('inventario')->name('inventario.')->group(function () {
    Route::get('/', [InventarioController::class, 'index'])->name('index');
    Route::post('/', [InventarioController::class, 'store'])->name('store');
    Route::get('/{insumo}', [InventarioController::class, 'show'])->name('show');
    Route::put('/{insumo}', [InventarioController::class, 'update'])->name('update');
    Route::post('/{insumo}/movimientos', [InventarioController::class, 'movimiento'])->name('movimiento');
});
Route::middleware('can:ganaderia.nutricion')->group(function () {
    // Plan de alimentación basado en pesos (biomasa ⇒ % peso vivo ⇒ BOM ⇒ bodega)
    Route::get('/nutricion/planeacion', [PlanAlimentacionController::class, 'index'])->name('nutricion.planeacion');
    Route::get('/nutricion/plan/{corral}', [PlanAlimentacionController::class, 'show'])->name('nutricion.plan.corral');
    Route::post('/nutricion/plan/{corral}/dieta', [PlanAlimentacionController::class, 'asignar'])->name('nutricion.plan.asignar');
    Route::put('/nutricion/plan/{corral}/consumo', [PlanAlimentacionController::class, 'guardarPct'])->name('nutricion.plan.pct');
    Route::put('/nutricion/raciones/{racion}/consumo', [PlanAlimentacionController::class, 'consumoDieta'])->name('raciones.consumo.update');
    Route::get('/nutricion/raciones/{racion}/formula', [InventarioController::class, 'ingredientes'])->name('raciones.formula');
    Route::put('/nutricion/raciones/{racion}/formula', [InventarioController::class, 'guardarIngredientes'])->name('raciones.formula.update');
});

// Sanidad avanzada: calendario, aplicación masiva y casos de enfermedad
Route::middleware('can:ganaderia.sanidad')->prefix('sanidad')->name('sanidad.')->group(function () {
    Route::get('/calendario', [SanidadAvanzadaController::class, 'calendario'])->name('calendario');
    Route::post('/calendario', [SanidadAvanzadaController::class, 'programar'])->name('programar');
    Route::post('/calendario/{programacion}/aplicar', [SanidadAvanzadaController::class, 'aplicar'])->name('aplicar');
    Route::post('/calendario/{programacion}/cancelar', [SanidadAvanzadaController::class, 'cancelar'])->name('cancelar');
    Route::get('/enfermedades', [SanidadAvanzadaController::class, 'enfermedades'])->name('enfermedades');
    Route::post('/enfermedades', [SanidadAvanzadaController::class, 'storeCaso'])->name('casos.store');
    Route::put('/enfermedades/{caso}', [SanidadAvanzadaController::class, 'cerrarCaso'])->name('casos.update');
});

// Rentabilidad y reportes
Route::middleware('can:ganaderia.reportes')->prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/', [ReporteController::class, 'index'])->name('index');
    Route::get('/lotes', [ReporteController::class, 'lotes'])->name('lotes');
    Route::get('/animales', [ReporteController::class, 'animales'])->name('animales');
    Route::get('/inventario-ganado', [ReporteController::class, 'inventario'])->name('inventario');
});

// Configuración: precios de mercado, ubicaciones y metas por lote
Route::middleware('can:ganaderia.configuracion')->prefix('configuracion')->name('configuracion.')->group(function () {
    Route::get('/', [ConfiguracionController::class, 'index'])->name('index');
    Route::post('/precios', [ConfiguracionController::class, 'storePrecio'])->name('precios.store');
    Route::delete('/precios/{precio}', [ConfiguracionController::class, 'destroyPrecio'])->name('precios.destroy');
    Route::post('/ubicaciones', [ConfiguracionController::class, 'storeUbicacion'])->name('ubicaciones.store');
    Route::put('/ubicaciones/{ubicacion}', [ConfiguracionController::class, 'updateUbicacion'])->name('ubicaciones.update');
    Route::put('/corrales/{corral}/metas', [ConfiguracionController::class, 'updateMetas'])->name('metas.update');
});
