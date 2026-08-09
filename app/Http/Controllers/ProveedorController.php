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
        //
    //   $proveedores = ProveedorGanado::where('estado','=','activo')->get();
       $proveedores = ProveedorGanado::query()
        ->where('estado', 'activo')
        // Esto crea una columna virtual llamada 'adelantos_sum_dinero'
        ->withSum('adelantos as total_adelanto', 'dinero')
        // Ordenamos por esa suma de mayor a menor
        ->orderBy('total_adelanto', 'desc')
        ->get();
     //  return response()->json($proveedores);
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
