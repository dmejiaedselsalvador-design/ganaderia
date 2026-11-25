<?php

namespace App\Services\Ganaderia;

use App\Models\Ganaderia\AbonoVenta;
use App\Models\Venta\VentaGanado;
use Exception;
use Illuminate\Support\Facades\DB;

class CobranzaService
{
    public function registrarAbono(VentaGanado $venta, array $datos): AbonoVenta
    {
        $monto = round((float) $datos['monto'], 2);

        if ($venta->estado !== VentaGanado::ESTADO_COMPLETADA) {
            throw new Exception('La venta está anulada.');
        }
        if ($monto <= 0) {
            throw new Exception('El monto debe ser mayor a cero.');
        }
        if ($monto > (float) $venta->saldo_pendiente + 0.009) {
            throw new Exception('El abono ($ ' . number_format($monto, 2) . ') es mayor al saldo pendiente ($ ' . number_format((float) $venta->saldo_pendiente, 2) . ').');
        }

        return DB::transaction(function () use ($venta, $datos, $monto) {
            $abono = AbonoVenta::create([
                'venta_id'   => $venta->id,
                'fecha'      => $datos['fecha'] ?? now()->toDateString(),
                'monto'      => $monto,
                'forma_pago' => $datos['forma_pago'],
                'referencia' => $datos['referencia'] ?? null,
                'notas'      => $datos['notas'] ?? null,
                'user_id'    => auth()->id(),
            ]);
            $venta->recalcularSaldo();

            return $abono;
        });
    }

    public function anularAbono(AbonoVenta $abono, string $motivo): void
    {
        if ($abono->estado !== 'vigente') {
            throw new Exception('El abono ya estaba anulado.');
        }

        DB::transaction(function () use ($abono, $motivo) {
            $abono->update(['estado' => 'anulado', 'motivo_anulacion' => $motivo]);
            $abono->venta->recalcularSaldo();
        });
    }

    /** Antigüedad de saldos: corriente, 1-30, 31-60, 61-90, +90 días vencidos. */
    public function antiguedadSaldos(): array
    {
        $rangos = ['corriente' => 0, '1_30' => 0, '31_60' => 0, '61_90' => 0, 'mas_90' => 0];

        VentaGanado::where('estado', VentaGanado::ESTADO_COMPLETADA)->where('saldo_pendiente', '>', 0)
            ->get(['saldo_pendiente', 'fecha_vencimiento', 'fecha_venta'])
            ->each(function ($v) use (&$rangos) {
                $venc = $v->fecha_vencimiento ?? $v->fecha_venta;
                $dias = $venc->isFuture() || $venc->isToday() ? 0 : (int) $venc->diffInDays(now());
                $clave = match (true) {
                    $dias === 0 => 'corriente',
                    $dias <= 30 => '1_30',
                    $dias <= 60 => '31_60',
                    $dias <= 90 => '61_90',
                    default     => 'mas_90',
                };
                $rangos[$clave] += (float) $v->saldo_pendiente;
            });

        return array_map(fn ($v) => round($v, 2), $rangos);
    }
}
