<?php

namespace App\Services;

use App\Models\Correccion;
use App\Models\Engorda\ConsumoAlimento;
use App\Models\Engorda\RacionAlimento;
use App\Models\Ganaderia\Insumo;
use App\Models\Ganado\BajaGanado;
use App\Models\Ganado\FacturaGanado;
use App\Models\Ganado\Ganado;
use App\Models\Ganado\PesajeGanado;
use App\Models\Sanidad\TratamientoSanitario;
use App\Models\Venta\VentaGanado;
use App\Services\Ganaderia\CobranzaService;
use App\Services\Ganaderia\CostosCompraService;
use App\Services\Ganaderia\ExplosionMaterialesService;
use App\Services\Ganaderia\InventarioService;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Corrección de registros mal capturados (solo con el permiso registros.corregir).
 * Nunca se pierde el rastro: cada cambio guarda el antes, el después, el motivo y quién lo hizo,
 * y los efectos se recalculan (bodega, ganancia de peso, saldos, estatus del animal).
 */
class CorreccionService
{
    public function __construct(
        private InventarioService $inventario,
        private ExplosionMaterialesService $explosion,
    ) {}

    // ───────────── Pesajes ─────────────

    public function corregirPesaje(PesajeGanado $pesaje, array $datos, string $motivo): void
    {
        DB::transaction(function () use ($pesaje, $datos, $motivo) {
            $antes = $pesaje->only(['peso', 'fecha_pesaje', 'condicion_corporal', 'gdp']);
            $pesaje->update([
                'peso'               => $datos['peso'],
                'fecha_pesaje'       => $datos['fecha'],
                'condicion_corporal' => $datos['condicion_corporal'] ?? $pesaje->condicion_corporal,
            ]);
            $this->recalcularPesajes($pesaje->ganado_id);
            $pesaje->refresh();
            $this->anotar('pesaje', $pesaje->id, 'corregido', "Pesaje del {$pesaje->fecha_pesaje->format('d/m/Y')}: {$antes['peso']} → {$pesaje->peso} kg",
                $motivo, $antes, $pesaje->only(['peso', 'fecha_pesaje', 'condicion_corporal', 'gdp']), $pesaje->ganado_id);
        });
    }

    public function anularPesaje(PesajeGanado $pesaje, string $motivo): void
    {
        DB::transaction(function () use ($pesaje, $motivo) {
            $antes = $pesaje->toArray();
            $ganadoId = $pesaje->ganado_id;
            $pesaje->delete();
            $this->recalcularPesajes($ganadoId);
            $this->anotar('pesaje', $antes['id'], 'anulado', 'Pesaje del ' . Carbon::parse($antes['fecha_pesaje'])->format('d/m/Y') . " ({$antes['peso']} kg) anulado",
                $motivo, $antes, null, $ganadoId);
        });
    }

    /** Recalcula la cadena de pesajes del animal (peso anterior, días, ganancia y GDP) y su último peso. */
    public function recalcularPesajes(int $ganadoId): void
    {
        $ganado = Ganado::findOrFail($ganadoId);
        $anteriorPeso = (float) ($ganado->pesoActual ?: $ganado->ultimoPeso);
        $anteriorFecha = $ganado->fechaCompra ? Carbon::parse($ganado->fechaCompra) : Carbon::parse($ganado->created_at);

        $pesajes = PesajeGanado::where('ganado_id', $ganadoId)->orderBy('fecha_pesaje')->orderBy('id')->get();
        foreach ($pesajes as $p) {
            $dias = max(1, (int) abs(Carbon::parse($p->fecha_pesaje)->diffInDays($anteriorFecha)));
            $ganancia = round((float) $p->peso - $anteriorPeso, 2);
            $p->forceFill(['peso_anterior' => $anteriorPeso, 'ganancia_peso' => $ganancia, 'dias_transcurridos' => $dias,
                'gdp' => round($ganancia / $dias, 3)])->saveQuietly();
            $anteriorPeso = (float) $p->peso;
            $anteriorFecha = Carbon::parse($p->fecha_pesaje);
        }

        // El último peso del animal activo sale del pesaje más reciente (en vendidos lo fija la venta)
        if ($ganado->puedeOperar()) {
            $ganado->forceFill(['ultimoPeso' => $pesajes->last()?->peso ?? $ganado->pesoActual])->save();
        }
    }

    // ───────────── Servidos de alimento ─────────────

    public function corregirServido(ConsumoAlimento $consumo, array $datos, string $motivo): void
    {
        DB::transaction(function () use ($consumo, $datos, $motivo) {
            $antes = $consumo->only(['fecha', 'racion_id', 'kilos_servidos', 'kilos_rechazo', 'kilos_consumidos', 'costo_total_mxn']);
            $this->inventario->revertirOrigen('ConsumoAlimento', $consumo->id, "Corrección del consumo #{$consumo->id}");

            $racion = RacionAlimento::findOrFail($datos['racion_id']);
            $servidos = (float) $datos['kilos_servidos'];
            $rechazo = (float) ($datos['kilos_rechazo'] ?? 0);
            if ($rechazo > $servidos) {
                throw new Exception('El rechazo no puede ser mayor a lo servido.');
            }
            $consumidos = round($servidos - $rechazo, 2);
            $cabezas = max(1, (int) $consumo->cabezas_contadas);
            $consumo->update([
                'fecha' => $datos['fecha'], 'racion_id' => $racion->id, 'kilos_servidos' => $servidos, 'kilos_rechazo' => $rechazo,
                'kilos_consumidos' => $consumidos, 'costo_total_mxn' => round($consumidos * (float) $racion->costo_por_kg, 2),
                'consumo_promedio_cabeza_kg' => round($consumidos / $cabezas, 2),
            ]);
            $consumo->load('corralLote');
            $this->explosion->descontarServido($racion, $servidos, [
                'fecha' => $datos['fecha'], 'origen_type' => 'ConsumoAlimento', 'origen_id' => $consumo->id, 'corral_lote_id' => $consumo->corral_lote_id,
                'referencia' => "Consumo #{$consumo->id} (corregido) · {$consumo->corralLote?->codigo} · {$racion->codigo}",
            ]);

            $this->anotar('servido', $consumo->id, 'corregido',
                "Servido {$consumo->corralLote?->codigo} del " . Carbon::parse($datos['fecha'])->format('d/m/Y') . ": {$antes['kilos_servidos']} → {$servidos} kg",
                $motivo, $antes, $consumo->only(array_keys($antes)));
        });
    }

    public function anularServido(ConsumoAlimento $consumo, string $motivo): void
    {
        DB::transaction(function () use ($consumo, $motivo) {
            $consumo->load('corralLote');
            $antes = $consumo->toArray();
            $this->inventario->revertirOrigen('ConsumoAlimento', $consumo->id, "Anulación del consumo #{$consumo->id}");
            $consumo->delete();
            $this->anotar('servido', $antes['id'], 'anulado', "Servido {$consumo->corralLote?->codigo} del "
                . Carbon::parse($antes['fecha'])->format('d/m/Y') . " ({$antes['kilos_servidos']} kg) anulado; se devolvió a bodega", $motivo, $antes, null);
        });
    }

    // ───────────── Tratamientos ─────────────

    public function corregirTratamiento(TratamientoSanitario $tr, array $datos, string $motivo): void
    {
        DB::transaction(function () use ($tr, $datos, $motivo) {
            $campos = ['fecha_aplicacion', 'cantidad_insumo', 'dosis_aplicada', 'dias_retiro_rastro', 'fecha_fin_retiro', 'costo_medicamento_mxn'];
            $antes = $tr->only($campos);
            $this->inventario->revertirOrigen('TratamientoSanitario', $tr->id, "Corrección del tratamiento #{$tr->id}");

            $retiro = (int) ($datos['dias_retiro_rastro'] ?? $tr->dias_retiro_rastro);
            $cantidad = isset($datos['cantidad_insumo']) && $datos['cantidad_insumo'] !== '' ? (float) $datos['cantidad_insumo'] : null;
            $insumo = $tr->insumo_id ? Insumo::withTrashed()->find($tr->insumo_id) : null;
            $tr->update([
                'fecha_aplicacion' => $datos['fecha'], 'dosis_aplicada' => $datos['dosis'] ?? $tr->dosis_aplicada, 'dias_retiro_rastro' => $retiro,
                'fecha_fin_retiro' => Carbon::parse($datos['fecha'])->addDays($retiro)->toDateString(), 'cantidad_insumo' => $cantidad,
                'costo_medicamento_mxn' => $insumo && $cantidad ? round($cantidad * (float) $insumo->costo_promedio, 2) : $tr->costo_medicamento_mxn,
            ]);
            if ($insumo && $cantidad > 0) {
                $this->inventario->salida($insumo, $cantidad, ['fecha' => $datos['fecha'], 'origen_type' => 'TratamientoSanitario', 'origen_id' => $tr->id,
                    'referencia' => "Tratamiento #{$tr->id} (corregido)"], permitirNegativo: true);
            }
            $this->anotar('tratamiento', $tr->id, 'corregido', "Tratamiento {$tr->medicamento_producto} del " . Carbon::parse($datos['fecha'])->format('d/m/Y'),
                $motivo, $antes, $tr->fresh()->only($campos), $tr->ganado_id);
        });
    }

    public function anularTratamiento(TratamientoSanitario $tr, string $motivo): void
    {
        DB::transaction(function () use ($tr, $motivo) {
            $antes = $tr->toArray();
            $this->inventario->revertirOrigen('TratamientoSanitario', $tr->id, "Anulación del tratamiento #{$tr->id}");
            $tr->delete();
            $this->anotar('tratamiento', $antes['id'], 'anulado', "Tratamiento {$antes['medicamento_producto']} anulado; el medicamento regresó a bodega",
                $motivo, $antes, null, $antes['ganado_id']);
        });
    }

    // ───────────── Bajas ─────────────

    public function revertirBaja(BajaGanado $baja, string $motivo): void
    {
        DB::transaction(function () use ($baja, $motivo) {
            $ganado = Ganado::findOrFail($baja->ganado_id);
            if (! in_array($ganado->status, [Ganado::STATUS_MUERTO, Ganado::STATUS_BAJA], true)) {
                throw new Exception("El animal #{$ganado->areteID} no está dado de baja.");
            }
            $antes = $baja->toArray();
            $baja->delete();
            $ganado->forceFill(['status' => Ganado::STATUS_ACTIVO])->save();
            $this->anotar('baja', $antes['id'], 'revertido', "Baja de #{$ganado->areteID} ({$antes['tipo_baja']}) revertida; el animal vuelve a estar activo",
                $motivo, $antes, ['status' => Ganado::STATUS_ACTIVO], $ganado->GanadoID);
        });
    }

    // ───────────── Ventas ─────────────

    public function anularVenta(VentaGanado $venta, string $motivo): void
    {
        if ($venta->estado === VentaGanado::ESTADO_ANULADA) {
            throw new Exception('La venta ya está anulada.');
        }

        DB::transaction(function () use ($venta, $motivo) {
            $antes = $venta->only(['folio_venta', 'estado', 'total_venta', 'saldo_pendiente']);
            foreach ($venta->abonos()->where('estado', 'vigente')->get() as $abono) {
                app(CobranzaService::class)->anularAbono($abono, "Venta anulada: {$motivo}");
            }
            $venta->refresh()->forceFill(['estado' => VentaGanado::ESTADO_ANULADA, 'saldo_pendiente' => 0])->save();

            $aretes = [];
            foreach ($venta->detalles as $det) {
                $g = Ganado::find($det->ganado_id);
                if ($g && $g->status === Ganado::STATUS_VENDIDO) {
                    $g->forceFill(['status' => Ganado::STATUS_ACTIVO])->save();
                    $aretes[] = $g->areteID;
                }
            }
            $this->anotar('venta', $venta->id, 'anulado', "Venta {$venta->folio_venta} anulada; regresan al hato: " . (implode(', ', $aretes) ?: 'ninguno'),
                $motivo, $antes, $venta->only(['estado', 'saldo_pendiente']));
        });
    }

    // ───────────── Datos de compra del animal ─────────────

    public function corregirCompra(Ganado $ganado, array $datos, string $motivo): void
    {
        DB::transaction(function () use ($ganado, $datos, $motivo) {
            $campos = ['pesoActual', 'precioCompra', 'fechaCompra', 'categoria', 'raza'];
            $antes = $ganado->only($campos);
            $ganado->forceFill([
                'pesoActual'   => $datos['peso'],
                'precioCompra' => $datos['precio'],
                'fechaCompra'  => $datos['fecha'] ?? $ganado->fechaCompra,
                'categoria'    => $datos['categoria'] ?? $ganado->categoria,
                'raza'         => $datos['raza'] ?? $ganado->raza,
            ])->save();
            if (! $ganado->pesajes()->exists() && $ganado->puedeOperar()) {
                $ganado->forceFill(['ultimoPeso' => $datos['peso']])->save();
            }
            $this->recalcularPesajes($ganado->GanadoID);
            // Flete, comisión y trámites se reparten por peso: se vuelven a repartir en la guía
            if ($ganado->facturaID && ($factura = FacturaGanado::find($ganado->facturaID))) {
                app(CostosCompraService::class)->prorratear($factura);
            }
            $this->anotar('compra', $ganado->GanadoID, 'corregido', "Datos de compra de #{$ganado->areteID}", $motivo, $antes, $ganado->fresh()->only($campos), $ganado->GanadoID);
        });
    }

    private function anotar(string $tipo, int|string $id, string $accion, string $descripcion, string $motivo, ?array $antes, ?array $despues, ?int $ganadoId = null): void
    {
        Correccion::create([
            'user_id' => auth()->id(), 'tipo' => $tipo, 'registro_id' => (string) $id, 'accion' => $accion,
            'descripcion' => mb_substr($descripcion, 0, 255), 'motivo' => $motivo, 'antes' => $antes, 'despues' => $despues, 'ganado_id' => $ganadoId,
        ]);
    }
}
