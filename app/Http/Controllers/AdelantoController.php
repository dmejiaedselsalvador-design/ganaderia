<?php

namespace App\Http\Controllers;
use App\Models\Ganado\ProveedorGanado;
use Illuminate\Http\Request;

class AdelantoController extends Controller
{
    //
    public function index(){
            $proveedores = ProveedorGanado::all();
        return view('adelantos.prestamos',compact('proveedores'));
    }
}
