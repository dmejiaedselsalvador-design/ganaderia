<?php

namespace App\Http\Controllers;

use App\Models\Engorda\CorralLote;
use App\Models\Engorda\RacionAlimento;
use App\Models\Ganado\Ganado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CorralLoteController extends Controller
{
    /**
     * Listado general de corrales y lotes de engorda con indicadores zootécnicos.
     */
    public function index(Request $request): View
    {
        $filtro = $request->input('filtro', 'todos');

        $query = CorralLote::with(['ganadosActivos', 'consumos']);

        if ($filtro !== 'todos') {
            $query->where('estado', $filtro);
        }

        $corrales = $query->latest('id')->get();
        $raciones = RacionAlimento::activo()->get();

        // Métricas globales de la engorda
        $totalCabezasEngorda = Ganado::activo()->whereNotNull('corral_lote_id')->count();
        $capacidadTotalInstalada = CorralLote::where('estado', 'Activo')->sum('capacidad_maxima');
        $porcentajeOcupacionGlobal = $capacidadTotalInstalada > 0
            ? round(($totalCabezasEngorda / $capacidadTotalInstalada) * 100, 1)
            : 0;

        $inversionTotalEngordaMXN = Ganado::activo()->whereNotNull('corral_lote_id')->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(pesoActual, 0) * COALESCE(precioCompra, 0)'));

        return view('corrales.index', compact(
            'corrales',
            'raciones',
            'filtro',
            'totalCabezasEngorda',
            'capacidadTotalInstalada',
            'porcentajeOcupacionGlobal',
            'inversionTotalEngordaMXN'
        ));
    }

    /**
     * Detalle específico de un corral con su ganado y consumos.
     */
    public function show(int $id): View
    {
        $corral = CorralLote::with([
            'ganadosActivos.pesajes',
            'ganadosActivos.factura.proveedor',
            'consumos.racion',
            'consumos.usuario'
        ])->findOrFail($id);

        $raciones = RacionAlimento::activo()->get();

        // Ganado disponible sin corral asignado para agregar al lote
        $ganadoDisponible = Ganado::activo()
            ->whereNull('corral_lote_id')
            ->orderBy('ultimoPeso', 'asc')
            ->get();

        return view('corrales.show', compact('corral', 'raciones', 'ganadoDisponible'));
    }

    /**
     * Registro de un nuevo corral / lote de engorda.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo'                  => 'required|string|max:50|unique:corrales_lotes,codigo',
            'nombre'                  => 'required|string|max:100',
            'fase_engorda'            => 'required|in:Recepcion,Crecimiento,Finalizacion',
            'capacidad_maxima'        => 'required|integer|min:1|max:500',
            'peso_promedio_objetivo'  => 'required|numeric|min:200|max:900',
            'fecha_inicio_lote'       => 'nullable|date',
            'fecha_proyectada_salida' => 'nullable|date|after_or_equal:fecha_inicio_lote',
            'estado'                  => 'required|in:Activo,Vacio,En Cuarentena,Cerrado',
            'observaciones'           => 'nullable|string|max:500',
        ]);

        CorralLote::create($validated);

        return redirect()->route('corrales.index')->with('success', "Corral {$validated['codigo']} creado con éxito.");
    }

    /**
     * Asignar uno o más animales a este corral / lote.
     */
    public function asignarGanado(Request $request, int $id): RedirectResponse
    {
        $corral = CorralLote::findOrFail($id);

        $validated = $request->validate([
            'ganado_ids'   => 'required|array|min:1',
            'ganado_ids.*' => 'exists:ganado,GanadoID',
        ]);

        // Se registra el historial de corrales de cada animal (base del costeo por lote)
        Ganado::whereIn('GanadoID', $validated['ganado_ids'])->get()
            ->filter(fn ($g) => $g->corral_lote_id !== $corral->id)
            ->each(fn ($g) => \App\Models\Ganaderia\MovimientoCorral::mover($g, $corral, 'Asignación a corral'));

        return redirect()->route('corrales.show', $corral->id)
            ->with('success', count($validated['ganado_ids']) . ' cabezas asignadas al corral con éxito.');
    }

    /**
     * Remover un animal del corral.
     */
    public function removerGanado(int $corralId, int $ganadoId): RedirectResponse
    {
        $ganado = Ganado::where('GanadoID', $ganadoId)->where('corral_lote_id', $corralId)->firstOrFail();
        \App\Models\Ganaderia\MovimientoCorral::mover($ganado, null, 'Retirado del corral');

        return redirect()->route('corrales.show', $corralId)
            ->with('success', "Animal arete {$ganado->areteID} desasignado del corral.");
    }
}
