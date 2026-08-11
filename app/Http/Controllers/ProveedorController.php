<?php

namespace App\Http\Controllers;

use App\Models\Ganado\ProveedorGanado;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    $proveedores = ProveedorGanado::query()
        ->where('estado', 'activo')
        // Suma total de adelantos (Equivalente al LEFT JOIN de adelantos)
        ->withSum('adelantos as total_adelantos', 'dinero')
        // Suma total de facturas de ganado (Equivalente al LEFT JOIN de facturaGanado)
        ->withSum('facturasGanado as total_facturas', 'montoTotal')
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

    'nombreProoveedor' => $request->nombreProoveedor,
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
