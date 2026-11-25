<?php

namespace App\Http\Controllers\Ganaderia;

use App\Http\Controllers\Controller;
use App\Models\Ganado\FacturaGanado;
use App\Services\Ganaderia\CostosCompraService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Costos completos de adquisición de una guía de compra y sus documentos. */
class CompraCostosController extends Controller
{
    public function __construct(private CostosCompraService $costos) {}

    public function edit(int $id): View
    {
        $factura = FacturaGanado::with(['proveedor', 'animales'])->findOrFail($id);

        return view('ganaderia.compras.costos', [
            'factura' => $factura,
            'merma'   => $this->costos->mermaGuia($factura),
            'compra'  => (float) $factura->animales->sum(fn ($a) => (float) $a->pesoActual * (float) $a->precioCompra),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $factura = FacturaGanado::findOrFail($id);

        $data = $request->validate([
            'costo_flete'    => ['nullable', 'numeric', 'min:0'],
            'costo_comision' => ['nullable', 'numeric', 'min:0'],
            'costo_tramites' => ['nullable', 'numeric', 'min:0'],
            'otros_costos'   => ['nullable', 'numeric', 'min:0'],
            'peso_origen_kg' => ['nullable', 'numeric', 'min:0'],
            'guia_reemo'     => ['nullable', 'string', 'max:60'],
            'upp_origen'     => ['nullable', 'string', 'max:40'],
            'pesos_origen'   => ['array'],
            'pesos_origen.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $factura->update(collect($data)->except('pesos_origen')->map(fn ($v, $k) => str_starts_with($k, 'costo') || $k === 'otros_costos' ? ($v ?? 0) : $v)->all());

        foreach ($data['pesos_origen'] ?? [] as $ganadoId => $peso) {
            $factura->animales()->where('GanadoID', $ganadoId)->first()?->update(['peso_origen' => $peso ?: null]);
        }
        if (! empty($data['upp_origen'])) {
            $factura->animales()->whereNull('upp_origen')->update(['upp_origen' => $data['upp_origen']]);
        }

        $this->costos->prorratear($factura->fresh());

        return back()->with('success', 'Costos guardados y repartidos entre ' . $factura->animales()->count() . ' animales según su peso.');
    }
}
