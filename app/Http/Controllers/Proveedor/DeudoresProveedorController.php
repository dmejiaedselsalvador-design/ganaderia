<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Models\Ganado\Adelanto;
use Illuminate\Http\Request;

class DeudoresProveedorController extends Controller
{
    //
    public function index()
    {
        $deudores = Adelanto::with('proveedor')
            ->where('status', '=', 'entregado')
            ->get();
        // obtener la lista de los deudores de proveedores desde la base de datos
     //  return response()->json(['message' => 'Lista de deudores', 'deudores' => $deudores]);
     return view('proveedores.deudores.index', compact('deudores'));
    }
}
