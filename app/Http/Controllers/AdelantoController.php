<?php

namespace App\Http\Controllers;

use App\Http\Requests\Adelanto\StoreAdelantoRequest;
use App\Models\Ganado\Adelanto;
use App\Models\Ganado\ProveedorGanado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdelantoController extends Controller
{
    /**
     * Muestra la lista completa de adelantos por ítem, con balance y sumas monetarias.
     */
    public function index(Request $request): View
    {
        $filtro = $request->input('filtro', 'todos');
        $proveedorId = $request->input('proveedor_id');
        $buscar = $request->input('buscar');

        $query = Adelanto::with('proveedor')->latest('date')->latest('id');

        if ($filtro === 'pendientes') {
            $query->whereIn('status', [Adelanto::STATUS_ENTREGADO, Adelanto::STATUS_PARCIAL]);
        } elseif ($filtro === 'liquidados') {
            $query->where('status', Adelanto::STATUS_LIQUIDADO);
        }

        if ($proveedorId) {
            $query->where('proveedor_id', $proveedorId);
        }

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('concepto', 'like', "%{$buscar}%")
                  ->orWhereHas('proveedor', function ($sub) use ($buscar) {
                      $sub->where('nombreProveedor', 'like', "%{$buscar}%")
                          ->orWhere('nombreContacto', 'like', "%{$buscar}%");
                  });
            });
        }

        $adelantos = $query->paginate(20)->withQueryString();

        // Totales monetarios consolidados en Pesos Mexicanos (MXN)
        $totalDineroEntregadoMXN = (float) Adelanto::sum('dinero');
        // Lo aplicado se toma de las liquidaciones (un anticipo puede descontarse en partes)
        $totalDineroLiquidadoMXN = (float) \App\Models\Ganado\AdelantoFactura::sum('montoAplicado');
        $saldoPendienteMXN       = max(0, $totalDineroEntregadoMXN - $totalDineroLiquidadoMXN);
        $totalItems              = Adelanto::count();
        $totalPendientesItems    = Adelanto::whereIn('status', [Adelanto::STATUS_ENTREGADO, Adelanto::STATUS_PARCIAL])->count();

        $proveedores = ProveedorGanado::activo()->orderBy('nombreProveedor')->get();

        return view('adelantos.index', compact(
            'adelantos',
            'totalDineroEntregadoMXN',
            'totalDineroLiquidadoMXN',
            'saldoPendienteMXN',
            'totalItems',
            'totalPendientesItems',
            'proveedores',
            'filtro',
            'proveedorId',
            'buscar'
        ));
    }

    /**
     * Formulario para emitir un adelanto o préstamo a un proveedor específico o general.
     */
    public function create(?ProveedorGanado $proveedor = null): View
    {
        $proveedores = ProveedorGanado::activo()->orderBy('nombreProveedor')->get();

        return view('adelantos.prestamos', compact('proveedor', 'proveedores'));
    }

    /**
     * Registra un nuevo adelanto financiero.
     */
    public function store(StoreAdelantoRequest $request): RedirectResponse
    {
        $adelanto = Adelanto::create([
            'proveedor_id' => $request->input('supplier_id'),
            'date'         => $request->input('advance_date'),
            'concepto'     => $request->input('concept'),
            'dinero'       => $request->input('amount'),
            'status'       => Adelanto::STATUS_ENTREGADO,
        ]);

        return redirect()
            ->route('adelantos.index')
            ->with('success', "Adelanto #{$adelanto->id} por " . formatoPesos($adelanto->dinero) . " registrado con éxito.");
    }

    /**
     * Permite pagar o liquidar un ítem de adelanto individualmente
     * sin necesidad de ir hasta la guía del ganado ni tener que liquidar todo el ganado.
     */
    public function pagar(int $id): RedirectResponse
    {
        $adelanto = Adelanto::findOrFail($id);

        if ($adelanto->status === Adelanto::STATUS_LIQUIDADO) {
            return back()->with('info', "El adelanto #{$adelanto->id} ya se encontraba liquidado.");
        }

        $adelanto->update([
            'status' => Adelanto::STATUS_LIQUIDADO,
        ]);

        return back()->with('success', "¡Adelanto #{$adelanto->id} (" . formatoPesos($adelanto->dinero) . ") pagado y liquidado con éxito sin afectar guías pendientes!");
    }
}
