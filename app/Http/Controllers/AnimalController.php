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

    public function nuevoGanado()
    {
       $proveedores = ProveedorGanado::all();

        return view('compras.nuevoGanado',compact('proveedores'));
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
        'supplier_id'          => 'required|exists:proveedorGanado,id',
        'purchase_date'        => 'required|date',
        'invoice_number'       => 'nullable|string|max:255',
        'animals'              => 'required|array|min:1',
        'animals.*.tag_number' => 'required|string|distinct|unique:ganado,areteID',
        'animals.*.gender'     => 'required|in:Macho,Hembra',
        'animals.*.weight'     => 'required|numeric|min:0.01',
        'animals.*.unit_price' => 'required|numeric|min:0',
        'animals.*.total'      => 'required|numeric|min:0',
        'animals.*.breed'      => 'nullable|string|max:255',
        'animals.*.observation'=> 'nullable|string|max:255',
    ]);



    // 2. Proceso de guardado (ejemplo usando una transacción)
    \DB::beginTransaction();
    try {
        // Crear el lote o la compra general de ganado según tu estructura de BD
        // ...
  $montoTotal = collect($request->animals)->sum('total');

    $factura = FacturaGanado::create([
            'proveedorID'   => $request->supplier_id,
            'fechaFactura'  => $request->purchase_date,
            'numeroFactura' => $request->invoice_number,
            'montoTotal'    => $montoTotal,
            'estado'        => 'pendiente', // o el valor por defecto que prefieras
        ]);

  // 4. SEGUNDO: Recorrer el arreglo y crear cada animal vinculado a la factura recién creada
        foreach ($request->animals as $animalData) {
            Ganado::create([
                'facturaID'    => $factura->id, // Aquí vinculamos el ID de la factura creada
                'areteID'      => $animalData['tag_number'],
                'raza'         => $animalData['breed'] ?? null,
                'genero'       => $animalData['gender'],
                'pesoActual'   => $animalData['weight'],
                'ultimoPeso'   => $animalData['weight'], // Al ingresar por primera vez, el último peso es el inicial
                'precioCompra' => $animalData['unit_price'],
                'fechaCompra'  => $request->purchase_date,
                'status'       => 'Activo',
                'notas'        => $animalData['observation'] ?? null,
            ]);
        }

        DB::commit();

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
