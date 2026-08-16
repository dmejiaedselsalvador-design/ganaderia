<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Models\Ganado\FacturaGanado;
use App\Models\Ganado\ProveedorGanado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;



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

public function liquidar($id)
{
    // 1. Buscamos la factura y cargamos la relación con el proveedor
    $factura = FacturaGanado::with(['proveedor', 'animales'])->findOrFail($id);

    $proveedor = $factura->proveedor;

    // 2. Protegemos los adelantos asegurándonos de que el proveedor y la relación existan
    $totalAdelantos = 0;
    if ($proveedor && $proveedor->adelantos) {
        $totalAdelantos = $proveedor->adelantos->sum('dinero');
    }

    // 3. Sumamos los animales de la factura de forma segura
    $totalGanado = $factura->animales ? $factura->animales->sum('precioGanadoTotal') : 0;

    $saldoFinal = $totalAdelantos - $totalGanado;
        $montoAbsoluto = abs($saldoFinal);

    return view('proveedores.facturas.liquidar', compact('factura', 'proveedor', 'totalAdelantos', 'totalGanado', 'saldoFinal','montoAbsoluto'));
}

public function generarPdf($id)
{
    $factura = FacturaGanado::with(['proveedor', 'animales'])->findOrFail($id);
    $proveedor = $factura->proveedor;

    $totalAdelantos = $proveedor && $proveedor->adelantos ? $proveedor->adelantos->sum('dinero') : 0;
    $totalGanado = $factura->animales ? $factura->animales->sum('precioGanadoTotal') : 0;
    $saldoFinal = $totalAdelantos - $totalGanado;
    $montoAbsoluto = abs($saldoFinal);



    // Cargamos una vista específica para el PDF (o puedes usar la misma adaptada)
    $pdf = Pdf::loadView('reportes.liquidacionProveedores', compact('factura', 'proveedor', 'totalAdelantos', 'totalGanado', 'saldoFinal','montoAbsoluto'));

    // Opciones de papel (opcional: 'letter' o 'a4')
    $pdf->setPaper('letter', 'portrait');

    // Descargar el PDF directamente:
  //  return $pdf->download('liquidacion-factura-' . $factura->id . '.pdf');

    // O si prefieres mostrarlo en el navegador en lugar de descargarlo de golpe:
     return $pdf->stream('liquidacion-factura-' . $factura->id . '.pdf');
}

}
