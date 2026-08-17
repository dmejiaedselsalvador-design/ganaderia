<?php

namespace App\Http\Controllers;

use App\Models\Ganado\Ganado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    //
    public function index()
    {
        $ganados = Ganado::where('status', 'activo')->count();
        $pesoGanados = Ganado::average('ultimoPeso');

        $ganadoExportar = Ganado::where('status', 'activo')->where('ultimoPeso', '>', 200)->count();
        $distribucionSexo = Ganado::where('status', 'activo')->select('genero', \DB::raw('count(*) as total'))->groupBy('genero')->get();

        $tendenciaRaw = Ganado::where('status', 'activo')
        ->select(
            \DB::raw("DATE_FORMAT(created_at, '%Y-%m') as mes"),
            \DB::raw("AVG(ultimoPeso) as peso_promedio")
        )
        ->groupBy('mes')
        ->orderBy('mes', 'asc')
        ->take(6) // Últimos 6 meses
        ->get();

    // Separamos en arrays limpios para Chart.js
    // Si prefieres nombres de meses cortos (Ene, Feb, Mar), puedes mapearlos aquí o en JS.
    $labelsTendencia = $tendenciaRaw->pluck('mes');
    $dataTendencia = $tendenciaRaw->pluck('peso_promedio');
        //   return response()->json($ganados);
        return view('welcome', compact('ganados', 'pesoGanados', 'ganadoExportar','distribucionSexo','labelsTendencia','dataTendencia'));
    }
}
