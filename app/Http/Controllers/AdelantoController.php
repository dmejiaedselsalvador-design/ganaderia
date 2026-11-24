<?php

namespace App\Http\Controllers;

use App\Http\Requests\Adelanto\StoreAdelantoRequest;
use App\Models\Ganado\Adelanto;
use App\Models\Ganado\ProveedorGanado;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdelantoController extends Controller
{
    /**
     * Muestra la pantalla general de adelantos con todos los proveedores activos.
     */
    public function index(): View
    {
        $proveedores = ProveedorGanado::activo()->orderBy('nombreProveedor')->get();

        return view('adelantos.prestamos', compact('proveedores'));
    }

    /**
     * Formulario para emitir un adelanto o préstamo a un proveedor específico.
     */
    public function create(ProveedorGanado $proveedor): View
    {
        return view('adelantos.prestamos', compact('proveedor'));
    }

    /**
     * Registra un nuevo adelanto financiero.
     */
    public function store(StoreAdelantoRequest $request): RedirectResponse
    {
        Adelanto::create([
            'proveedor_id' => $request->input('supplier_id'),
            'date'         => $request->input('advance_date'),
            'concepto'     => $request->input('concept'),
            'dinero'       => $request->input('amount'),
            'status'       => Adelanto::STATUS_ENTREGADO,
        ]);

        return redirect()
            ->route('proveedores.index')
            ->with('success', 'Adelanto o préstamo registrado con éxito.');
    }
}
