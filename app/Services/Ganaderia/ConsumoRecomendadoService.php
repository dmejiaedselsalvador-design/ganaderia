<?php

namespace App\Services\Ganaderia;

use App\Models\Engorda\ConsumoAlimento;
use App\Models\Engorda\CorralLote;
use App\Models\Engorda\RacionAlimento;
use Illuminate\Support\Collection;

/**
 * Consumo sugerido por % de peso vivo y plan de alimentación por corral.
 *
 *   kg MS/cabeza/día  = peso × %PV
 *   kg TCO/cabeza/día = kg MS / (MS de la dieta / 100)      (si la dieta se maneja en materia seca)
 */
class ConsumoRecomendadoService
{
    public const PCT_PV_DEFECTO = 2.5;

    public function __construct(
        private BiomasaService $biomasa,
        private ExplosionMaterialesService $explosion,
    ) {}

    /** % de peso vivo a usar: el del corral, si no el sugerido de la dieta, si no el valor por defecto. */
    public function pctPara(?CorralLote $corral, ?RacionAlimento $racion): float
    {
        $asig = $corral?->dietaVigente;

        return (float) ($asig?->pct_pv ?? $racion?->pct_pv_sugerido ?? self::PCT_PV_DEFECTO);
    }

    /** kg por día para un animal de cierto peso: ['ms' => kg materia seca, 'tco' => kg tal como se ofrece]. */
    public function kgDia(float $peso, float $pctPv, RacionAlimento $racion): array
    {
        $ms = $racion->materiaSecaCalculada();
        $kg = $peso * $pctPv / 100;

        if ($racion->base_consumo === 'TCO') {
            return ['tco' => round($kg, 2), 'ms' => $ms ? round($kg * $ms / 100, 2) : null];
        }

        // Base materia seca; sin dato de MS no se puede convertir y se usa el mismo valor
        return ['ms' => round($kg, 2), 'tco' => $ms ? round($kg / ($ms / 100), 2) : round($kg, 2)];
    }

    /**
     * Plan completo de un conjunto de corrales.
     * @param array<int, float> $pctManual  % de peso vivo ajustado a mano por corral (corral_id => %)
     */
    public function plan(Collection $corrales, int $dias, array $pctManual = []): array
    {
        $dias = max(1, $dias);
        $corrales = (new \Illuminate\Database\Eloquent\Collection($corrales->all()))->loadMissing(['dietaVigente.racion.ingredientes.insumo']);
        $animales = $this->biomasa->animales($corrales->pluck('id')->all());
        $resumen = $this->biomasa->porCorral(null, $animales);
        $real = $this->consumoReal($corrales->pluck('id')->all());

        $lineas = [];
        $partidas = [];

        foreach ($corrales as $c) {
            $b = $resumen->get($c->id, ['cabezas' => 0, 'biomasa' => 0, 'peso_prom' => 0, 'estimados' => 0, 'vencidos' => 0, 'sin_pesaje' => 0]);
            $asig = $c->dietaVigente;
            $racion = $asig?->racion;
            $pctSugerido = $this->pctPara($c, $racion);
            $pct = isset($pctManual[$c->id]) && $pctManual[$c->id] > 0 ? (float) $pctManual[$c->id] : $pctSugerido;

            $kgCab = $racion && $b['cabezas'] ? $this->kgDia($b['peso_prom'], $pct, $racion) : ['ms' => null, 'tco' => 0];
            // Total del corral = suma de cada animal (peso × %PV) = biomasa × %PV, en kg tal como se ofrece
            $kgCorralDia = $racion && $b['biomasa'] > 0 ? $this->kgDia($b['biomasa'], $pct, $racion)['tco'] : 0;

            if ($racion && $kgCorralDia > 0) {
                $partidas[] = ['racion' => $racion, 'kg' => $kgCorralDia * $dias];
            }

            $lineas[$c->id] = [
                'corral'        => $c,
                'asignacion'    => $asig,
                'racion'        => $racion,
                'cabezas'       => $b['cabezas'],
                'biomasa'       => $b['biomasa'],
                'peso_prom'     => $b['peso_prom'],
                'estimados'     => $b['estimados'],
                'vencidos'      => $b['vencidos'],
                'sin_pesaje'    => $b['sin_pesaje'],
                'pct_sugerido'  => $pctSugerido,
                'pct'           => $pct,
                'pct_manual'    => $pct != $pctSugerido,
                'fuera_rango'   => $racion && $racion->pct_pv_min && $racion->pct_pv_max && ($pct < (float) $racion->pct_pv_min || $pct > (float) $racion->pct_pv_max),
                'ms_dieta'      => $racion?->materiaSecaCalculada(),
                'kg_cab_ms'     => $kgCab['ms'],
                'kg_cab_tco'    => $kgCab['tco'],
                'kg_dia'        => $kgCorralDia,
                'kg_periodo'    => round($kgCorralDia * $dias, 2),
                'costo_dia'     => $racion ? round($kgCorralDia * (float) $racion->costo_por_kg, 2) : 0,
                'real_cab_dia'  => $real[$c->id] ?? null,
                'receta_ok'     => $racion?->recetaCompleta() ?? false,
            ];
        }

        $requerimiento = $this->explosion->consolidar($partidas);

        return [
            'dias'      => $dias,
            'lineas'    => $lineas,
            'animales'  => $animales,
            'bodega'    => $this->explosion->cruzarConBodega($requerimiento, $dias),
        ];
    }

    /** kg servidos por cabeza por día (promedio de los últimos 7 días con registro), por corral. */
    public function consumoReal(array $corralIds): array
    {
        return ConsumoAlimento::whereIn('corral_lote_id', $corralIds)
            ->where('fecha', '>=', now()->subDays(7)->toDateString())
            ->get(['corral_lote_id', 'fecha', 'kilos_servidos', 'cabezas_contadas'])
            ->groupBy('corral_lote_id')
            ->map(function ($regs) {
                $dias = max(1, $regs->pluck('fecha')->map->toDateString()->unique()->count());
                $cab = max(1, $regs->avg('cabezas_contadas'));

                return round($regs->sum('kilos_servidos') / $dias / $cab, 2);
            })->all();
    }
}
