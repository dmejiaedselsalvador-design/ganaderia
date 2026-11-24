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

        // Adelantos pendientes de amortizar/liquidar
        $adelantosProveedor = $proveedor
            ? $proveedor->adelantos->where('status', Adelanto::STATUS_ENTREGADO)->values()
            : collect();

        $totalAdelantos = (float) $adelantosProveedor->sum('dinero');

        // Total del ganado registrado en esta factura
        $totalGanado = (float) $factura->animales->sum(function ($animal) {
            return (float) ($animal->precioGanadoTotal ?? ($animal->ultimoPeso * $animal->precioCompra));
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

        return DB::transaction(function () use ($facturaModel, $adelantoIds) {
            $facturaModel->estado = FacturaGanado::ESTADO_LIQUIDADO;
            $facturaModel->save();

            if (!empty($adelantoIds)) {
                $adelantos = Adelanto::whereIn('id', $adelantoIds)->get();

                foreach ($adelantos as $adelanto) {
                    $adelanto->update([
                        'status' => Adelanto::STATUS_LIQUIDADO,
                    ]);

                    // Registrar la trazabilidad en la tabla pivote
                    AdelantoFactura::updateOrCreate(
                        [
                            'adelanto_id' => $adelanto->id,
                            'factura_id'  => $facturaModel->id,
                        ],
                        [
                            'montoAplicado' => $adelanto->dinero,
                        ]
                    );
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
            ->withSum(['adelantos as total_adelantos' => function ($q) {
                $q->where('status', Adelanto::STATUS_ENTREGADO);
            }], 'dinero')
            ->withSum('ganadoDirecto as total_facturas', 'precioGanadoTotal')
            ->get();

        $proveedores->each(function ($proveedor) {
            $proveedor->total_adelantos = (float) ($proveedor->total_adelantos ?? 0);
            $proveedor->total_facturas = (float) ($proveedor->total_facturas ?? 0);
            $proveedor->saldo_liquidacion = $proveedor->total_adelantos - $proveedor->total_facturas;
            $proveedor->precioGanadoTotal = $proveedor->total_facturas;
        });

        return $proveedores->sortByDesc('saldo_liquidacion')->values();
    }
}
