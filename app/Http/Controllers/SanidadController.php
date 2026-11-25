<?php

namespace App\Http\Controllers;

use App\Models\Ganado\Ganado;
use App\Models\Sanidad\TratamientoSanitario;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SanidadController extends Controller
{
    /**
     * Módulo de Sanidad, Bioseguridad y Control de Tiempos de Retiro
     */
    public function index(Request $request): View
    {
        $filtro = $request->input('filtro', 'todos');

        $query = TratamientoSanitario::with(['ganado', 'usuario'])->latest('fecha_aplicacion');

        if ($filtro === 'en_retiro') {
            $query->where('fecha_fin_retiro', '>=', now()->toDateString());
        } elseif ($filtro !== 'todos') {
            $query->where('tipo_evento', $filtro);
        }

        $tratamientos = $query->paginate(20)->withQueryString();

        // Alertas de retiro veterinario
        $animalesEnRetiro = TratamientoSanitario::with('ganado')
            ->where('fecha_fin_retiro', '>=', now()->toDateString())
            ->orderBy('fecha_fin_retiro', 'asc')
            ->get();

        $totalTratamientos = TratamientoSanitario::count();
        $inversionSanitariaMXN = (float) TratamientoSanitario::sum('costo_medicamento_mxn');

        $ganadosActivos = Ganado::activo()->orderBy('areteID', 'asc')->get();
        $medicamentos = \App\Models\Ganaderia\Insumo::activos()->where('tipo', 'medicamento')->orderBy('nombre')->get();
        $casosAbiertos = \App\Models\Ganaderia\EventoEnfermedad::abiertos()->with('ganado:GanadoID,areteID')->get();

        return view('sanidad.index', compact(
            'tratamientos',
            'animalesEnRetiro',
            'totalTratamientos',
            'inversionSanitariaMXN',
            'ganadosActivos',
            'filtro',
            'medicamentos',
            'casosAbiertos'
        ));
    }

    /**
     * Registrar un nuevo evento sanitario / tratamiento.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ganado_id'              => 'required|exists:ganado,GanadoID',
            'tipo_evento'            => 'required|in:Recepcion,Vacunacion,Desparasitacion,Vitamina,Antibiotico,Implante,Tratamiento_Clinico',
            'medicamento_producto'   => 'required|string|max:150',
            'dosis_aplicada'         => 'required|string|max:50',
            'via_administracion'     => 'required|string|max:50',
            'fecha_aplicacion'       => 'required|date',
            'dias_retiro_rastro'     => 'required|integer|min:0|max:120',
            'costo_medicamento_mxn'  => 'required|numeric|min:0|max:10000',
            'veterinario_responsable'=> 'nullable|string|max:150',
            'diagnostico_motivo'     => 'nullable|string|max:500',
            'insumo_id'              => 'nullable|exists:insumos,id',
            'cantidad_insumo'        => 'nullable|numeric|min:0',
            'lote_medicamento'       => 'nullable|string|max:60',
            'caducidad_medicamento'  => 'nullable|date',
            'evento_enfermedad_id'   => 'nullable|exists:eventos_enfermedad,id',
        ]);

        $diasRetiro = (int) $validated['dias_retiro_rastro'];
        $fechaFinRetiro = $diasRetiro > 0
            ? Carbon::parse($validated['fecha_aplicacion'])->addDays($diasRetiro)->toDateString()
            : null;

        $tratamiento = TratamientoSanitario::create([
            'ganado_id'               => $validated['ganado_id'],
            'insumo_id'               => $validated['insumo_id'] ?? null,
            'cantidad_insumo'         => $validated['cantidad_insumo'] ?? null,
            'lote_medicamento'        => $validated['lote_medicamento'] ?? null,
            'caducidad_medicamento'   => $validated['caducidad_medicamento'] ?? null,
            'evento_enfermedad_id'    => $validated['evento_enfermedad_id'] ?? null,
            'tipo_evento'             => $validated['tipo_evento'],
            'medicamento_producto'    => $validated['medicamento_producto'],
            'dosis_aplicada'          => $validated['dosis_aplicada'],
            'via_administracion'      => $validated['via_administracion'],
            'fecha_aplicacion'        => $validated['fecha_aplicacion'],
            'dias_retiro_rastro'      => $diasRetiro,
            'fecha_fin_retiro'        => $fechaFinRetiro,
            'costo_medicamento_mxn'   => $validated['costo_medicamento_mxn'],
            'veterinario_responsable' => $validated['veterinario_responsable'] ?? null,
            'diagnostico_motivo'      => $validated['diagnostico_motivo'] ?? null,
            'user_id'                 => Auth::id(),
        ]);

        // Descuenta el medicamento del inventario
        if ($tratamiento->insumo_id && (float) $tratamiento->cantidad_insumo > 0) {
            app(\App\Services\Ganaderia\InventarioService::class)->salida($tratamiento->insumo, (float) $tratamiento->cantidad_insumo, [
                'fecha'       => $tratamiento->fecha_aplicacion,
                'lote'        => $tratamiento->lote_medicamento,
                'referencia'  => "Tratamiento #{$tratamiento->id}",
                'origen_type' => 'TratamientoSanitario',
                'origen_id'   => $tratamiento->id,
            ], permitirNegativo: true);
        }

        return back()->with('success', "Tratamiento registrado correctamente.");
    }
}
