<?php

namespace App\Http\Controllers;
use App\Models\Ganado\Adelanto;
use App\Models\Ganado\ProveedorGanado;
use Illuminate\Http\Request;

class AdelantoController extends Controller
{
    //
    public function index(){
            $proveedores = ProveedorGanado::all();

        return view('adelantos.prestamos',compact('proveedores'));
    }

    public function create(ProveedorGanado $proveedor){

        return view('adelantos.prestamos',compact('proveedor'));
    }

    public function store(Request $Request){

       $Request->validate([
            'supplier_id'   => 'required|exists:proveedorGanado,id', // Cambia 'proveedorGanado' por el nombre real de tu tabla de proveedores si es distinta
            'advance_date'  => 'required|date',
            'concept'       => 'required|string|max:255',
            'amount'        => 'required|numeric|min:0.01',

        ], [
            'supplier_id.required'  => 'El identificador del proveedor es obligatorio.',
            'advance_date.required' => 'La fecha del adelanto es obligatoria.',
            'concept.required'      => 'El concepto o descripción es obligatorio.',
            'amount.required'       => 'El monto equivalente es obligatorio.',
            'amount.min'            => 'El monto debe ser mayor a cero.',
        ]);

           Adelanto::create([
         'proveedor_id'    => $Request->supplier_id,
            'date'           => $Request->advance_date,
            'concepto'        => $Request->concept,
            'dinero'           => $Request->amount,
          //  'montoDisponible'  => $Request->amount,
            'estado'          => 'disponible',
           ]);

           return redirect()->route('proveedores.index')->with('success','Adelanto o prestamo Registrado con Exito');
    }
}
