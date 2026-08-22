<?php

namespace App\Http\Controllers;

use App\Models\Ganado\ProveedorGanado;
use App\Models\Ganado\FacturaGanado;
use App\Models\Ganado\Ganado;
use App\Models\Ganado\Adelanto;
use App\Models\Ganado\AdelantoFactura;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AnimalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
     $ganados =   Ganado::where('status','=','Activo')->get();
        return view('animals.index',compact('ganados'));
    }

    public function perfil()
    {
        return view('animals.perfil');
    }



    public function nuevoGanado(Request $request)
    {
      $factura = null;
    $proveedorSeleccionado = null;

    // Si viene desde la creación de factura
    if ($request->filled('factura_id')) {
        $factura = FacturaGanado::with('proveedor')->find($request->factura_id);
        if ($factura) {
            $proveedorSeleccionado = $factura->proveedor;
        }
    }
    // Si viene desde el botón de la tabla con ?proveedor_id=X
    elseif ($request->filled('proveedor_id')) {
        $proveedorSeleccionado = ProveedorGanado::find($request->proveedor_id);
    }
        return view('compras.nuevoGanado',compact('factura','proveedorSeleccionado'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    // 1. Validar los datos generales del formulario y el array de animales
    $request->validate([
        'factura-id'             => 'required|numeric',
        'animals'                => 'required|array|min:1',
        'animals.*.tag_number'   => 'required|string|distinct|unique:ganado,areteID',
        'animals.*.category'     => 'required|in:Becerro,Becerra,Vaca,Vaquilla,Toro,Torete',
        'animals.*.gender'       => 'required|in:Macho,Hembra',
        'animals.*.weight'       => 'required|numeric|min:0.01',
        'animals.*.unit_price'   => 'required|numeric|min:0',
        'animals.*.total'        => 'required|numeric|min:0',
        'animals.*.breed'        => 'nullable|string|max:255',
        'animals.*.observation'  => 'nullable|string|max:255',
    ]);

    // 2. Proceso de guardado usando una transacción
    \DB::beginTransaction();
    try {
        // 3. Recorrer el arreglo y crear cada animal vinculado
        foreach ($request->animals as $animalData) {
            Ganado::create([
                'facturaID'    => $request['factura-id'],
                'areteID'      => $animalData['tag_number'],
                'raza'         => $animalData['breed'] ?? null,
                'categoria'    => $animalData['category'], // Al asignar esto, tu modelo activa automáticamente el mutador para el 'sexo'
                'pesoActual'   => $animalData['weight'],
                'ultimoPeso'   => $animalData['weight'],
                'precioCompra' => $animalData['unit_price'],
                'fechaCompra'  => $request->purchase_date,
                'status'       => 'Activo',
                'notas'        => $animalData['observation'] ?? null,
            ]);
        }

        \DB::commit();

        return redirect()->route('compras.ganado.index')
            ->with('success', 'Lote de ganado registrado exitosamente.');

    } catch (\Exception $e) {
        \DB::rollBack();
        return back()->withErrors(['error' => 'Ocurrió un error al registrar el lote: ' . $e->getMessage()])->withInput();
    }
}
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
