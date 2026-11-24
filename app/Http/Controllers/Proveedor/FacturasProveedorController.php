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
    public function index(): View
    {
        $proveedores = FacturaGanado::with('proveedor')
            ->withCount('animales as cantidad_ganado')
            ->pendiente()
            ->latest('id')
            ->get();

        return view('proveedores.facturas.facturasProveedor', compact('proveedores'));
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
                ->with('error', 'Ocurrió un error al procesar la liquidación: ' . $e->getMessage())
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

        $pdf = Pdf::loadView('reportes.liquidacionProveedores', $resumen);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('liquidacion-factura-' . $resumen['factura']->id . '.pdf');
    }
}
