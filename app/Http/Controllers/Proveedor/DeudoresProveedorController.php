<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Models\Ganado\Adelanto;
use Illuminate\View\View;

class DeudoresProveedorController extends Controller
{
    /**
     * Muestra la lista de adelantos pendientes de liquidar por proveedor.
     */
    public function index(): View
    {
        $deudores = Adelanto::with('proveedor')
            ->pendiente()
            ->latest('date')
            ->get();

        return view('proveedores.deudores.index', compact('deudores'));
    }
}
