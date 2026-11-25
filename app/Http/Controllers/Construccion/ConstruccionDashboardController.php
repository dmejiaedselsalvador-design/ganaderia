<?php

namespace App\Http\Controllers\Construccion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConstruccionDashboardController extends Controller
{
    /** Módulos planificados de la planta Construcción. */
    public const MODULOS = [
        'proyectos'    => ['Proyectos y Obras', 'fa-building', 'Avance físico y financiero de cada obra, bitácora y fechas clave.'],
        'presupuestos' => ['Presupuestos', 'fa-file-invoice-dollar', 'Cotizaciones, partidas y control de costo vs. presupuesto.'],
        'materiales'   => ['Materiales', 'fa-trowel-bricks', 'Inventario de bodega, requisiciones y compras a proveedores.'],
        'personal'     => ['Personal y Cuadrillas', 'fa-people-group', 'Asistencia, planillas y asignación de cuadrillas por obra.'],
    ];

    public function index(): View
    {
        return view('construccion.dashboard', ['modulos' => self::MODULOS]);
    }

    public function modulo(Request $request, string $modulo): View
    {
        abort_unless($request->user()->can("construccion.$modulo"), 403);

        return view('construccion.modulo', [
            'clave'  => $modulo,
            'modulo' => self::MODULOS[$modulo],
        ]);
    }
}
