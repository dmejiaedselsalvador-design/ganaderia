<?php

namespace App\Http\Controllers\Ganaderia;

use App\Http\Controllers\Controller;
use App\Models\Engorda\CorralLote;
use App\Models\Engorda\RacionAlimento;
use App\Models\Ganaderia\Insumo;
use App\Models\Ganaderia\MovimientoInsumo;
use App\Models\Ganaderia\RacionIngrediente;
use App\Services\Ganaderia\InventarioService;
use App\Support\Csv;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventarioController extends Controller
{
    public function __construct(private InventarioService $inventario) {}

    public function index(Request $request)
    {
        $insumos = Insumo::query()
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->tipo))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('nombre', 'like', "%{$request->q}%")->orWhere('codigo', 'like', "%{$request->q}%")))
            ->when(! $request->boolean('inactivos'), fn ($q) => $q->where('activo', true))
            ->orderBy('tipo')->orderBy('nombre')->get();

        if ($request->input('exportar') === 'csv') {
            return Csv::descargar('inventario_insumos', ['Código', 'Nombre', 'Tipo', 'Unidad', 'Existencia', 'Costo promedio', 'Valor', 'Stock mínimo'],
                $insumos->map(fn ($i) => [$i->codigo, $i->nombre, $i->tipo, $i->unidad, (float) $i->existencia, (float) $i->costo_promedio, $i->valorInventario(), (float) $i->stock_minimo]));
        }

        return view('ganaderia.inventario.index', [
            'insumos'      => $insumos,
            'valorTotal'   => $insumos->sum(fn ($i) => $i->valorInventario()),
            'bajoMinimo'   => Insumo::bajoMinimo()->get(),
            'porCaducar'   => Insumo::lotesPorCaducar(30),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $insumo = Insumo::create($this->validar($request) + ['activo' => true, 'existencia' => 0, 'costo_promedio' => 0]);

        return redirect()->route('inventario.show', $insumo)->with('success', "Insumo {$insumo->nombre} creado. Registra su primera entrada.");
    }

    public function update(Request $request, Insumo $insumo): RedirectResponse
    {
        $insumo->update($this->validar($request, $insumo) + ['activo' => $request->boolean('activo')]);

        return back()->with('success', 'Insumo actualizado.');
    }

    /** Kardex del insumo. */
    public function show(Request $request, Insumo $insumo)
    {
        $movs = $insumo->movimientos()->with(['usuario', 'corral'])
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('fecha', '>=', $request->desde))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('fecha', '<=', $request->hasta));

        if ($request->input('exportar') === 'csv') {
            return Csv::descargar('kardex_' . $insumo->codigo, ['Fecha', 'Tipo', 'Cantidad', 'Costo unitario', 'Importe', 'Existencia', 'Lote', 'Caducidad', 'Referencia', 'Usuario'],
                $movs->get()->map(fn ($m) => [$m->fecha->format('Y-m-d'), $m->tipo, (float) $m->cantidad, (float) $m->costo_unitario, $m->importe(),
                    (float) $m->existencia_resultante, $m->lote, $m->fecha_caducidad?->format('Y-m-d'), $m->referencia, $m->usuario->name ?? '']));
        }

        return view('ganaderia.inventario.show', [
            'insumo'   => $insumo,
            'movs'     => $movs->paginate(30)->withQueryString(),
            'corrales' => CorralLote::orderBy('codigo')->pluck('codigo', 'id'),
        ]);
    }

    public function movimiento(Request $request, Insumo $insumo): RedirectResponse
    {
        $data = $request->validate([
            'tipo'            => ['required', Rule::in(['entrada', 'salida', 'ajuste'])],
            'fecha'           => ['required', 'date', 'before_or_equal:today'],
            'cantidad'        => ['required', 'numeric', $request->tipo === 'ajuste' ? 'min:0' : 'gt:0'],
            'costo_unitario'  => ['required_if:tipo,entrada', 'nullable', 'numeric', 'min:0'],
            'lote'            => ['nullable', 'string', 'max:60'],
            'fecha_caducidad' => ['nullable', 'date'],
            'proveedor'       => ['nullable', 'string', 'max:150'],
            'referencia'      => ['nullable', 'string', 'max:150'],
            'corral_lote_id'  => ['nullable', 'exists:corrales_lotes,id'],
            'notas'           => ['nullable', 'string', 'max:500'],
        ], ['cantidad.gt' => 'La cantidad debe ser mayor a cero.', 'costo_unitario.required_if' => 'Indica el costo unitario de la compra.']);

        $extra = collect($data)->only(['fecha', 'lote', 'fecha_caducidad', 'proveedor', 'referencia', 'corral_lote_id', 'notas'])->all();

        try {
            match ($data['tipo']) {
                'entrada' => $this->inventario->entrada($insumo, (float) $data['cantidad'], (float) $data['costo_unitario'], $extra),
                'salida'  => $this->inventario->salida($insumo, (float) $data['cantidad'], $extra),
                'ajuste'  => $this->inventario->ajustar($insumo, (float) $data['cantidad'], $extra + ['referencia' => $data['referencia'] ?? 'Conteo físico']),
            };
        } catch (Exception $e) {
            return back()->withErrors(['cantidad' => $e->getMessage()])->withInput();
        }

        return back()->with('success', 'Movimiento registrado.');
    }

    /* ---------------- Ingredientes de raciones ---------------- */

    public function ingredientes(Request $request, RacionAlimento $racion): View
    {
        return view('ganaderia.inventario.racion', [
            'racion'  => $racion->load('ingredientes.insumo'),
            'insumos' => Insumo::activos()->whereIn('tipo', ['alimento', 'otro'])->orderBy('nombre')->get(),
            'volver'  => \App\Support\Volver::url($request),
        ]);
    }

    public function guardarIngredientes(Request $request, RacionAlimento $racion): RedirectResponse
    {
        $porCantidad = $request->input('modo') === 'cantidad';

        $data = $request->validate([
            'modo'                      => ['nullable', Rule::in(['porcentaje', 'cantidad'])],
            'ingredientes'              => ['array'],
            'ingredientes.*.insumo_id'  => ['required', 'distinct', 'exists:insumos,id'],
            'ingredientes.*.porcentaje' => [Rule::requiredIf(! $porCantidad), 'nullable', 'numeric', 'gt:0', 'max:100'],
            'ingredientes.*.cantidad'   => [Rule::requiredIf($porCantidad), 'nullable', 'numeric', 'gt:0', 'max:1000000'],
            'ingredientes.*.unidad'     => [Rule::requiredIf($porCantidad), 'nullable', 'string', 'max:10'],
            'actualizar_costo'          => ['nullable', 'boolean'],
        ], [
            'ingredientes.*.insumo_id.distinct' => 'Un ingrediente está repetido.',
            'ingredientes.*.cantidad.required'  => 'Falta la cantidad de un ingrediente.',
            'ingredientes.*.cantidad.gt'        => 'Las cantidades deben ser mayores a cero.',
        ]);

        $lista = collect($data['ingredientes'] ?? []);

        if ($porCantidad) {
            // Receta por cantidades (ej. 2 kg maíz, 2 l agua, 2 lb sal): todo se pasa a kg y se calcula el %
            $insumos = Insumo::whereIn('id', $lista->pluck('insumo_id'))->get()->keyBy('id');
            $lista = $lista->map(function ($ing) use ($insumos) {
                $insumo = $insumos[$ing['insumo_id']];
                if (! in_array($ing['unidad'], $insumo->unidadesReceta(), true)) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['ingredientes' => "La unidad «{$ing['unidad']}» no aplica para {$insumo->nombre}."]);
                }

                return $ing + ['kg' => $insumo->kgDe((float) $ing['cantidad'], $ing['unidad'])];
            });
            $totalKg = $lista->sum('kg');
            $lista = $lista->map(fn ($ing) => [
                'insumo_id'      => $ing['insumo_id'],
                'porcentaje'     => round($ing['kg'] / max($totalKg, 0.000001) * 100, 3),
                'cantidad'       => $ing['cantidad'],
                'unidad_captura' => $ing['unidad'],
            ]);
        } else {
            $lista = $lista->map(fn ($ing) => ['insumo_id' => $ing['insumo_id'], 'porcentaje' => $ing['porcentaje'], 'cantidad' => null, 'unidad_captura' => null]);
            $suma = round($lista->sum('porcentaje'), 3);
            if ($lista->isNotEmpty() && abs($suma - 100) > 0.5) {
                return back()->withErrors(['ingredientes' => "Los porcentajes suman {$suma}%. Deben sumar 100%."])->withInput();
            }
        }

        $racion->ingredientes()->delete();
        foreach ($lista as $ing) {
            RacionIngrediente::create(['racion_id' => $racion->id] + $ing);
        }

        $mensaje = $porCantidad && $lista->isNotEmpty()
            ? 'Receta guardada. Rinde ' . number_format($totalKg, 2) . ' kg por bache; el sistema calculó el % de cada ingrediente.'
            : 'Fórmula guardada.';
        if ($request->boolean('actualizar_costo') && $lista->isNotEmpty()) {
            // costo por kg de cada ingrediente (si se maneja en toneladas, se divide entre 1,000)
            $costo = $racion->fresh('ingredientes.insumo')->ingredientes->sum(fn ($i) => $i->insumo->costoKg() * (float) $i->porcentaje / 100);
            $racion->update(['costo_por_kg' => round($costo, 2)]);
            $mensaje .= ' Costo por kg actualizado a $ ' . number_format($costo, 2) . ' según el costo promedio de los ingredientes.';
        }

        // Si llegó desde otra pantalla (p. ej. el plan de un corral), regresa allá
        if ($volver = \App\Support\Volver::url($request)) {
            return redirect()->to($volver)->with('success', $mensaje);
        }

        return back()->with('success', $mensaje);
    }

    private function validar(Request $request, ?Insumo $insumo = null): array
    {
        $request->merge(['codigo' => mb_strtoupper(trim((string) $request->codigo))]);
        if (isset(Insumo::KG_POR_UNIDAD_PESO[$request->input('unidad')])) {
            $request->merge(['factor_kg' => Insumo::KG_POR_UNIDAD_PESO[$request->input('unidad')]]);
        }

        $d = $request->validate([
            'codigo'       => ['required', 'string', 'max:40', Rule::unique('insumos', 'codigo')->ignore($insumo?->id)],
            'nombre'       => ['required', 'string', 'max:150'],
            'tipo'         => ['required', Rule::in(array_keys(Insumo::TIPOS))],
            'unidad'       => ['required', Rule::in(Insumo::UNIDADES)],
            'materia_seca_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'factor_kg'    => ['nullable', 'numeric', 'min:0.001'],
            'stock_minimo' => ['nullable', 'numeric', 'min:0'],
            'dias_retiro'  => ['nullable', 'integer', 'min:0', 'max:365'],
            'presentacion' => ['nullable', 'string', 'max:100'],
            'notas'        => ['nullable', 'string', 'max:500'],
        ]);
        $d['factor_kg'] = $d['factor_kg'] ?? 1;
        $d['controla_existencia'] = ! $request->boolean('sin_existencia');

        return $d;
    }
}
