<?php

namespace App\Http\Controllers\Ganaderia;

use App\Http\Controllers\Controller;
use App\Models\Correccion;
use App\Models\Engorda\ConsumoAlimento;
use App\Models\Engorda\CorralLote;
use App\Models\Engorda\RacionAlimento;
use App\Models\Ganado\BajaGanado;
use App\Models\Ganado\Ganado;
use App\Models\Ganado\PesajeGanado;
use App\Models\Sanidad\TratamientoSanitario;
use App\Models\Venta\VentaGanado;
use App\Services\CorreccionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

/**
 * Corrección de registros (permiso registros.corregir: Súper Administrador, Dueño y Gerente por defecto).
 * Busca por arete o por corral/fechas y permite corregir o anular con motivo obligatorio.
 */
class CorreccionController extends Controller
{
    public function __construct(private CorreccionService $servicio) {}

    public function index(Request $request): View
    {
        $arete = trim((string) $request->input('arete'));
        $ganado = $arete !== ''
            ? Ganado::with(['pesajes' => fn ($q) => $q->orderByDesc('fecha_pesaje')->orderByDesc('id'), 'tratamientos' => fn ($q) => $q->orderByDesc('fecha_aplicacion'),
                'baja', 'detalleVenta.venta', 'corralLote'])->where('areteID', $arete)->first()
            : null;

        $desde = $request->date('desde') ?? today()->subDays(6);
        $hasta = $request->date('hasta') ?? today();
        $corralId = $request->integer('corral') ?: null;
        $servidos = ConsumoAlimento::with(['corralLote', 'racion'])
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->when($corralId, fn ($q) => $q->where('corral_lote_id', $corralId))
            ->orderByDesc('fecha')->orderBy('corral_lote_id')->limit(200)->get();

        $ventas = VentaGanado::where('estado', VentaGanado::ESTADO_COMPLETADA)->latest('fecha_venta')->latest('id')->limit(30)->get();

        return view('ganaderia.correcciones.index', [
            'arete' => $arete, 'ganado' => $ganado, 'servidos' => $servidos, 'desde' => $desde, 'hasta' => $hasta, 'corralId' => $corralId,
            'corrales' => CorralLote::orderBy('codigo')->get(['id', 'codigo', 'nombre']),
            'raciones' => RacionAlimento::orderBy('codigo')->get(['id', 'codigo', 'nombre']),
            'ventas' => $ventas,
            'historial' => Correccion::with('usuario')
                ->when($ganado, fn ($q) => $q->where(fn ($w) => $w->where('ganado_id', $ganado->GanadoID)
                    ->orWhere(fn ($v) => $v->where('tipo', 'venta')->whereIn('registro_id', \App\Models\Venta\DetalleVentaGanado::where('ganado_id', $ganado->GanadoID)->pluck('venta_id')->map(fn ($i) => (string) $i)))))
                ->latest('id')->limit(30)->get(),
        ]);
    }

    public function pesaje(Request $request, PesajeGanado $pesaje): RedirectResponse
    {
        return $this->ejecutar($request, function (string $motivo) use ($request, $pesaje) {
            if ($request->boolean('anular')) {
                $this->servicio->anularPesaje($pesaje, $motivo);

                return 'Pesaje anulado y ganancia de peso recalculada.';
            }
            $d = $request->validate([
                'peso' => ['required', 'numeric', 'min:20', 'max:1500'], 'fecha' => ['required', 'date', 'before_or_equal:today'],
                'condicion_corporal' => ['nullable', 'numeric', 'min:1', 'max:5'],
            ]);
            $this->servicio->corregirPesaje($pesaje, $d, $motivo);

            return 'Pesaje corregido y ganancia de peso recalculada.';
        });
    }

    public function servido(Request $request, ConsumoAlimento $consumo): RedirectResponse
    {
        return $this->ejecutar($request, function (string $motivo) use ($request, $consumo) {
            if ($request->boolean('anular')) {
                $this->servicio->anularServido($consumo, $motivo);

                return 'Servido anulado; los ingredientes regresaron a bodega.';
            }
            $d = $request->validate([
                'fecha' => ['required', 'date', 'before_or_equal:today'], 'racion_id' => ['required', 'exists:raciones_alimento,id'],
                'kilos_servidos' => ['required', 'numeric', 'min:1', 'max:50000'], 'kilos_rechazo' => ['nullable', 'numeric', 'min:0', 'lte:kilos_servidos'],
            ]);
            $this->servicio->corregirServido($consumo, $d, $motivo);

            return 'Servido corregido; la bodega se ajustó.';
        });
    }

    public function tratamiento(Request $request, TratamientoSanitario $tratamiento): RedirectResponse
    {
        return $this->ejecutar($request, function (string $motivo) use ($request, $tratamiento) {
            if ($request->boolean('anular')) {
                $this->servicio->anularTratamiento($tratamiento, $motivo);

                return 'Tratamiento anulado; el medicamento regresó a bodega.';
            }
            $d = $request->validate([
                'fecha' => ['required', 'date', 'before_or_equal:today'], 'cantidad_insumo' => ['nullable', 'numeric', 'min:0', 'max:100000'],
                'dosis' => ['nullable', 'string', 'max:50'], 'dias_retiro_rastro' => ['nullable', 'integer', 'min:0', 'max:365'],
            ]);
            $this->servicio->corregirTratamiento($tratamiento, $d, $motivo);

            return 'Tratamiento corregido; bodega y fecha de retiro ajustadas.';
        });
    }

    public function baja(Request $request, BajaGanado $baja): RedirectResponse
    {
        return $this->ejecutar($request, function (string $motivo) use ($baja) {
            $this->servicio->revertirBaja($baja, $motivo);

            return 'Baja revertida: el animal vuelve a estar activo.';
        });
    }

    public function venta(Request $request, VentaGanado $venta): RedirectResponse
    {
        return $this->ejecutar($request, function (string $motivo) use ($venta) {
            $this->servicio->anularVenta($venta, $motivo);

            return "Venta {$venta->folio_venta} anulada; los animales regresaron al hato y se cancelaron sus abonos.";
        });
    }

    public function compra(Request $request, int $id): RedirectResponse
    {
        $ganado = Ganado::findOrFail($id);

        return $this->ejecutar($request, function (string $motivo) use ($request, $ganado) {
            $d = $request->validate([
                'peso' => ['required', 'numeric', 'min:20', 'max:1500'], 'precio' => ['required', 'numeric', 'min:0', 'max:1000'],
                'fecha' => ['required', 'date', 'before_or_equal:today'],
                'categoria' => ['required', Rule::in(array_merge(Ganado::CATEGORIAS_MACHOS, Ganado::CATEGORIAS_HEMBRAS))],
                'raza' => ['nullable', 'string', 'max:100'],
            ]);
            $this->servicio->corregirCompra($ganado, $d, $motivo);

            return 'Datos de compra corregidos; el costo y la ganancia de peso se recalcularon.';
        });
    }

    /** Valida el motivo, ejecuta y regresa a la misma búsqueda. */
    private function ejecutar(Request $request, callable $accion): RedirectResponse
    {
        $request->validate(['motivo' => ['required', 'string', 'min:5', 'max:500']], [
            'motivo.required' => 'Escribe el motivo de la corrección.', 'motivo.min' => 'Explica un poco más el motivo de la corrección.',
        ]);

        try {
            $mensaje = $accion(trim($request->input('motivo')));
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            return back()->withInput()->with('aviso', $e->getMessage());
        }

        return back()->with('success', $mensaje);
    }
}
