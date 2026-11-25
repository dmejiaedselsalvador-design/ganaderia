<?php

namespace App\Http\Controllers;

use App\Models\Engorda\ConsumoAlimento;
use App\Models\Engorda\CorralLote;
use App\Models\Engorda\RacionAlimento;
use App\Models\Ganado\Ganado;
use App\Services\Ganaderia\ConsumoRecomendadoService;
use App\Support\Volver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NutricionController extends Controller
{
    /**
     * Panel de control de nutrición, catálogo de dietas y registro de consumos diarios.
     */
    public function index(Request $request, ConsumoRecomendadoService $consumo): View
    {
        $raciones = RacionAlimento::withCount(['consumos', 'ingredientes'])->orderBy('id', 'asc')->get();
        $corrales = CorralLote::with(['ganadosActivos', 'dietaVigente.racion'])->where('estado', 'Activo')->orderBy('codigo')->get();

        // Sugerencia de servido por corral (dieta asignada + kg/día por peso vivo) para precargar el registro
        $plan = $consumo->plan($corrales, 1);
        $sugerencias = collect($plan['lineas'])->mapWithKeys(fn ($l) => [$l['corral']->id => [
            'racion' => $l['racion']?->id,
            'kg'     => $l['kg_dia'] > 0 ? round($l['kg_dia']) : null,
            'cab'    => $l['cabezas'],
        ]]);
        $servir = $request->integer('servir') ?: null;
        $volver = Volver::url($request);

        $consumosRecientes = ConsumoAlimento::with(['corralLote', 'racion', 'usuario'])
            ->latest('fecha')
            ->latest('id')
            ->take(15)
            ->get();

        // Métricas de alimentación
        $totalKilosConsumidos = ConsumoAlimento::sum('kilos_consumidos');
        $inversionTotalAlimentoMXN = ConsumoAlimento::sum('costo_total_mxn');
        $costoPromedioKgAlimento = $totalKilosConsumidos > 0
            ? round($inversionTotalAlimentoMXN / $totalKilosConsumidos, 2)
            : 0;

        // Conversión alimenticia promedio global (kg alimento / kg peso ganado)
        $kilosGanadosTotales = (float) Ganado::activo()->get()->sum(fn($g) => $g->ganancia_total);
        $conversionAlimenticiaGlobal = ($kilosGanadosTotales > 0 && $totalKilosConsumidos > 0)
            ? round($totalKilosConsumidos / $kilosGanadosTotales, 2)
            : 0;

        return view('nutricion.index', compact(
            'raciones',
            'corrales',
            'consumosRecientes',
            'totalKilosConsumidos',
            'inversionTotalAlimentoMXN',
            'costoPromedioKgAlimento',
            'conversionAlimenticiaGlobal',
            'sugerencias',
            'servir',
            'volver'
        ));
    }

    /**
     * Crear una nueva ración técnica en el catálogo.
     */
    public function storeRacion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo'                     => 'required|string|max:50|unique:raciones_alimento,codigo',
            'nombre'                     => 'required|string|max:150',
            'fase'                       => 'required|in:Recepcion,Crecimiento,Finalizacion,Mantenimiento',
            'costo_por_kg'               => 'required|numeric|min:0.1|max:100',
            'materia_seca_pct'           => 'required|numeric|min:10|max:100',
            'proteina_cruda_pct'         => 'required|numeric|min:5|max:40',
            'energia_metabolizable_mcal' => 'required|numeric|min:1|max:5',
            'descripcion_ingredientes'   => 'nullable|string|max:1000',
        ]);

        $validated['activo'] = true;
        // Rango de consumo (% del peso vivo, base materia seca) sugerido según la fase
        [$min, $max, $sug] = RacionAlimento::RANGOS_PV[$validated['fase']];
        $racion = RacionAlimento::create($validated + [
            'base_consumo' => 'MS', 'pct_pv_min' => $min, 'pct_pv_max' => $max, 'pct_pv_sugerido' => $sug,
        ]);

        // Siguiente paso natural: capturar la receta (BOM). Se conserva el regreso a donde venía.
        return redirect()->route('raciones.formula', array_filter(['racion' => $racion->id, 'volver' => Volver::url($request)]))
            ->with('success', "Dieta {$validated['nombre']} registrada. Ahora captura su receta (ingredientes).");
    }

    /**
     * Registrar el servido diario de alimento a un corral / lote.
     */
    public function storeConsumo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'corral_lote_id' => 'required|exists:corrales_lotes,id',
            'racion_id'      => 'required|exists:raciones_alimento,id',
            'fecha'          => 'required|date',
            'kilos_servidos' => 'required|numeric|min:1|max:50000',
            'kilos_rechazo'  => 'nullable|numeric|min:0|lte:kilos_servidos',
            'observaciones'  => 'nullable|string|max:500',
        ]);

        $racion = RacionAlimento::findOrFail($validated['racion_id']);
        $corral = CorralLote::with('ganadosActivos')->findOrFail($validated['corral_lote_id']);

        $rechazo = (float) ($validated['kilos_rechazo'] ?? 0);
        $consumidos = round((float) $validated['kilos_servidos'] - $rechazo, 2);
        $costoTotalMXN = round($consumidos * (float) $racion->costo_por_kg, 2);

        $cabezas = max(1, $corral->ganadosActivos->count());
        $promedioPorCabeza = round($consumidos / $cabezas, 2);

        $consumo = ConsumoAlimento::create([
            'corral_lote_id'             => $corral->id,
            'racion_id'                  => $racion->id,
            'fecha'                      => $validated['fecha'],
            'kilos_servidos'             => $validated['kilos_servidos'],
            'kilos_rechazo'              => $rechazo,
            'kilos_consumidos'           => $consumidos,
            'costo_total_mxn'            => $costoTotalMXN,
            'cabezas_contadas'           => $cabezas,
            'consumo_promedio_cabeza_kg' => $promedioPorCabeza,
            'observaciones'              => $validated['observaciones'] ?? null,
            'user_id'                    => Auth::id(),
        ]);

        // Descuenta de bodega los ingredientes de la receta (BOM) de la dieta
        $negativos = app(\App\Services\Ganaderia\ExplosionMaterialesService::class)->descontarServido($racion, (float) $validated['kilos_servidos'], [
            'fecha'          => $validated['fecha'],
            'referencia'     => "Consumo #{$consumo->id} · {$corral->codigo} · {$racion->codigo}",
            'origen_type'    => 'ConsumoAlimento',
            'origen_id'      => $consumo->id,
            'corral_lote_id' => $corral->id,
        ]);
        $aviso = $negativos ? ' ⚠ Quedaron con existencia negativa: ' . implode(', ', $negativos) . '. Registra su entrada en Inventario.' : '';

        // Regresa a la pantalla de origen (plan del corral) o al panel de nutrición sin reabrir el formulario
        return redirect()->to(Volver::url($request) ?? route('nutricion.index'))
            ->with('success', "Consumo registrado para {$corral->nombre}: {$consumidos} kg (" . formatoPesos($costoTotalMXN) . ")." . $aviso);
    }
}
