<?php

namespace App\Http\Controllers\Ganaderia;

use App\Http\Controllers\Controller;
use App\Models\Engorda\CorralLote;
use App\Models\Ganaderia\EventoEnfermedad;
use App\Models\Ganaderia\Insumo;
use App\Models\Ganaderia\ProgramacionSanitaria;
use App\Models\Ganado\Ganado;
use App\Models\Sanidad\TratamientoSanitario;
use App\Services\Ganaderia\InventarioService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Calendario sanitario, aplicación masiva por corral y casos de enfermedad (morbilidad).
 */
class SanidadAvanzadaController extends Controller
{
    public const TIPOS_EVENTO = ['Recepcion', 'Vacunacion', 'Desparasitacion', 'Vitamina', 'Antibiotico', 'Implante', 'Tratamiento_Clinico'];

    public function __construct(private InventarioService $inventario) {}

    /* ---------------- Calendario ---------------- */

    public function calendario(Request $request): View
    {
        $mes = $request->filled('mes') ? Carbon::parse($request->mes . '-01') : now()->startOfMonth();

        return view('ganaderia.sanidad.calendario', [
            'mes'          => $mes,
            'programadas'  => ProgramacionSanitaria::with(['corral', 'insumo'])
                ->whereBetween('fecha_programada', [$mes->copy()->startOfMonth(), $mes->copy()->endOfMonth()])
                ->orderBy('fecha_programada')->get(),
            'vencidas'     => ProgramacionSanitaria::with('corral')->pendientes()->whereDate('fecha_programada', '<', today())->orderBy('fecha_programada')->get(),
            'proximas'     => ProgramacionSanitaria::with('corral')->pendientes()->whereBetween('fecha_programada', [today(), today()->addDays(7)])->orderBy('fecha_programada')->get(),
            'corrales'     => CorralLote::withCount('ganadosActivos')->orderBy('codigo')->get(),
            'medicamentos' => Insumo::activos()->where('tipo', 'medicamento')->orderBy('nombre')->get(),
        ]);
    }

    public function programar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'titulo'             => ['required', 'string', 'max:150'],
            'tipo_evento'        => ['required', Rule::in(self::TIPOS_EVENTO)],
            'corral_lote_id'     => ['required', 'exists:corrales_lotes,id'],
            'insumo_id'          => ['nullable', 'exists:insumos,id'],
            'fecha_programada'   => ['required', 'date'],
            'dosis'              => ['nullable', 'string', 'max:50'],
            'via_administracion' => ['nullable', 'string', 'max:50'],
            'notas'              => ['nullable', 'string', 'max:500'],
            'repetir_dias'       => ['nullable', 'integer', 'min:1', 'max:365'],
            'repeticiones'       => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $veces = $request->filled('repetir_dias') ? (int) ($data['repeticiones'] ?? 1) : 1;
        for ($i = 0; $i < $veces; $i++) {
            ProgramacionSanitaria::create(collect($data)->except(['repetir_dias', 'repeticiones'])->all() + [
                'fecha_programada' => Carbon::parse($data['fecha_programada'])->addDays($i * (int) ($data['repetir_dias'] ?? 0))->toDateString(),
                'titulo'           => $veces > 1 ? $data['titulo'] . ' (' . ($i + 1) . "/{$veces})" : $data['titulo'],
                'estado'           => 'pendiente',
                'user_id'          => auth()->id(),
            ]);
        }

        return back()->with('success', $veces > 1 ? "{$veces} aplicaciones programadas." : 'Aplicación programada.');
    }

    /**
     * Aplica el tratamiento a todos los animales activos del corral:
     * crea un tratamiento por animal, calcula el retiro y descuenta el medicamento del inventario.
     */
    public function aplicar(Request $request, ProgramacionSanitaria $programacion): RedirectResponse
    {
        abort_if($programacion->estado !== 'pendiente', 422, 'Esta aplicación ya no está pendiente.');

        $data = $request->validate([
            'fecha_aplicacion'        => ['required', 'date', 'before_or_equal:today'],
            'medicamento_producto'    => ['required', 'string', 'max:150'],
            'dosis_aplicada'          => ['required', 'string', 'max:50'],
            'via_administracion'      => ['required', 'string', 'max:50'],
            'cantidad_por_cabeza'     => ['nullable', 'numeric', 'min:0'],
            'costo_por_cabeza'        => ['nullable', 'numeric', 'min:0'],
            'dias_retiro_rastro'      => ['required', 'integer', 'min:0', 'max:365'],
            'lote_medicamento'        => ['nullable', 'string', 'max:60'],
            'caducidad_medicamento'   => ['nullable', 'date'],
            'veterinario_responsable' => ['nullable', 'string', 'max:150'],
            'excluir'                 => ['array'],
        ]);

        $animales = Ganado::activo()->where('corral_lote_id', $programacion->corral_lote_id)
            ->whereNotIn('GanadoID', $data['excluir'] ?? [])->get();

        if ($animales->isEmpty()) {
            return back()->withErrors(['programacion' => 'El corral no tiene animales activos.']);
        }

        $insumo = $programacion->insumo;
        $cantidad = (float) ($data['cantidad_por_cabeza'] ?? 0);
        $costoCabeza = $data['costo_por_cabeza'] ?? ($insumo && $cantidad > 0 ? round($cantidad * (float) $insumo->costo_promedio, 2) : 0);
        $finRetiro = $data['dias_retiro_rastro'] > 0 ? Carbon::parse($data['fecha_aplicacion'])->addDays($data['dias_retiro_rastro'])->toDateString() : null;

        DB::transaction(function () use ($animales, $programacion, $data, $insumo, $cantidad, $costoCabeza, $finRetiro) {
            foreach ($animales as $g) {
                TratamientoSanitario::create([
                    'ganado_id'               => $g->GanadoID,
                    'programacion_id'         => $programacion->id,
                    'insumo_id'               => $insumo?->id,
                    'cantidad_insumo'         => $cantidad ?: null,
                    'lote_medicamento'        => $data['lote_medicamento'] ?? null,
                    'caducidad_medicamento'   => $data['caducidad_medicamento'] ?? null,
                    'tipo_evento'             => $programacion->tipo_evento,
                    'medicamento_producto'    => $data['medicamento_producto'],
                    'dosis_aplicada'          => $data['dosis_aplicada'],
                    'via_administracion'      => $data['via_administracion'],
                    'fecha_aplicacion'        => $data['fecha_aplicacion'],
                    'dias_retiro_rastro'      => $data['dias_retiro_rastro'],
                    'fecha_fin_retiro'        => $finRetiro,
                    'costo_medicamento_mxn'   => $costoCabeza,
                    'veterinario_responsable' => $data['veterinario_responsable'] ?? null,
                    'diagnostico_motivo'      => $programacion->titulo,
                    'user_id'                 => auth()->id(),
                ]);
            }

            if ($insumo && $cantidad > 0) {
                $this->inventario->salida($insumo, round($cantidad * $animales->count(), 3), [
                    'fecha'          => $data['fecha_aplicacion'],
                    'lote'           => $data['lote_medicamento'] ?? null,
                    'referencia'     => "Aplicación masiva: {$programacion->titulo}",
                    'origen_type'    => 'ProgramacionSanitaria',
                    'origen_id'      => $programacion->id,
                    'corral_lote_id' => $programacion->corral_lote_id,
                ], permitirNegativo: true);
            }

            $programacion->update(['estado' => 'aplicada', 'aplicada_at' => now(), 'cabezas_aplicadas' => $animales->count()]);
        });

        return back()->with('success', "Aplicado a {$animales->count()} cabezas" . ($finRetiro ? '; retiro hasta ' . Carbon::parse($finRetiro)->format('d/m/Y') : '') . '.');
    }

    public function cancelar(ProgramacionSanitaria $programacion): RedirectResponse
    {
        abort_if($programacion->estado !== 'pendiente', 422);
        $programacion->update(['estado' => 'cancelada']);

        return back()->with('success', 'Aplicación cancelada.');
    }

    /* ---------------- Enfermedades / morbilidad ---------------- */

    public function enfermedades(Request $request): View
    {
        $periodoDesde = now()->subDays(90);
        $casos = EventoEnfermedad::with(['ganado.corralLote', 'tratamientos'])
            ->when($request->input('ver', 'abiertos') === 'abiertos', fn ($q) => $q->abiertos())
            ->orderByDesc('fecha_inicio')->paginate(30)->withQueryString();

        $casos90 = EventoEnfermedad::where('fecha_inicio', '>=', $periodoDesde)->get();
        $animalesExpuestos = Ganado::where(fn ($q) => $q->where('status', Ganado::STATUS_ACTIVO)->orWhere('updated_at', '>=', $periodoDesde))->count();

        return view('ganaderia.sanidad.enfermedades', [
            'casos'        => $casos,
            'ver'          => $request->input('ver', 'abiertos'),
            'morbilidad'   => $animalesExpuestos ? round($casos90->pluck('ganado_id')->unique()->count() / $animalesExpuestos * 100, 1) : 0,
            'recaidas'     => $casos90->where('es_recaida', true)->count(),
            'enEnfermeria' => EventoEnfermedad::abiertos()->where('en_enfermeria', true)->count(),
            'abiertos'     => EventoEnfermedad::abiertos()->count(),
            'recuperacion' => ($cerrados = $casos90->where('resultado', '!=', 'en_tratamiento'))->count()
                ? round($cerrados->where('resultado', 'recuperado')->count() / $cerrados->count() * 100, 1) : null,
            'porDiagnostico' => $casos90->groupBy('diagnostico')->map->count()->sortDesc(),
            'animales'     => Ganado::activo()->orderBy('areteID')->get(['GanadoID', 'areteID', 'categoria']),
        ]);
    }

    public function storeCaso(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ganado_id'     => ['required', 'exists:ganado,GanadoID'],
            'fecha_inicio'  => ['required', 'date', 'before_or_equal:today'],
            'diagnostico'   => ['required', 'string', 'max:150'],
            'sintomas'      => ['nullable', 'string', 'max:1000'],
            'severidad'     => ['required', Rule::in(['leve', 'moderada', 'grave'])],
            'en_enfermeria' => ['nullable', 'boolean'],
            'notas'         => ['nullable', 'string', 'max:1000'],
        ]);

        // Recaída: mismo diagnóstico en los últimos 30 días
        $data['es_recaida'] = EventoEnfermedad::where('ganado_id', $data['ganado_id'])->where('diagnostico', $data['diagnostico'])
            ->where('fecha_inicio', '>=', Carbon::parse($data['fecha_inicio'])->subDays(30))->exists();

        EventoEnfermedad::create($data + ['en_enfermeria' => $request->boolean('en_enfermeria'), 'resultado' => 'en_tratamiento', 'user_id' => auth()->id()]);

        return back()->with('success', 'Caso registrado' . ($data['es_recaida'] ? ' (marcado como RECAÍDA).' : '.') . ' Registra el tratamiento en Sanidad.');
    }

    public function cerrarCaso(Request $request, EventoEnfermedad $caso): RedirectResponse
    {
        $data = $request->validate([
            'resultado'  => ['required', Rule::in(['recuperado', 'cronico', 'murio', 'en_tratamiento'])],
            'fecha_alta' => ['nullable', 'date', 'before_or_equal:today'],
            'notas'      => ['nullable', 'string', 'max:1000'],
        ]);

        $caso->update([
            'resultado'     => $data['resultado'],
            'fecha_alta'    => $data['resultado'] === 'en_tratamiento' ? null : ($data['fecha_alta'] ?? today()),
            'en_enfermeria' => $data['resultado'] === 'en_tratamiento' ? $caso->en_enfermeria : false,
            'notas'         => trim(($caso->notas ? $caso->notas . "\n" : '') . ($data['notas'] ?? '')) ?: null,
        ]);

        $msg = 'Caso actualizado.';
        if ($data['resultado'] === 'murio') {
            $msg .= ' Recuerda registrar la baja del animal en su expediente.';
        }

        return back()->with('success', $msg);
    }
}
