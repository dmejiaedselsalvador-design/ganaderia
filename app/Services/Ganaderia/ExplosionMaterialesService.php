<?php

namespace App\Services\Ganaderia;

use App\Models\Engorda\RacionAlimento;
use App\Models\Ganaderia\Insumo;
use App\Models\Ganaderia\MovimientoInsumo;
use Illuminate\Support\Collection;

/**
 * Única fuente de cálculo de la receta (BOM) de una dieta:
 * kg de dieta tal como se ofrece ⇒ kg de cada ingrediente, costo, y cruce con bodega.
 * Lo usan el servido de alimento, el plan de alimentación y la planeación.
 */
class ExplosionMaterialesService
{
    /** Margen mínimo de sobra para considerar el stock "holgado" (10 %). */
    public const MARGEN_JUSTO = 0.10;

    public function __construct(private InventarioService $inventario) {}

    /**
     * @return Collection<int, array{insumo: Insumo, pct: float, kg: float, unidades: float, costo: float}> indexado por insumo_id
     */
    public function explotar(RacionAlimento $racion, float $kgDieta): Collection
    {
        $racion->loadMissing('ingredientes.insumo');

        return $racion->ingredientes->filter(fn ($i) => $i->insumo)->mapWithKeys(function ($ing) use ($kgDieta) {
            $kg = $kgDieta * (float) $ing->porcentaje / 100;

            return [$ing->insumo_id => [
                'insumo'   => $ing->insumo,
                'pct'      => (float) $ing->porcentaje,
                'kg'       => round($kg, 3),
                'unidades' => round($kg / $ing->insumo->kgPorUnidad(), 4),
                'costo'    => round($kg * $ing->insumo->costoKg(), 2),
            ]];
        });
    }

    /**
     * Suma varias explosiones (p. ej. todos los corrales) en una sola lista por insumo.
     * @param iterable<array{racion: RacionAlimento, kg: float}> $partidas
     */
    public function consolidar(iterable $partidas): Collection
    {
        $total = collect();
        foreach ($partidas as $p) {
            foreach ($this->explotar($p['racion'], $p['kg']) as $id => $l) {
                $prev = $total->get($id);
                $total->put($id, $prev ? array_merge($prev, [
                    'kg'       => $prev['kg'] + $l['kg'],
                    'unidades' => $prev['unidades'] + $l['unidades'],
                    'costo'    => $prev['costo'] + $l['costo'],
                ]) : $l);
            }
        }

        return $total;
    }

    /**
     * Cruza un requerimiento (kg por insumo para todo el periodo) con la bodega.
     * Estado: ok (sobra más del 10 %), justo (sobra menos del 10 %), faltante.
     */
    public function cruzarConBodega(Collection $requerimiento, int $dias = 1): Collection
    {
        // Lo que no se controla en bodega (agua de pozo, etc.) no genera faltantes
        return $requerimiento->filter(fn ($l) => $l['insumo']->controla_existencia ?? true)->map(function ($l) use ($dias) {
            $ins = $l['insumo'];
            $disponible = max(0, $ins->existenciaKg()); // existencia negativa = 0 disponible
            $faltante = max(0, $l['kg'] - $disponible);
            $kgDia = $dias > 0 ? $l['kg'] / $dias : 0;

            return $l + [
                'disponible_kg' => round($disponible, 2),
                'negativo'      => (float) $ins->existencia < 0,
                'faltante_kg'   => round($faltante, 2),
                'cobertura'     => $kgDia > 0 ? round($disponible / $kgDia, 1) : null,
                'costo_compra'  => round($faltante * $ins->costoKg(), 2),
                'estado'        => $faltante > 0 ? 'faltante' : ($disponible < $l['kg'] * (1 + self::MARGEN_JUSTO) ? 'justo' : 'ok'),
            ];
        })->sortBy(fn ($l) => [$l['estado'] === 'faltante' ? 0 : ($l['estado'] === 'justo' ? 1 : 2), $l['cobertura'] ?? 9999])->values();
    }

    /**
     * Descuenta de bodega los ingredientes de un servido de alimento.
     * Devuelve los insumos que quedaron con existencia negativa.
     */
    public function descontarServido(RacionAlimento $racion, float $kgServidos, array $datos): array
    {
        $negativos = [];
        foreach ($this->explotar($racion, $kgServidos) as $l) {
            if ($l['unidades'] <= 0 || ! ($l['insumo']->controla_existencia ?? true)) {
                continue;
            }
            $mov = $this->inventario->salida($l['insumo'], $l['unidades'], $datos, permitirNegativo: true);
            if ((float) $mov->existencia_resultante < 0) {
                $negativos[] = $l['insumo']->nombre;
            }
        }

        return $negativos;
    }
}
