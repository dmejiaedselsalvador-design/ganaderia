<?php

namespace App\Services\Ganaderia;

use App\Models\Ganado\FacturaGanado;

/**
 * Reparte los costos adicionales de una guía de compra (flete, comisión, trámites, otros)
 * entre sus animales en proporción a su peso de ingreso.
 */
class CostosCompraService
{
    public function prorratear(FacturaGanado $factura): void
    {
        $animales = $factura->animales()->get();
        $total = $factura->costosAdicionales();
        $pesoTotal = (float) $animales->sum('pesoActual');

        foreach ($animales as $animal) {
            $parte = $pesoTotal > 0
                ? $total * ((float) $animal->pesoActual / $pesoTotal)
                : ($animales->count() ? $total / $animales->count() : 0);

            $animal->forceFill(['costo_adicional_compra' => round($parte, 2)])->saveQuietly();
        }
    }

    /** Merma de viaje de toda la guía: peso en origen vs peso de llegada. */
    public function mermaGuia(FacturaGanado $factura): ?array
    {
        if (! $factura->peso_origen_kg || (float) $factura->peso_origen_kg <= 0) {
            return null;
        }
        $llegada = (float) $factura->animales()->sum('pesoActual');
        $kg = round((float) $factura->peso_origen_kg - $llegada, 2);

        return ['llegada' => $llegada, 'kg' => $kg, 'pct' => round($kg / (float) $factura->peso_origen_kg * 100, 2)];
    }
}
