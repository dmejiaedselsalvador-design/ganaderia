<?php

namespace App\Http\Controllers;

use App\Http\Requests\Venta\StoreVentaRequest;
use App\Models\Ganado\Ganado;
use App\Models\Venta\VentaGanado;
use App\Services\VentaService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VentaGanadoController extends Controller
{
    public function __construct(
        protected VentaService $ventaService
    ) {}

    /**
     * Listado general de ventas y métricas financieras de ingresos.
     */
    public function index(): View
    {
        $ventas = VentaGanado::with(['detalles.ganado', 'usuario'])
            ->latest('fecha_venta')
            ->paginate(15);

        $totalIngresosMXN = (float) VentaGanado::where('estado', VentaGanado::ESTADO_COMPLETADA)->sum('total_venta');
        $totalCabezasVendidas = (int) VentaGanado::where('estado', VentaGanado::ESTADO_COMPLETADA)->sum('total_cabezas');
        $totalKilosVendidos = (float) VentaGanado::where('estado', VentaGanado::ESTADO_COMPLETADA)->sum('peso_total_kg');

        return view('ventas.index', compact('ventas', 'totalIngresosMXN', 'totalCabezasVendidas', 'totalKilosVendidos'));
    }

    /**
     * Formulario interactivo para armar una venta individual o por lote.
     */
    public function create(Request $request): View
    {
        // Animales activos disponibles para vender
        $animalesDisponibles = Ganado::activo()
            ->with(['factura.proveedor'])
            ->orderBy('ultimoPeso', 'desc')
            ->get();

        // Si se enviaron IDs seleccionados desde el catálogo
        $seleccionadosIds = $request->input('seleccionados', []);
        if (is_string($seleccionadosIds)) {
            $seleccionadosIds = array_filter(explode(',', $seleccionadosIds));
        }

        $clientes = \App\Models\Ganaderia\Cliente::where('activo', true)->orderBy('nombre')->get();

        return view('ventas.create', compact('animalesDisponibles', 'seleccionadosIds', 'clientes'));
    }

    /**
     * Procesa la venta y descuenta los animales del inventario activo.
     */
    public function store(StoreVentaRequest $request): RedirectResponse
    {
        try {
            $datosVenta = $request->only([
                'cliente_id',
                'condicion_pago',
                'forma_pago',
                'dias_credito',
                'folio_cfdi',
                'cliente_nombre',
                'cliente_telefono',
                'cliente_identificacion',
                'destino',
                'fecha_venta',
                'modalidad_pago',
                'descuento',
                'notas',
            ]);

            $animalesVenta = (array) $request->input('animales', []);

            $venta = $this->ventaService->registrarVenta(
                datosVenta: $datosVenta,
                animalesVenta: $animalesVenta,
                userId: Auth::id()
            );

            return redirect()
                ->route('ventas.show', $venta->id)
                ->with('success', "¡Venta {$venta->folio_venta} completada exitosamente! Se vendieron {$venta->total_cabezas} cabezas de ganado.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Muestra el comprobante o liquidación ejecutiva de la venta realizada.
     */
    public function show(int $id): View
    {
        $venta = VentaGanado::with(['detalles.ganado', 'usuario', 'abonos', 'cliente'])->findOrFail($id);

        return view('ventas.show', compact('venta'));
    }
}
