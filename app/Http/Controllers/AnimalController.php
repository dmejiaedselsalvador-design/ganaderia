<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ganado\StoreGanadoBatchRequest;
use App\Models\Ganado\BajaGanado;
use App\Models\Ganado\FacturaGanado;
use App\Models\Ganado\Ganado;
use App\Models\Ganado\ProveedorGanado;
use App\Services\GanadoService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnimalController extends Controller
{
    public function __construct(
        protected GanadoService $ganadoService
    ) {}

    /**
     * Muestra el catálogo de ganado con filtros operativos en tiempo real
     * (Activos, Listos para Venta, Vendidos, Bajas/Mortalidad).
     */
    public function index(Request $request): View
    {
        $filtro = $request->input('filtro', 'activos');

        // Conteos globales para los tabs de filtro
        $conteos = [
            'todos'        => Ganado::count(),
            'activos'      => Ganado::activo()->count(),
            'listos_venta' => Ganado::listoParaVenta()->count(),
            'vendidos'     => Ganado::vendido()->count(),
            'muertos'      => Ganado::muerto()->count(),
        ];

        $query = Ganado::with(['factura.proveedor', 'ultimoPesaje']);

        match ($filtro) {
            'todos'        => null,
            'listos_venta' => $query->listoParaVenta(),
            'vendidos'     => $query->vendido(),
            'muertos'      => $query->muerto(),
            default        => $query->activo(),
        };

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('areteID', 'LIKE', "%{$buscar}%")
                  ->orWhere('raza', 'LIKE', "%{$buscar}%")
                  ->orWhere('categoria', 'LIKE', "%{$buscar}%");
            });
        }

        $ganados = $query->latest('GanadoID')->paginate(20)->withQueryString();

        return view('animals.index', compact('ganados', 'filtro', 'conteos'));
    }

    /**
     * Muestra el expediente operativo y perfil individual de un animal:
     * - Historial de pesajes y GDP (Ganancia Diaria de Peso).
     * - Estatus de peso objetivo de venta.
     * - Registro de baja/mortalidad o venta si aplica.
     */
    public function perfil(Request $request): View
    {
        $ganadoSeleccionado = null;

        $relations = [
            'factura.proveedor',
            'pesajes.usuario',
            'baja.usuario',
            'detalleVenta.venta',
            'corralLote',
            'tratamientos.usuario',
            'evaluacionCanal',
        ];

        if ($request->filled('arete')) {
            $ganadoSeleccionado = Ganado::with($relations)->where('areteID', $request->input('arete'))->first();
        } elseif ($request->filled('ganado_id')) {
            $ganadoSeleccionado = Ganado::with($relations)->find($request->input('ganado_id'));
        }

        $causasMuerte = BajaGanado::CAUSAS_COMUNES;

        return view('animals.perfil', compact('ganadoSeleccionado', 'causasMuerte'));
    }

    /**
     * Formulario de ingreso de un nuevo lote de ganado vinculado a una factura o proveedor.
     */
    public function nuevoGanado(Request $request): View
    {
        $factura = null;
        $proveedorSeleccionado = null;

        if ($request->filled('factura_id')) {
            $factura = FacturaGanado::with('proveedor')->find($request->factura_id);
            if ($factura) {
                $proveedorSeleccionado = $factura->proveedor;
            }
        } elseif ($request->filled('proveedor_id')) {
            $proveedorSeleccionado = ProveedorGanado::find($request->proveedor_id);
        }

        return view('compras.nuevoGanado', compact('factura', 'proveedorSeleccionado'));
    }

    /**
     * Guarda un lote de animales en una transacción controlada.
     */
    public function store(StoreGanadoBatchRequest $request): RedirectResponse
    {
        try {
            $facturaId = (int) ($request->input('factura_id') ?? $request->input('factura-id'));
            $fechaCompra = $request->input('purchase_date') ?: now()->toDateString();
            $animals = (array) $request->input('animals', []);

            $this->ganadoService->registrarLote($facturaId, $fechaCompra, $animals);

            return redirect()
                ->route('compras.ganado.index')
                ->with('success', 'Lote de ganado registrado exitosamente.');
        } catch (Exception $e) {
            return back()
                ->withErrors(['error' => 'Ocurrió un error al registrar el lote: ' . $e->getMessage()])
                ->withInput();
        }
    }
}
