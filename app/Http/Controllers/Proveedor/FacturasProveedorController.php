<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Models\Ganado\FacturaGanado;
use App\Models\Ganado\ProveedorGanado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacturasProveedorController extends Controller
{
    //
    public function index()
    {
        $proveedores = FacturaGanado::with('proveedorData')
        ->withCount('animales as cantidad_ganado')
        ->get();
       // $proveedores = ProveedorGanado::all();
        return view('proveedores.facturas.facturasProveedor', compact('proveedores'));
    }
      public function crearFactura()
    {
       $proveedores = ProveedorGanado::all();

        return view('proveedores.facturas.FacturaCreate',compact('proveedores'));
    }

    public function storeFactura(Request $request)
{


    // 1. Validar los datos generales del formulario y el array de animales
    $request->validate([
        'proveedor_id'          => 'required|exists:proveedorGanado,id',
        'fecha_factura'        => 'required|date',
        'factura'       => 'required|string|max:255',
        'observaciones'              => 'string|nullable|max:255',

    ]);





    // 2. Proceso de guardado (ejemplo usando una transacción)
    \DB::beginTransaction();
    try {
        // Crear el lote o la compra general de ganado según tu estructura de BD
        // ...


    $factura = FacturaGanado::create([
            'proveedorID'   => $request->proveedor_id,
            'fechaFactura'  => $request->fecha_factura,
            'numeroFactura' => $request->factura,
            'estado'        => 'proceso',
        ]);


//$proveedor = ProveedorGanado::find($factura->proveedorID);

        DB::commit();

     //   return redirect()->route('compras.nuevo.ganado')
     return redirect()->route('compras.nuevo.ganado', ['factura_id' => $factura->id])
        ->with('success', 'Factura creada exitosamente. Ahora puedes registrar el ganado asociado a esta factura.');


   } catch (\Exception $e) {
    \DB::rollBack();

    // Registramos el error real en los logs del servidor para ti
    \Log::error('Error al registrar factura de ganado: ' . $e->getMessage());

    // Verificamos si es un error de entrada duplicada (Código SQLSTATE 23000 o código de error 1062)
    if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
        return back()
            ->withErrors(['error' => 'El número de factura o recibo ya se encuentra registrado en el sistema. Por favor verifica el número e intenta de nuevo.'])
            ->withInput();
    }

    // Para cualquier otro error inesperado
    return back()
        ->withErrors(['error' => 'Ocurrió un error inesperado al procesar la factura. Por favor, intenta de nuevo.'])
        ->withInput();
}
}
}
