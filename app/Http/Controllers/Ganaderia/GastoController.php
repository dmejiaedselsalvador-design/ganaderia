<?php

namespace App\Http\Controllers\Ganaderia;

use App\Http\Controllers\Controller;
use App\Models\Engorda\CorralLote;
use App\Models\Ganaderia\GastoOperativo;
use App\Models\Ganaderia\Ubicacion;
use App\Support\Csv;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GastoController extends Controller
{
    public function index(Request $request)
    {
        $desde = $request->date('desde') ?? now()->startOfMonth();
        $hasta = $request->date('hasta') ?? now();

        $query = GastoOperativo::with(['corral', 'usuario'])
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->when($request->filled('categoria'), fn ($q) => $q->where('categoria', $request->categoria))
            ->when($request->filled('corral'), fn ($q) => $request->corral === 'general'
                ? $q->whereNull('corral_lote_id') : $q->where('corral_lote_id', $request->corral))
            ->when(! $request->boolean('anulados'), fn ($q) => $q->vigentes())
            ->latest('fecha')->latest('id');

        if ($request->input('exportar') === 'csv') {
            return Csv::descargar('gastos', ['Fecha', 'Categoría', 'Concepto', 'Proveedor', 'Forma de pago', 'Folio CFDI', 'Corral', 'Monto', 'Estado'],
                $query->get()->map(fn ($g) => [$g->fecha->format('Y-m-d'), $g->categoria, $g->concepto, $g->proveedor, $g->forma_pago,
                    $g->folio_cfdi, $g->corral->codigo ?? 'General', (float) $g->monto, $g->estado]));
        }

        $vigentesPeriodo = GastoOperativo::vigentes()->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()]);

        return view('ganaderia.gastos.index', [
            'gastos'      => $query->paginate(25)->withQueryString(),
            'desde'       => $desde,
            'hasta'       => $hasta,
            'total'       => (float) (clone $vigentesPeriodo)->sum('monto'),
            'porCategoria'=> (clone $vigentesPeriodo)->selectRaw('categoria, SUM(monto) as total')->groupBy('categoria')->orderByDesc('total')->pluck('total', 'categoria'),
            'generales'   => (float) (clone $vigentesPeriodo)->whereNull('corral_lote_id')->sum('monto'),
            'corrales'    => CorralLote::orderBy('codigo')->pluck('codigo', 'id'),
            'ubicaciones' => Ubicacion::where('activo', true)->orderBy('nombre')->pluck('nombre', 'id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $gasto = GastoOperativo::create($this->validar($request) + ['user_id' => auth()->id(), 'estado' => 'vigente']);

        return redirect()->route('gastos.edit', $gasto)->with('success', 'Gasto registrado. Puedes adjuntar la factura o comprobante.');
    }

    public function edit(GastoOperativo $gasto): View
    {
        return view('ganaderia.gastos.edit', [
            'gasto'       => $gasto,
            'corrales'    => CorralLote::orderBy('codigo')->pluck('codigo', 'id'),
            'ubicaciones' => Ubicacion::orderBy('nombre')->pluck('nombre', 'id'),
        ]);
    }

    public function update(Request $request, GastoOperativo $gasto): RedirectResponse
    {
        abort_if($gasto->estado !== 'vigente', 422, 'Un gasto anulado no se puede editar.');
        $gasto->update($this->validar($request));

        return back()->with('success', 'Gasto actualizado.');
    }

    public function anular(Request $request, GastoOperativo $gasto): RedirectResponse
    {
        $data = $request->validate(['motivo' => ['required', 'string', 'max:255']], ['motivo.required' => 'Indica el motivo de la anulación.']);
        $gasto->update(['estado' => 'anulado', 'motivo_anulacion' => $data['motivo']]);

        return redirect()->route('gastos.index')->with('success', 'Gasto anulado. Queda registrado en la bitácora.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'fecha'          => ['required', 'date', 'before_or_equal:today'],
            'categoria'      => ['required', Rule::in(GastoOperativo::CATEGORIAS)],
            'concepto'       => ['required', 'string', 'max:255'],
            'monto'          => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'proveedor'      => ['nullable', 'string', 'max:150'],
            'forma_pago'     => ['nullable', Rule::in(GastoOperativo::FORMAS_PAGO)],
            'folio_cfdi'     => ['nullable', 'string', 'max:60'],
            'corral_lote_id' => ['nullable', 'exists:corrales_lotes,id'],
            'ubicacion_id'   => ['nullable', 'exists:ubicaciones,id'],
            'notas'          => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
