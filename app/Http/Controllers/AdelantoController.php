<?php

namespace App\Http\Controllers;
use App\Models\Ganado\proveedorGanado;
use Illuminate\Http\Request;

class AdelantoController extends Controller
{
    //
    public function index(){
            $proveedores = proveedorGanado::all();
        return view('adelantos.prestamos',compact('proveedores'));
    }
}
