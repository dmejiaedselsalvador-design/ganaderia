<?php

namespace App\Services\Ganaderia;

use App\Models\Engorda\ConsumoAlimento;
use App\Models\Engorda\CorralLote;
use App\Models\Ganaderia\GastoOperativo;
use App\Models\Ganaderia\MovimientoCorral;
use App\Models\Ganaderia\PrecioMercado;
use App\Models\Ganado\Ganado;
use App\Models\Sanidad\TratamientoSanitario;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * Costo acumulado, ingreso y utilidad de cada animal y de cada lote.
 *
 * Cómo se reparte cada costo:
 *  - Compra: peso de ingreso x precio + su parte de flete/comisión/trámites de la guía.
 *  - Alimento: cada servido de alimento a un corral se divide entre las cabezas contadas ese día;
 *    al animal le toca la parte de los días que estuvo en ese corral (según su historial de corrales).
 *  - Medicinas: los tratamientos aplicados al animal.
 *  - Gastos asignados a un corral: se dividen entre los animales que estaban en ese corral ese día.
 *  - Gastos generales (sin corral): se reparten por "cabeza-día" de cada mes entre todo el hato.
 *  - Ingreso: precio de venta (descuento prorrateado). Si sigue activo, valor de mercado proyectado
 *    (peso actual x último precio de mercado de su categoría). Si se dio de baja, lo recuperado.
 */
class RentabilidadService
{
    /** @var array<int, array<int, array{corral: ?int, desde: Carbon, hasta: Carbon}>> */
    private array $intervalos = [];

    /**
     * @param  Collection<int, Ganado>|null  $animales  (null = todos)
     * @return Collection<int, array>  indexado por GanadoID
     */
    public function porAnimal(?Collection $animales = null): Collection
    {
        $todos = Ganado::with(['detalleVenta.venta', 'baja'])->get();
        $animales ??= $todos;
        $hoy = now()->startOfDay();

        // 1. Intervalos de permanencia en corrales (de todo el hato, para repartir costos correctamente)
        $movs = MovimientoCorral::orderBy('fecha')->orderBy('id')->get()->groupBy('ganado_id');
        foreach ($todos as $g) {
            $this->intervalos[$g->GanadoID] = $this->intervalosDe($g, $movs->get($g->GanadoID, collect()), $hoy);
        }

        // 2. Alimento por corral y fecha (costo por cabeza)
        $consumos = ConsumoAlimento::get(['corral_lote_id', 'fecha', 'costo_total_mxn', 'cabezas_contadas'])
            ->groupBy('corral_lote_id');

        // 3. Gastos vigentes
        $gastos = GastoOperativo::vigentes()->get(['corral_lote_id', 'fecha', 'monto']);
        $gastosCorral = $gastos->whereNotNull('corral_lote_id')->groupBy('corral_lote_id');
        $tasaGeneralMes = $this->tasaGastosGenerales($gastos->whereNull('corral_lote_id'), $todos, $hoy);

        // 4. Medicinas por animal
        $medicinas = TratamientoSanitario::selectRaw('ganado_id, SUM(costo_medicamento_mxn) as total')
            ->groupBy('ganado_id')->pluck('total', 'ganado_id');

        $precios = PrecioMercado::vigentes();

        return $animales->mapWithKeys(function (Ganado $g) use ($consumos, $gastosCorral, $tasaGeneralMes, $medicinas, $precios, $hoy) {
            $compra = $g->costoAdquisicion();
            $alimento = 0.0;
            $gastoCorral = 0.0;

            foreach ($this->intervalos[$g->GanadoID] as $int) {
                if (! $int['corral']) {
                    continue;
                }
                foreach ($consumos->get($int['corral'], collect()) as $c) {
                    if ($c->fecha->betweenIncluded($int['desde'], $int['hasta'])) {
                        $alimento += (float) $c->costo_total_mxn / max(1, (int) $c->cabezas_contadas);
                    }
                }
                foreach ($gastosCorral->get($int['corral'], collect()) as $gc) {
                    if ($gc->fecha->betweenIncluded($int['desde'], $int['hasta'])) {
                        $gastoCorral += (float) $gc->monto / max(1, $this->cabezasEnCorral($int['corral'], $gc->fecha));
                    }
                }
            }

            [$entrada, $salida] = $this->periodoEnHato($g, $hoy);
            $gastoGeneral = 0.0;
            foreach (CarbonPeriod::create($entrada->copy()->startOfMonth(), '1 month', $salida) as $mes) {
                $clave = $mes->format('Y-m');
                if (! isset($tasaGeneralMes[$clave])) {
                    continue;
                }
                $ini = $entrada->max($mes->copy()->startOfMonth());
                $fin = $salida->min($mes->copy()->endOfMonth()->startOfDay());
                $dias = $ini->lte($fin) ? $ini->diffInDays($fin) + 1 : 0;
                $gastoGeneral += $dias * $tasaGeneralMes[$clave];
            }

            $medicina = (float) ($medicinas[$g->GanadoID] ?? 0);
            $costoTotal = $compra + $alimento + $medicina + $gastoCorral + $gastoGeneral;

            // Ingreso
            $tipoIngreso = 'proyectado';
            $ingreso = null;
            if ($g->status === Ganado::STATUS_VENDIDO && $g->detalleVenta) {
                $venta = $g->detalleVenta->venta;
                $sub = (float) $g->detalleVenta->subtotal;
                $desc = $venta && (float) $venta->subtotal > 0 ? (float) $venta->descuento * $sub / (float) $venta->subtotal : 0;
                $ingreso = $sub - $desc;
                $tipoIngreso = 'venta';
            } elseif (in_array($g->status, [Ganado::STATUS_MUERTO, Ganado::STATUS_BAJA], true)) {
                $ingreso = (float) ($g->baja->valor_recuperado_mxn ?? 0);
                $tipoIngreso = 'baja';
            } else {
                $precio = PrecioMercado::precioPara($g->categoria, $precios);
                $ingreso = $precio !== null ? (float) $g->ultimoPeso * $precio : null;
            }

            $kgGanados = max(0, (float) $g->ultimoPeso - (float) $g->pesoActual);
            $costoProduccion = $alimento + $medicina + $gastoCorral + $gastoGeneral;
            $utilidad = $ingreso !== null ? $ingreso - $costoTotal : null;

            return [$g->GanadoID => [
                'ganado'           => $g,
                'dias'             => (int) $entrada->diffInDays($salida),
                'compra'           => round($compra, 2),
                'alimento'         => round($alimento, 2),
                'medicina'         => round($medicina, 2),
                'gasto_corral'     => round($gastoCorral, 2),
                'gasto_general'    => round($gastoGeneral, 2),
                'costo_total'      => round($costoTotal, 2),
                'kg_ganados'       => round($kgGanados, 2),
                'costo_kg'         => $kgGanados > 0 ? round($costoProduccion / $kgGanados, 2) : null,
                'ingreso'          => $ingreso !== null ? round($ingreso, 2) : null,
                'tipo_ingreso'     => $tipoIngreso,
                'utilidad'         => $utilidad !== null ? round($utilidad, 2) : null,
                'margen_pct'       => $utilidad !== null && $costoTotal > 0 ? round($utilidad / $costoTotal * 100, 1) : null,
                'corral_id'        => $g->corral_lote_id,
            ]];
        });
    }

    /** Rentabilidad de un animal. */
    public function deAnimal(Ganado $ganado): array
    {
        return $this->porAnimal(collect([$ganado]))->first();
    }

    /**
     * Resumen por lote (corral actual o último corral del animal) con metas vs real.
     */
    public function porLote(): Collection
    {
        $animales = $this->porAnimal();
        $corrales = CorralLote::with('ubicacion')->orderBy('codigo')->get()->keyBy('id');
        $kgAlimento = ConsumoAlimento::selectRaw('corral_lote_id, SUM(kilos_consumidos) as kg')->groupBy('corral_lote_id')->pluck('kg', 'corral_lote_id');

        return $animales->groupBy('corral_id')->map(function (Collection $grupo, $corralId) use ($corrales, $kgAlimento) {
            $corral = $corrales->get($corralId);
            $cabezas = $grupo->count();
            $muertes = $grupo->filter(fn ($a) => $a['ganado']->status === Ganado::STATUS_MUERTO)->count();
            $kgGanados = $grupo->sum('kg_ganados');
            $costoProd = $grupo->sum(fn ($a) => $a['alimento'] + $a['medicina'] + $a['gasto_corral'] + $a['gasto_general']);
            $conIngreso = $grupo->whereNotNull('ingreso');
            $gdp = $grupo->filter(fn ($a) => $a['dias'] > 0 && $a['kg_ganados'] > 0)
                ->avg(fn ($a) => $a['kg_ganados'] / $a['dias']);

            return [
                'corral'         => $corral,
                'nombre'         => $corral ? "{$corral->codigo} · {$corral->nombre}" : 'Sin corral asignado',
                'cabezas'        => $cabezas,
                'activos'        => $grupo->filter(fn ($a) => $a['ganado']->status === Ganado::STATUS_ACTIVO)->count(),
                'compra'         => round($grupo->sum('compra'), 2),
                'alimento'       => round($grupo->sum('alimento'), 2),
                'medicina'       => round($grupo->sum('medicina'), 2),
                'gastos'         => round($grupo->sum('gasto_corral') + $grupo->sum('gasto_general'), 2),
                'costo_total'    => round($grupo->sum('costo_total'), 2),
                'ingreso'        => round($conIngreso->sum('ingreso'), 2),
                'utilidad'       => round($conIngreso->sum('utilidad'), 2),
                'sin_precio'     => $cabezas - $conIngreso->count(),
                'kg_ganados'     => round($kgGanados, 1),
                'gdp'            => $gdp ? round($gdp, 3) : null,
                'costo_kg'       => $kgGanados > 0 ? round($costoProd / $kgGanados, 2) : null,
                'mortalidad_pct' => $cabezas ? round($muertes / $cabezas * 100, 2) : 0,
                'conversion'     => $kgGanados > 0 && isset($kgAlimento[$corralId]) ? round((float) $kgAlimento[$corralId] / $kgGanados, 2) : null,
                'gasto_real'     => round($grupo->sum('alimento') + $grupo->sum('medicina') + $grupo->sum('gasto_corral'), 2),
            ];
        })->sortBy('nombre')->values();
    }

    /** Totales del hato para el dashboard. */
    public function resumen(): array
    {
        $a = $this->porAnimal();
        $activos = $a->filter(fn ($x) => $x['ganado']->status === Ganado::STATUS_ACTIVO);
        $vendidos = $a->where('tipo_ingreso', 'venta');

        return [
            'valor_mercado_activos' => round($activos->sum('ingreso'), 2),
            'costo_activos'         => round($activos->sum('costo_total'), 2),
            'activos_sin_precio'    => $activos->whereNull('ingreso')->count(),
            'utilidad_proyectada'   => round($activos->whereNotNull('ingreso')->sum('utilidad'), 2),
            'utilidad_realizada'    => round($vendidos->sum('utilidad'), 2),
            'vendidos'              => $vendidos->count(),
            'margen_realizado_pct'  => $vendidos->sum('costo_total') > 0 ? round($vendidos->sum('utilidad') / $vendidos->sum('costo_total') * 100, 1) : null,
        ];
    }

    /* ------------------------------------------------------------------ */

    /** Fecha de entrada y salida (venta, baja u hoy) del animal en el hato. */
    private function periodoEnHato(Ganado $g, Carbon $hoy): array
    {
        $entrada = Carbon::parse($g->fecha_ingreso_corral ?? $g->fechaCompra ?? $g->created_at)->startOfDay();
        $salida = $hoy->copy();

        if ($g->status === Ganado::STATUS_VENDIDO && $g->detalleVenta?->venta?->fecha_venta) {
            $salida = $g->detalleVenta->venta->fecha_venta->copy()->startOfDay();
        } elseif (in_array($g->status, [Ganado::STATUS_MUERTO, Ganado::STATUS_BAJA], true) && $g->baja?->fecha_muerte) {
            $salida = $g->baja->fecha_muerte->copy()->startOfDay();
        }

        return [$entrada->min($salida), $salida];
    }

    private function intervalosDe(Ganado $g, Collection $movs, Carbon $hoy): array
    {
        [$entrada, $salida] = $this->periodoEnHato($g, $hoy);

        if ($movs->isEmpty()) {
            return [['corral' => $g->corral_lote_id, 'desde' => $entrada, 'hasta' => $salida]];
        }

        $intervalos = [];
        $primero = $movs->first();
        if ($primero->corral_origen_id && $primero->fecha->gt($entrada)) {
            $intervalos[] = ['corral' => $primero->corral_origen_id, 'desde' => $entrada, 'hasta' => $primero->fecha->copy()->subDay()];
        }
        $lista = $movs->values();
        foreach ($lista as $i => $m) {
            $siguiente = $lista->get($i + 1);
            $hasta = $siguiente ? $siguiente->fecha->copy()->subDay() : $salida;
            if ($m->corral_destino_id && $m->fecha->lte($hasta)) {
                $intervalos[] = ['corral' => $m->corral_destino_id, 'desde' => $m->fecha->copy(), 'hasta' => $hasta];
            }
        }

        return $intervalos;
    }

    private array $cacheCabezas = [];

    private function cabezasEnCorral(int $corralId, Carbon $fecha): int
    {
        $clave = $corralId . '|' . $fecha->toDateString();

        return $this->cacheCabezas[$clave] ??= collect($this->intervalos)->filter(
            fn ($ints) => collect($ints)->contains(fn ($i) => $i['corral'] === $corralId && $fecha->betweenIncluded($i['desde'], $i['hasta']))
        )->count();
    }

    /** Gasto general por cabeza-día de cada mes: ['2026-11' => 3.25, ...]. */
    private function tasaGastosGenerales(Collection $gastosGenerales, Collection $todos, Carbon $hoy): array
    {
        if ($gastosGenerales->isEmpty()) {
            return [];
        }

        $porMes = $gastosGenerales->groupBy(fn ($g) => $g->fecha->format('Y-m'))->map->sum('monto');
        $cabezaDias = [];

        foreach ($todos as $g) {
            [$entrada, $salida] = $this->periodoEnHato($g, $hoy);
            foreach (CarbonPeriod::create($entrada->copy()->startOfMonth(), '1 month', $salida) as $mes) {
                $ini = $entrada->max($mes->copy()->startOfMonth());
                $fin = $salida->min($mes->copy()->endOfMonth()->startOfDay());
                if ($ini->lte($fin)) {
                    $cabezaDias[$mes->format('Y-m')] = ($cabezaDias[$mes->format('Y-m')] ?? 0) + $ini->diffInDays($fin) + 1;
                }
            }
        }

        return $porMes->mapWithKeys(fn ($monto, $mes) => [$mes => isset($cabezaDias[$mes]) && $cabezaDias[$mes] > 0 ? (float) $monto / $cabezaDias[$mes] : 0])->all();
    }
}
