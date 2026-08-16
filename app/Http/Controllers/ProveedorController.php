<?php

namespace App\Http\Controllers;

use App\Models\Ganado\Adelanto;
use App\Models\Ganado\ProveedorGanado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
$proveedores = ProveedorGanado::query()
    ->where('estado', 'activo')
    ->withSum('adelantos as total_adelantos', 'dinero')
    ->withSum('ganadoDirecto as total_facturas', 'precioCompra') // Apunta a la columna correcta
    ->get();

    // Calcular el saldo neto de liquidación por proveedor
    $proveedores->each(function($proveedor) {
        $totalAdelantos = $proveedor->total_adelantos ?? 0;
        $totalFacturas = $proveedor->total_facturas ?? 0;

        // Adelantos dados menos facturas de ganado (saldo neto)
        $proveedor->saldo_liquidacion = $totalAdelantos - $totalFacturas;
    });

    // Opcional: ordenar por mayor saldo
    $proveedores = $proveedores->sortByDesc('saldo_liquidacion');

    return view('proveedores.index', compact('proveedores'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('proveedores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validar los datos que vienen del formulario
    $request->validate([
        'nombreProoveedor' => 'required|string|max:255',
        'telefono'         => 'nullable|string|max:50',
        'nombreContacto'   => 'nullable|string|max:255',
        'lugar'            => 'nullable|string|max:255',
        'razon_social'     => 'nullable|string|max:255',
        'ubicacion'        => 'nullable|string',
    ]);

    $proveedor = ProveedorGanado::create([

    'nombreProveedor' => $request->nombreProoveedor,
        'nombreContacto'   => $request->nombreContacto,
        'telefono'         => $request->telefono,
        'lugar'            => $request->lugar,
        'razon_social'     => $request->razon_social,
        'ubicacion'        => $request->ubicacion,
    ]);

    return redirect()->route('proveedores.index')
                     ->with('success', 'Proveedor creado exitosamente.');

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

    $proveedor = ProveedorGanado::findOrFail($id);

        return view('proveedores.edit',compact('proveedor'));
    }

    /**
     * Update the specified resource in storage.
     */
public function update(Request $request, string $id)
{
    // 1. Validar los datos que vienen del formulario
    $request->validate([
        'nombreProoveedor' => 'required|string|max:255',
        'telefono'         => 'nullable|string|max:50',
        'nombreContacto'   => 'nullable|string|max:255',
        'lugar'            => 'nullable|string|max:255',
        'razon_social'     => 'nullable|string|max:255',
        'ubicacion'        => 'nullable|string',
    ]);

    // 2. Buscar el proveedor
    $proveedor = ProveedorGanado::findOrFail($id);

    // 3. Determinar el estado que el usuario quiere poner (activo o inactivo)
    $nuevoEstado = $request->has('estado') ? 'activo' : 'inactivo';

    $mensajeAlerta = 'Proveedor editado exitosamente.';

    // 4. Si el usuario quiere desactivarlo ('inactivo'), validamos si tiene deuda
    if ($nuevoEstado === 'inactivo') {
        $tieneDeuda = Adelanto::where('proveedor_id', $id)
                              ->where('dinero', '>', 0)
                              ->exists();

        if ($tieneDeuda) {
            // Si tiene deuda: Forzamos a que se quede 'activo' (no se desactiva)
            $nuevoEstado = 'activo';

            // Preparamos un mensaje de alerta indicando que lo demás se guardó pero el estado no cambió
            $mensajeAlerta = 'Proveedor editado exitosamente, pero NO se pudo desactivar porque tiene adelantos con saldo pendiente (dinero > 0).';
        }
    }

    // 5. Actualizamos todos los datos (el estado cambiará a inactivo solo si no tenía deuda)
    $proveedor->update([
        'nombreProveedor' => $request->nombreProoveedor,
        'nombreContacto'  => $request->nombreContacto,
        'telefono'        => $request->telefono,
        'lugar'           => $request->lugar,
        'razon_social'    => $request->razon_social,
        'ubicacion'       => $request->ubicacion,
        'estado'          => $nuevoEstado,
    ]);

    // 6. Redirigimos con el mensaje correspondiente (éxito o aviso)
    return redirect()->route('proveedores.index')
                     ->with('success', $mensajeAlerta);
}
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
