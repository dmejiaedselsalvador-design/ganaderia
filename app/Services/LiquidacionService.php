<?php

namespace App\Services;

use App\Models\Ganado\Adelanto;
use App\Models\Ganado\AdelantoFactura;
use App\Models\Ganado\FacturaGanado;
use App\Models\Ganado\ProveedorGanado;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LiquidacionService
{
    /**
     * Calcula los totales financieros y datos consolidados para la liquidación de una factura.
     *
     * @return array{
     *     factura: FacturaGanado,
     *     proveedor: ProveedorGanado|null,
     *     adelantosProveedor: Collection<int, Adelanto>,
     *     totalAdelantos: float,
     *     totalGanado: float,
     *     saldoFinal: float,
     *     montoAbsoluto: float,
     *     esDeudaProveedor: bool,
     *     animalesPorCategoria: array<string, \Illuminate\Support\Collection>
     * }
     */
    public function calcularResumenFactura(FacturaGanado|int $factura): array
    {
        if (is_numeric($factura)) {
            $factura = FacturaGanado::with(['proveedor.adelantos', 'animales'])->findOrFail($factura);
        } else {
            $factura->loadMissing(['proveedor.adelantos', 'animales']);
        }

        $proveedor = $factura->proveedor;

        // Guía ya liquidada: los anticipos que se le aplicaron (con su monto aplicado).
        // Pendiente: los anticipos del proveedor que aún no se amortizan.
        if ($factura->estado === FacturaGanado::ESTADO_LIQUIDADO) {
            $factura->loadMissing('adelantos');
            $adelantosProveedor = $factura->adelantos->each(fn ($a) => $a->setAttribute('monto_aplicado', (float) ($a->pivot->montoAplicado ?? $a->dinero)))->values();
        } else {
            $adelantosProveedor = $proveedor
                ? $proveedor->adelantos->whereIn('status', [Adelanto::STATUS_ENTREGADO, Adelanto::STATUS_PARCIAL])
                    ->each(fn ($a) => $a->setAttribute('monto_aplicado', $a->saldoDisponible()))
                    ->filter(fn ($a) => $a->monto_aplicado > 0)->values()
                : collect();
        }

        $totalAdelantos = (float) $adelantosProveedor->sum('monto_aplicado');

        // Total del ganado registrado en esta factura
        $totalGanado = (float) $factura->animales->sum(function ($animal) {
            return (float) $animal->precioGanadoTotal; // peso de compra × precio
        });

        // Saldo neto: positivo = saldo a favor del rancho (proveedor debe dinero)
        // negativo = saldo a pagar al proveedor (rancho le debe al proveedor)
        $saldoFinal = $totalAdelantos - $totalGanado;
        $montoAbsoluto = abs($saldoFinal);
        $esDeudaProveedor = $saldoFinal > 0;

        // Agrupación de animales por categoría
        $categorias = ['Becerro', 'Torete', 'Toro', 'Becerra', 'Vaquilla', 'Vaca'];
        $animalesPorCategoria = [];
        foreach ($categorias as $cat) {
            $animalesPorCategoria[$cat] = $factura->animales->where('categoria', $cat)->values();
        }

        return [
            'factura'              => $factura,
            'proveedor'            => $proveedor,
            'adelantosProveedor'   => $adelantosProveedor,
            'totalAdelantos'       => $totalAdelantos,
            'totalGanado'          => $totalGanado,
            'saldoFinal'           => $saldoFinal,
            'montoAbsoluto'        => $montoAbsoluto,
            'esDeudaProveedor'     => $esDeudaProveedor,
            'animalesPorCategoria' => $animalesPorCategoria,
        ];
    }

    /**
     * Procesa la liquidación formal de una factura y descuenta los adelantos seleccionados.
     */
    public function liquidarFactura(FacturaGanado|int $factura, array $adelantoIds = []): FacturaGanado
    {
        $facturaModel = is_numeric($factura) ? FacturaGanado::findOrFail($factura) : $factura;

        if ($facturaModel->estado === FacturaGanado::ESTADO_LIQUIDADO) {
            throw new \Exception("La guía {$facturaModel->numeroFactura} ya está liquidada.");
        }
        if (! $facturaModel->animales()->exists()) {
            throw new \Exception("La guía {$facturaModel->numeroFactura} no tiene ganado registrado.");
        }

        return DB::transaction(function () use ($facturaModel, $adelantoIds) {
            $facturaModel->estado = FacturaGanado::ESTADO_LIQUIDADO;
            $facturaModel->save();

            if (! empty($adelantoIds)) {
                // Se descuentan en orden (el más antiguo primero) solo hasta cubrir el valor del ganado.
                // Lo que sobre de un anticipo queda «parcial» y se descuenta en la siguiente compra.
                $porCubrir = round((float) $facturaModel->load('animales')->monto_total_calculado, 2);
                $adelantos = Adelanto::whereIn('id', $adelantoIds)->where('proveedor_id', $facturaModel->proveedorID)
                    ->whereIn('status', [Adelanto::STATUS_ENTREGADO, Adelanto::STATUS_PARCIAL])
                    ->orderBy('date')->orderBy('id')->get();

                foreach ($adelantos as $adelanto) {
                    if ($porCubrir <= 0.004) {
                        break;
                    }
                    $saldo = $adelanto->saldoDisponible();
                    $aplicar = round(min($saldo, $porCubrir), 2);
                    if ($aplicar <= 0) {
                        continue;
                    }

                    // Trazabilidad: cuánto de este anticipo se descontó en esta guía
                    AdelantoFactura::updateOrCreate(
                        ['adelanto_id' => $adelanto->id, 'factura_id' => $facturaModel->id],
                        ['montoAplicado' => $aplicar]
                    );
                    $adelanto->update(['status' => $saldo - $aplicar <= 0.004 ? Adelanto::STATUS_LIQUIDADO : Adelanto::STATUS_PARCIAL]);
                    $porCubrir = round($porCubrir - $aplicar, 2);
                }
            }

            return $facturaModel;
        });
    }

    /**
     * Obtiene el listado de proveedores activos con sus balances de adelantos y ganado consolidado.
     */
    public function obtenerSaldosProveedores()
    {
        $proveedores = ProveedorGanado::query()
            ->where('estado', ProveedorGanado::ESTADO_ACTIVO)
            ->with(['adelantos' => fn ($q) => $q->whereIn('status', [Adelanto::STATUS_ENTREGADO, Adelanto::STATUS_PARCIAL])])
            ->withSum('ganadoDirecto as total_facturas', 'precioGanadoTotal')
            ->get();

        $proveedores->each(function ($proveedor) {
            $proveedor->total_adelantos = (float) $proveedor->adelantos->sum(fn ($a) => $a->saldoDisponible());
            $proveedor->total_facturas = (float) ($proveedor->total_facturas ?? 0);
            $proveedor->saldo_liquidacion = $proveedor->total_adelantos - $proveedor->total_facturas;
            $proveedor->precioGanadoTotal = $proveedor->total_facturas;
        });

        return $proveedores->sortByDesc('saldo_liquidacion')->values();
    }
}
