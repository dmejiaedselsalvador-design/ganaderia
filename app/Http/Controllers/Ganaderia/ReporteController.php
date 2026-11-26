<?php

namespace App\Http\Controllers\Ganaderia;

use App\Http\Controllers\Controller;
use App\Models\Engorda\CorralLote;
use App\Models\Ganado\Ganado;
use App\Services\Ganaderia\RentabilidadService;
use App\Support\Csv;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Rentabilidad por animal y por lote, metas vs real y reportes exportables.
 */
class ReporteController extends Controller
{
    public function __construct(private RentabilidadService $rentabilidad) {}

    public function index(): View
    {
        return view('ganaderia.reportes.index', ['resumen' => $this->rentabilidad->resumen()]);
    }

    public function lotes(Request $request)
    {
        $lotes = $this->rentabilidad->porLote();

        if ($request->input('exportar') === 'csv') {
            return Csv::descargar('rentabilidad_por_lote',
                ['Lote', 'Cabezas', 'Activos', 'Compra', 'Alimento', 'Medicinas', 'Gastos', 'Costo total', 'Ingreso', 'Utilidad', 'Kg ganados', 'GDP', 'Costo/kg', 'Conversión', 'Mortalidad %'],
                $lotes->map(fn ($l) => [$l['nombre'], $l['cabezas'], $l['activos'], $l['compra'], $l['alimento'], $l['medicina'], $l['gastos'], $l['costo_total'],
                    $l['ingreso'], $l['utilidad'], $l['kg_ganados'], $l['gdp'], $l['costo_kg'], $l['conversion'], $l['mortalidad_pct']]));
        }
        if ($request->input('exportar') === 'pdf') {
            return \App\Support\DocumentoPdf::crear('ganaderia.reportes.lotes-pdf', compact('lotes'), 'landscape')
                ->download('rentabilidad_por_lote_' . now()->format('Ymd') . '.pdf');
        }

        return view('ganaderia.reportes.lotes', compact('lotes'));
    }

    public function animales(Request $request)
    {
        $estado = $request->input('estado', 'Activo');
        $animales = Ganado::query()
            ->when($estado !== 'todos', fn ($q) => $q->where('status', $estado))
            ->when($request->filled('corral'), fn ($q) => $q->where('corral_lote_id', $request->corral))
            ->get();
        $filas = $this->rentabilidad->porAnimal($animales)->sortBy(fn ($r) => $r['utilidad'] ?? PHP_INT_MAX)->values();

        if ($request->input('exportar') === 'csv') {
            return Csv::descargar('rentabilidad_por_animal',
                ['Arete', 'Categoría', 'Estatus', 'Días', 'Compra', 'Alimento', 'Medicinas', 'Gastos', 'Costo total', 'Kg ganados', 'Costo/kg', 'Ingreso', 'Tipo ingreso', 'Utilidad', 'Margen %'],
                $filas->map(fn ($r) => [$r['ganado']->areteID, $r['ganado']->categoria, $r['ganado']->status, $r['dias'], $r['compra'], $r['alimento'], $r['medicina'],
                    $r['gasto_corral'] + $r['gasto_general'], $r['costo_total'], $r['kg_ganados'], $r['costo_kg'], $r['ingreso'], $r['tipo_ingreso'], $r['utilidad'], $r['margen_pct']]));
        }

        return view('ganaderia.reportes.animales', [
            'filas'    => $filas,
            'estado'   => $estado,
            'corrales' => CorralLote::orderBy('codigo')->pluck('codigo', 'id'),
        ]);
    }

    /** Inventario de ganado activo por corral (CSV / PDF). */
    public function inventario(Request $request)
    {
        $animales = Ganado::activo()->with('corralLote')->orderBy('corral_lote_id')->orderBy('areteID')->get();

        if ($request->input('exportar') === 'pdf') {
            return \App\Support\DocumentoPdf::crear('ganaderia.reportes.inventario-pdf', ['grupos' => $animales->groupBy(fn ($a) => $a->corralLote->codigo ?? 'Sin corral')])
                ->download('inventario_ganado_' . now()->format('Ymd') . '.pdf');
        }

        return Csv::descargar('inventario_ganado',
            ['Corral', 'Arete', 'SINIIGA', 'Categoría', 'Sexo', 'Raza', 'Fierro', 'Fecha compra', 'Días', 'Peso ingreso', 'Peso actual', 'Ganancia kg', 'Procedencia'],
            $animales->map(fn ($a) => [$a->corralLote->codigo ?? 'Sin corral', $a->areteID, $a->arete_siniiga, $a->categoria, $a->sexo, $a->raza, $a->fierro,
                $a->fechaCompra?->format('Y-m-d'), $a->fechaCompra ? (int) $a->fechaCompra->diffInDays(now()) : null,
                (float) $a->pesoActual, (float) $a->ultimoPeso, round((float) $a->ultimoPeso - (float) $a->pesoActual, 2), $a->procedencia]));
    }
}
