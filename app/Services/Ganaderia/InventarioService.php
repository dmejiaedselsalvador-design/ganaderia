<?php

namespace App\Services\Ganaderia;

use App\Models\Ganaderia\Insumo;
use App\Models\Ganaderia\MovimientoInsumo;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Kardex de insumos con costo promedio ponderado.
 */
class InventarioService
{
    /** Compra / entrada a bodega. Recalcula el costo promedio. */
    public function entrada(Insumo $insumo, float $cantidad, float $costoUnitario, array $datos = []): MovimientoInsumo
    {
        if ($cantidad <= 0) {
            throw new Exception('La cantidad de entrada debe ser mayor a cero.');
        }

        return DB::transaction(function () use ($insumo, $cantidad, $costoUnitario, $datos) {
            $insumo = Insumo::lockForUpdate()->findOrFail($insumo->id);
            $existencia = (float) $insumo->existencia;
            $base = max(0, $existencia);
            $nuevoCosto = ($base + $cantidad) > 0
                ? (($base * (float) $insumo->costo_promedio) + ($cantidad * $costoUnitario)) / ($base + $cantidad)
                : $costoUnitario;

            $insumo->forceFill([
                'existencia'     => round($existencia + $cantidad, 3),
                'costo_promedio' => round($nuevoCosto, 4),
            ])->save();

            return $this->registrar($insumo, MovimientoInsumo::ENTRADA, $cantidad, $costoUnitario, $datos);
        });
    }

    /**
     * Salida / consumo al costo promedio vigente.
     * $permitirNegativo: los consumos automáticos (alimento servido, tratamientos) no se bloquean
     * aunque falte registrar una entrada; la existencia queda negativa y se alerta.
     */
    public function salida(Insumo $insumo, float $cantidad, array $datos = [], bool $permitirNegativo = false): MovimientoInsumo
    {
        if ($cantidad <= 0) {
            throw new Exception('La cantidad de salida debe ser mayor a cero.');
        }

        return DB::transaction(function () use ($insumo, $cantidad, $datos, $permitirNegativo) {
            $insumo = Insumo::lockForUpdate()->findOrFail($insumo->id);

            if (! $permitirNegativo && (float) $insumo->existencia < $cantidad) {
                throw new Exception("Existencia insuficiente de {$insumo->nombre}: hay " . number_format((float) $insumo->existencia, 2) . " {$insumo->unidad}.");
            }

            $insumo->forceFill(['existencia' => round((float) $insumo->existencia - $cantidad, 3)])->save();

            return $this->registrar($insumo, MovimientoInsumo::SALIDA, $cantidad, (float) $insumo->costo_promedio, $datos);
        });
    }

    /**
     * Devuelve a bodega lo que salió por un registro (servido, tratamiento…) que se corrige o anula.
     * Se calcula el neto (salidas − devoluciones previas) de ese origen, así se puede corregir varias veces.
     */
    public function revertirOrigen(string $origenType, int $origenId, string $referencia): int
    {
        $movs = MovimientoInsumo::where('origen_type', $origenType)->where('origen_id', $origenId)->get();
        $n = 0;
        foreach ($movs->groupBy('insumo_id') as $insumoId => $lista) {
            $salidas = $lista->where('tipo', MovimientoInsumo::SALIDA);
            $neto = (float) $salidas->sum('cantidad') - (float) $lista->where('tipo', MovimientoInsumo::ENTRADA)->sum('cantidad');
            if ($neto <= 0.0005 || ! ($insumo = Insumo::withTrashed()->find($insumoId))) {
                continue;
            }
            $costo = $salidas->sum('cantidad') > 0
                ? $salidas->sum(fn ($m) => (float) $m->cantidad * (float) $m->costo_unitario) / (float) $salidas->sum('cantidad')
                : (float) $insumo->costo_promedio;
            $this->entrada($insumo, round($neto, 3), $costo, [
                'fecha' => now()->toDateString(), 'referencia' => $referencia, 'origen_type' => $origenType, 'origen_id' => $origenId,
                'notas' => 'Devolución por corrección', 'corral_lote_id' => $lista->first()->corral_lote_id,
            ]);
            $n++;
        }

        return $n;
    }

    /** Ajuste por conteo físico: deja la existencia en el valor contado. */
    public function ajustar(Insumo $insumo, float $existenciaContada, array $datos = []): ?MovimientoInsumo
    {
        return DB::transaction(function () use ($insumo, $existenciaContada, $datos) {
            $insumo = Insumo::lockForUpdate()->findOrFail($insumo->id);
            $diferencia = round($existenciaContada - (float) $insumo->existencia, 3);

            if ($diferencia == 0.0) {
                return null;
            }

            $insumo->forceFill(['existencia' => round($existenciaContada, 3)])->save();

            return $this->registrar($insumo, MovimientoInsumo::AJUSTE, $diferencia, (float) $insumo->costo_promedio, $datos);
        });
    }

    private function registrar(Insumo $insumo, string $tipo, float $cantidad, float $costo, array $datos): MovimientoInsumo
    {
        return MovimientoInsumo::create(array_merge([
            'insumo_id'             => $insumo->id,
            'tipo'                  => $tipo,
            'fecha'                 => now()->toDateString(),
            'cantidad'              => $cantidad,
            'costo_unitario'        => round($costo, 4),
            'existencia_resultante' => $insumo->existencia,
            'user_id'               => auth()->id(),
        ], array_filter($datos, fn ($v) => $v !== null && $v !== '')));
    }
}
