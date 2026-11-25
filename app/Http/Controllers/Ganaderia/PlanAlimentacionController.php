<?php

namespace App\Http\Controllers\Ganaderia;

use App\Http\Controllers\Controller;
use App\Models\Engorda\CorralLote;
use App\Models\Engorda\RacionAlimento;
use App\Models\Ganaderia\AsignacionDieta;
use App\Services\Ganaderia\ConsumoRecomendadoService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Plan de alimentación basado en pesos:
 * cabezas y biomasa ⇒ consumo sugerido por % de peso vivo ⇒ explosión de la receta (BOM) ⇒ cruce con bodega.
 */
class PlanAlimentacionController extends Controller
{
    public function __construct(private ConsumoRecomendadoService $consumo) {}

    /** Todos los corrales activos. */
    public function index(Request $request): View
    {
        $dias = $this->dias($request);
        $corrales = CorralLote::where('estado', 'Activo')->orderBy('codigo')->get();

        return view('ganaderia.plan.index', [
            'plan'      => $this->consumo->plan($corrales, $dias, (array) $request->input('pct', [])),
            'dias'      => $dias,
            'raciones'  => RacionAlimento::where('activo', true)->orderBy('codigo')->get(),
        ]);
    }

    /** Detalle de un corral: consumo por animal, explosión y bodega. */
    public function show(Request $request, CorralLote $corral): View
    {
        $dias = $this->dias($request);
        $pct = $request->filled('pct') ? [$corral->id => (float) $request->input('pct')] : [];
        $plan = $this->consumo->plan(collect([$corral]), $dias, $pct);
        $linea = $plan['lineas'][$corral->id];

        // Consumo de cada animal con el % aplicado al corral
        $animales = $plan['animales']->where('corral_id', $corral->id)->map(function ($a) use ($linea) {
            return $a + ($linea['racion'] ? $this->consumo->kgDia($a['peso'], $linea['pct'], $linea['racion']) : ['ms' => null, 'tco' => null]);
        });

        return view('ganaderia.plan.show', [
            'corral'    => $corral->load(['asignacionesDieta.racion', 'asignacionesDieta.usuario']),
            'plan'      => $plan,
            'l'         => $linea,
            'animales'  => $animales,
            'dias'      => $dias,
            'raciones'  => RacionAlimento::where('activo', true)->orderBy('codigo')->get(),
        ]);
    }

    /** Asigna (o cambia) la dieta del corral conservando el historial. */
    public function asignar(Request $request, CorralLote $corral): RedirectResponse
    {
        $data = $request->validate([
            'racion_id' => ['required', 'exists:raciones_alimento,id'],
            'desde'     => ['required', 'date', 'before_or_equal:' . now()->addDays(30)->toDateString()],
            'notas'     => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($corral, $data) {
            $desde = Carbon::parse($data['desde'])->startOfDay();
            $vigente = $corral->dietaVigente()->lockForUpdate()->first();

            if ($vigente && $vigente->desde->gte($desde)) {
                // Corrección el mismo día (o antes de que empezara): se reemplaza la dieta
                $vigente->update(['racion_id' => $data['racion_id'], 'desde' => $desde, 'pct_pv' => null, 'notas' => $data['notas'] ?? $vigente->notas]);

                return;
            }
            $vigente?->update(['hasta' => $desde->copy()->subDay()]);

            AsignacionDieta::create([
                'corral_lote_id' => $corral->id,
                'racion_id'      => $data['racion_id'],
                'desde'          => $desde,
                'notas'          => $data['notas'] ?? null,
                'user_id'        => auth()->id(),
            ]);
        });

        $racion = RacionAlimento::find($data['racion_id']);
        $msg = "Dieta {$racion->codigo} asignada al corral {$corral->codigo}.";
        if (! $racion->recetaCompleta()) {
            $msg .= ' ⚠ Esta dieta aún no tiene su receta completa (100 %); captúrala para poder descontar de bodega.';
        }

        return back()->with('success', $msg);
    }

    /** Guarda el % de peso vivo propio del corral (o vuelve al sugerido de la dieta). */
    public function guardarPct(Request $request, CorralLote $corral): RedirectResponse
    {
        $asig = $corral->dietaVigente;
        abort_unless($asig, 422, 'Primero asigna una dieta al corral.');
        $racion = $asig->racion;

        $data = $request->validate([
            'pct_pv'        => ['nullable', 'numeric', 'min:0.5', 'max:5'],
            'justificacion' => ['nullable', 'string', 'max:255'],
        ]);

        $pct = $data['pct_pv'] ?? null;
        $fuera = $pct !== null && $racion->pct_pv_min && $racion->pct_pv_max
            && ($pct < (float) $racion->pct_pv_min || $pct > (float) $racion->pct_pv_max);

        if ($fuera && empty($data['justificacion'])) {
            return back()->withErrors(['justificacion' => "El {$pct} % está fuera del rango de la dieta ({$racion->pct_pv_min}–{$racion->pct_pv_max} %). Escribe el motivo para guardarlo."])->withInput();
        }

        $asig->update([
            'pct_pv' => $pct,
            'notas'  => $fuera ? mb_substr("% fuera de rango: {$data['justificacion']}", 0, 255) : $asig->notas,
        ]);

        return back()->with('success', $pct === null
            ? "El corral {$corral->codigo} vuelve a usar el % sugerido de la dieta."
            : "Consumo del corral {$corral->codigo} fijado en {$pct} % del peso vivo.");
    }

    /** Parámetros de consumo de una dieta: base (MS/TCO), rango de % de peso vivo y materia seca. */
    public function consumoDieta(Request $request, RacionAlimento $racion): RedirectResponse
    {
        $data = $request->validate([
            'base_consumo'     => ['required', Rule::in(['MS', 'TCO'])],
            'pct_pv_min'       => ['required', 'numeric', 'min:0.5', 'max:5'],
            'pct_pv_max'       => ['required', 'numeric', 'gte:pct_pv_min', 'max:5'],
            'pct_pv_sugerido'  => ['required', 'numeric', 'gte:pct_pv_min', 'lte:pct_pv_max'],
            'materia_seca_pct' => ['nullable', 'numeric', 'min:1', 'max:100'],
        ], [
            'pct_pv_max.gte'      => 'El máximo debe ser mayor o igual al mínimo.',
            'pct_pv_sugerido.gte' => 'El sugerido debe estar dentro del rango.',
            'pct_pv_sugerido.lte' => 'El sugerido debe estar dentro del rango.',
        ]);

        if ($data['materia_seca_pct'] === null) {
            unset($data['materia_seca_pct']);
        }
        $racion->update($data);

        return back()->with('success', 'Parámetros de consumo de la dieta guardados.');
    }

    private function dias(Request $request): int
    {
        return max(1, min(120, (int) $request->input('dias', 7)));
    }
}
