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
            // 1. Suma el dinero total histórico de adelantos
            ->withSum('adelantos as total_adelanto', 'dinero')

            // 2. Suma el saldo vivo (lo que aún no se ha gastado de los adelantos)
            ->withSum(['adelantos as adelantos_disponibles' => function($query) {
                $query->where('montoDisponible', '>', 0);
            }], 'montoDisponible')

            // 3. Suma el valor total de las facturas/lotes de ganado de este proveedor
            ->withSum('facturasGanado as total_ganado_recibido', 'montoTotal')
            ->get();

        // 4. Calcular el balance neto (Saldo = Dinero disponible de adelantos - Deuda de ganado recibido)
        $proveedores->each(function($proveedor) {
            $dineroDisponible = $proveedor->adelantos_disponibles ?? 0;
            $ganadoRecibido = $proveedor->total_ganado_recibido ?? 0;

            // Si le diste 0 de adelanto y te trajo 500 de ganado, el resultado será -500.00 (Le debes)
            $proveedor->saldo_neto = $dineroDisponible - $ganadoRecibido;
        });

        // Opcional: Ordenar para ver primero los que tienen mayores saldos o deudas
        $proveedores = $proveedores->sortByDesc('saldo_neto');

       return view('proveedores.index',compact('proveedores'));
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
