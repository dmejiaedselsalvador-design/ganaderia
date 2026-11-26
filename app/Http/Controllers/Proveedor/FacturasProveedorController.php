<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Factura\StoreFacturaRequest;
use App\Models\Ganado\FacturaGanado;
use App\Models\Ganado\ProveedorGanado;
use App\Services\LiquidacionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class FacturasProveedorController extends Controller
{
    public function __construct(
        protected LiquidacionService $liquidacionService
    ) {}

    /**
     * Listado de guías y facturas de ganado pendientes de liquidación.
     */
    public function index(Request $request): View
    {
        // Por defecto: guías pendientes de liquidar. Opciones: liquidadas, anuladas o todas.
        $estado = $request->input('estado', 'pendientes');
        $desde = $request->date('desde');
        $hasta = $request->date('hasta');
        $q = trim((string) $request->input('q'));

        $base = FacturaGanado::query()
            ->when($desde, fn ($w) => $w->whereDate('fechaFactura', '>=', $desde))
            ->when($hasta, fn ($w) => $w->whereDate('fechaFactura', '<=', $hasta))
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('numeroFactura', 'like', "%{$q}%")
                ->orWhereHas('proveedor', fn ($p) => $p->where('nombreProveedor', 'like', "%{$q}%")->orWhere('nombreContacto', 'like', "%{$q}%"))));

        $conteos = (clone $base)->selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado');

        $proveedores = (clone $base)
            ->with(['proveedor', 'animales', 'adelantos'])
            ->withCount('animales as cantidad_ganado')
            ->when($estado === 'pendientes', fn ($w) => $w->whereIn('estado', [FacturaGanado::ESTADO_PENDIENTE, FacturaGanado::ESTADO_PARCIAL]))
            ->when($estado === 'liquidadas', fn ($w) => $w->where('estado', FacturaGanado::ESTADO_LIQUIDADO))
            ->when($estado === 'anuladas', fn ($w) => $w->where('estado', FacturaGanado::ESTADO_ANULADA))
            ->orderByDesc('fechaFactura')->orderByDesc('id')
            ->paginate(25)->withQueryString();

        return view('proveedores.facturas.facturasProveedor', compact('proveedores', 'estado', 'desde', 'hasta', 'q', 'conteos'));
    }

    /** Cambia el número / nombre de la guía (es solo un texto: el ganado está ligado por el id interno). */
    public function actualizarNumero(Request $request, int $id): RedirectResponse
    {
        $factura = FacturaGanado::findOrFail($id);
        $data = $request->validate([
            'numero' => ['required', 'string', 'max:100', \Illuminate\Validation\Rule::unique('facturaGanado', 'numeroFactura')->ignore($factura->id)],
        ], ['numero.unique' => 'Ya existe otra guía con ese número.', 'numero.required' => 'Escribe el número de la guía.']);

        $anterior = $factura->numeroFactura;
        $factura->update(['numeroFactura' => trim($data['numero'])]);

        return back()->with('success', "Guía «{$anterior}» ahora se llama «{$factura->numeroFactura}». Su ganado y costos siguen ligados.");
    }

    /**
     * Formulario para crear una nueva guía / factura de ganado.
     */
    public function crearFactura(): View
    {
        $proveedores = ProveedorGanado::activo()->orderBy('nombreProveedor')->get();

        return view('proveedores.facturas.FacturaCreate', compact('proveedores'));
    }

    /**
     * Guarda una nueva factura y redirige al registro de animales del lote.
     */
    public function storeFactura(StoreFacturaRequest $request): RedirectResponse
    {
        try {
            $factura = FacturaGanado::create([
                'proveedorID'   => $request->input('proveedor_id'),
                'fechaFactura'  => $request->input('fecha_factura'),
                'numeroFactura' => $request->input('factura'),
                'notas'         => $request->input('observaciones'),
                'guia_reemo'    => $request->input('guia_reemo') ?: null,
                'upp_origen'    => $request->input('upp_origen') ?: null,
                'estado'        => FacturaGanado::ESTADO_PENDIENTE,
            ]);

            return redirect()
                ->route('compras.nuevo.ganado', ['factura_id' => $factura->id])
                ->with('success', 'Factura creada exitosamente. Ahora puedes registrar el ganado asociado a esta factura.');
        } catch (Exception $e) {
            Log::error('Error al registrar factura de ganado: ' . $e->getMessage(), [
                'exception' => $e,
                'request'   => $request->except(['_token']),
            ]);

            return back()
                ->withErrors(['error' => 'Ocurrió un error inesperado al procesar la factura. Por favor, intenta de nuevo.'])
                ->withInput();
        }
    }

    /**
     * Muestra la pantalla interactiva de revisión y liquidación de una factura.
     */
    public function liquidar(int $id): View
    {
        $resumen = $this->liquidacionService->calcularResumenFactura($id);
        $resumen['liquidada'] = $resumen['factura']->estado === FacturaGanado::ESTADO_LIQUIDADO;

        return view('proveedores.facturas.liquidar', $resumen);
    }

    /**
     * Procesa la liquidación formal y amortización de adelantos.
     */
    public function liquidarFactura(Request $request, int $id): RedirectResponse
    {
        try {
            $adelantosSeleccionados = (array) $request->input('adelantos', []);

            $this->liquidacionService->liquidarFactura($id, $adelantosSeleccionados);

            return redirect()
                ->route('proveedores.facturas.liquidar.exito', $id)
                ->with('success', 'Factura liquidada exitosamente.');
        } catch (Exception $e) {
            Log::error('Error al procesar liquidación: ' . $e->getMessage());

            return back()
                ->with('aviso', 'No se pudo liquidar: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Pantalla de comprobante y confirmación de liquidación exitosa.
     */
    public function liquidarExito(int $id): View
    {
        $factura = FacturaGanado::with('proveedor')->findOrFail($id);

        return view('proveedores.facturas.ExitoLiquidaciones', compact('factura'));
    }

    /**
     * Genera el PDF imprimible del reporte de liquidación del lote.
     */
    public function generarPdf(int $id): Response
    {
        $resumen = $this->liquidacionService->calcularResumenFactura($id);

        $pdf = \App\Support\DocumentoPdf::crear('reportes.liquidacionProveedores', $resumen);

        return $pdf->stream('liquidacion-guia-' . \Illuminate\Support\Str::slug($resumen['factura']->numeroFactura ?: $resumen['factura']->id) . '.pdf');
    }
}
