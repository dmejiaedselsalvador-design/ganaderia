<?php

namespace App\Http\Controllers;

use App\Models\Engorda\ConsumoAlimento;
use App\Models\Engorda\CorralLote;
use App\Models\Engorda\RacionAlimento;
use App\Models\Ganado\Ganado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NutricionController extends Controller
{
    /**
     * Panel de control de nutrición, catálogo de dietas y registro de consumos diarios.
     */
    public function index(): View
    {
        $raciones = RacionAlimento::withCount('consumos')->orderBy('id', 'asc')->get();
        $corrales = CorralLote::with('ganadosActivos')->where('estado', 'Activo')->get();

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
            'conversionAlimenticiaGlobal'
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
        RacionAlimento::create($validated);

        return redirect()->route('nutricion.index')
            ->with('success', "Dieta {$validated['nombre']} registrada correctamente.");
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

        ConsumoAlimento::create([
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

        return redirect()->route('nutricion.index')
            ->with('success', "Consumo registrado para {$corral->nombre}: {$consumidos} kg (" . formatoPesos($costoTotalMXN) . ").");
    }
}
