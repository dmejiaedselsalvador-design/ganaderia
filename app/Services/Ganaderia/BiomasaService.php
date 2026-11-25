<?php

namespace App\Services\Ganaderia;

use App\Models\Ganado\Ganado;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Cabezas activas, biomasa y peso promedio por corral a partir del último pesaje de cada animal.
 *
 * - Una sola consulta (ganado activo + subconsulta agregada de pesajes).
 * - Si el último pesaje tiene más de DIAS_VIGENCIA días, el peso se proyecta con la GDP del animal
 *   (o la meta del corral) y se marca como "estimado".
 * - Si el animal no tiene pesajes, se usa su peso de ingreso desde la fecha de compra.
 */
class BiomasaService
{
    public const DIAS_VIGENCIA = 21;
    public const GDP_MAXIMA = 3.0; // tope de seguridad para proyecciones (kg/día)

    /** @return Collection<int, array> animales activos con su peso efectivo, indexado por GanadoID */
    public function animales(?array $corralIds = null): Collection
    {
        $pesajes = DB::table('pesajes_ganado')
            ->selectRaw('ganado_id, MAX(fecha_pesaje) as ultima_fecha, AVG(CASE WHEN gdp > 0 THEN gdp END) as gdp_prom')
            ->groupBy('ganado_id');

        $hoy = Carbon::today();

        return Ganado::query()
            ->where('ganado.status', Ganado::STATUS_ACTIVO)
            ->whereNotNull('ganado.corral_lote_id')
            ->when($corralIds, fn ($q) => $q->whereIn('ganado.corral_lote_id', $corralIds))
            ->leftJoinSub($pesajes, 'up', 'up.ganado_id', '=', 'ganado.GanadoID')
            ->leftJoin('corrales_lotes as c', 'c.id', '=', 'ganado.corral_lote_id')
            ->orderBy('ganado.areteID')
            ->get([
                'ganado.GanadoID', 'ganado.areteID', 'ganado.categoria', 'ganado.corral_lote_id', 'ganado.ultimoPeso',
                'ganado.pesoActual', 'ganado.fechaCompra', 'up.ultima_fecha', 'up.gdp_prom', 'c.meta_gdp',
            ])
            ->mapWithKeys(function ($g) use ($hoy) {
                $sinPesaje = $g->ultima_fecha === null;
                $fechaRef = Carbon::parse($g->ultima_fecha ?? $g->fechaCompra ?? $hoy)->startOfDay();
                $dias = max(0, (int) $fechaRef->diffInDays($hoy));
                $base = (float) ($sinPesaje ? ($g->pesoActual ?: $g->ultimoPeso) : $g->ultimoPeso);
                $gdp = min(self::GDP_MAXIMA, (float) ($g->gdp_prom ?: $g->meta_gdp ?: 0));
                $estimado = $dias > self::DIAS_VIGENCIA && $gdp > 0;
                $peso = $estimado ? $base + $gdp * $dias : $base;

                return [$g->GanadoID => [
                    'id'             => $g->GanadoID,
                    'arete'          => $g->areteID,
                    'categoria'      => $g->categoria,
                    'corral_id'      => (int) $g->corral_lote_id,
                    'peso_registrado'=> round($base, 1),
                    'peso'           => round($peso, 1),
                    'fecha_pesaje'   => $sinPesaje ? null : Carbon::parse($g->ultima_fecha)->toDateString(),
                    'dias_pesaje'    => $dias,
                    'gdp'            => round($gdp, 3),
                    'estimado'       => $estimado,
                    'vencido'        => $dias > self::DIAS_VIGENCIA,
                    'sin_pesaje'     => $sinPesaje,
                ]];
            });
    }

    /** Resumen por corral: cabezas, biomasa, peso promedio y calidad del dato. */
    public function porCorral(?array $corralIds = null, ?Collection $animales = null): Collection
    {
        $animales ??= $this->animales($corralIds);

        return $animales->groupBy('corral_id')->map(fn (Collection $a, $corralId) => [
            'corral_id'  => (int) $corralId,
            'cabezas'    => $a->count(),
            'biomasa'    => round($a->sum('peso'), 1),
            'peso_prom'  => $a->count() ? round($a->avg('peso'), 1) : 0,
            'estimados'  => $a->where('estimado', true)->count(),
            'vencidos'   => $a->where('vencido', true)->count(),
            'sin_pesaje' => $a->where('sin_pesaje', true)->count(),
        ]);
    }
}
