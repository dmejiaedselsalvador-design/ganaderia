<?php

namespace App\Services;

use App\Models\Calidad\EvaluacionCanal;
use App\Models\Engorda\ConsumoAlimento;
use App\Models\Engorda\CorralLote;
use App\Models\Ganado\BajaGanado;
use App\Models\Ganado\Ganado;
use App\Models\Ganado\PesajeGanado;
use App\Models\Sanidad\TratamientoSanitario;
use App\Models\Venta\VentaGanado;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardService
{
    /** Meses permitidos en el filtro de período. */
    public const PERIODOS = [3, 6, 12];

    /**
     * Consolida métricas operativas, zootécnicas y financieras para el Dashboard de Ganadería.
     */
    public function obtenerMetricas(int $meses = 6): array
    {
        $meses = in_array($meses, self::PERIODOS, true) ? $meses : 6;
        $desde = now()->startOfMonth()->subMonths($meses - 1);
        $hastaAnterior = $desde->copy()->subDay();
        $desdeAnterior = $desde->copy()->subMonths($meses);

        $tablas = [
            'ventas'      => Schema::hasTable('ventas_ganado'),
            'consumos'    => Schema::hasTable('consumos_alimento'),
            'corrales'    => Schema::hasTable('corrales_lotes'),
            'tratamientos'=> Schema::hasTable('tratamientos_sanitarios'),
            'canal'       => Schema::hasTable('evaluaciones_canal'),
            'bajas'       => Schema::hasTable('bajas_ganado'),
            'pesajes'     => Schema::hasTable('pesajes_ganado'),
        ];

        /* ---------------- Inventario ---------------- */
        $ganados        = Ganado::activo()->count();
        $pesoGanados    = round((float) (Ganado::activo()->average('ultimoPeso') ?? 0), 1);
        $kgEnPie        = (float) Ganado::activo()->sum('ultimoPeso');
        $ganadoExportar = Ganado::activo()->where('ultimoPeso', '>', 200)->count();
        $listosVenta    = Ganado::listoParaVenta()->count();
        $totalVendidos  = Ganado::vendido()->count();
        $totalMuertos   = $tablas['bajas'] ? BajaGanado::count() : Ganado::muerto()->count();
        $historicoTotal = max(1, $ganados + $totalVendidos + $totalMuertos);
        $mortalidadPct  = round($totalMuertos / $historicoTotal * 100, 2);

        /* ---------------- Finanzas ---------------- */
        $costoCompraHato   = (float) Ganado::activo()->sum(DB::raw('COALESCE(pesoActual, 0) * COALESCE(precioCompra, 0)'));
        $valorHato         = (float) Ganado::activo()->sum('precioGanadoTotal');
        $plusvaliaHato     = $valorHato - $costoCompraHato;
        $plusvaliaPct      = $costoCompraHato > 0 ? round($plusvaliaHato / $costoCompraHato * 100, 1) : 0;

        $ventasCompletadas = fn () => VentaGanado::where('estado', VentaGanado::ESTADO_COMPLETADA);
        $ingresosPeriodo   = $tablas['ventas'] ? (float) $ventasCompletadas()->where('fecha_venta', '>=', $desde)->sum('total_venta') : 0.0;
        $ingresosAnterior  = $tablas['ventas'] ? (float) $ventasCompletadas()->whereBetween('fecha_venta', [$desdeAnterior, $hastaAnterior])->sum('total_venta') : 0.0;
        $ingresosHistorico = $tablas['ventas'] ? (float) $ventasCompletadas()->sum('total_venta') : 0.0;

        $alimentoPeriodo   = $tablas['consumos'] ? (float) ConsumoAlimento::where('fecha', '>=', $desde)->sum('costo_total_mxn') : 0.0;
        $alimentoHistorico = $tablas['consumos'] ? (float) ConsumoAlimento::sum('costo_total_mxn') : 0.0;
        $sanidadPeriodo    = $tablas['tratamientos'] ? (float) TratamientoSanitario::where('fecha_aplicacion', '>=', $desde)->sum('costo_medicamento_mxn') : 0.0;

        $kgProducidos      = $tablas['pesajes'] ? (float) PesajeGanado::where('fecha_pesaje', '>=', $desde)->where('ganancia_peso', '>', 0)->sum('ganancia_peso') : 0.0;
        $costoPorKg        = $kgProducidos > 0 ? round(($alimentoPeriodo + $sanidadPeriodo) / $kgProducidos, 2) : null;

        /* ---------------- Zootecnia ---------------- */
        $gdpPromedio = $tablas['pesajes']
            ? round((float) (PesajeGanado::where('fecha_pesaje', '>=', $desde)->where('gdp', '>', 0)->avg('gdp') ?? 0), 3)
            : 0.0;

        $corrales = $tablas['corrales']
            ? CorralLote::where('estado', 'Activo')->withCount('ganadosActivos')->orderBy('codigo')->get()
            : collect();
        $capacidadTotal = (int) $corrales->sum('capacidad_maxima');
        $ocupados       = (int) $corrales->sum('ganados_activos_count');
        $ocupacionPct   = $capacidadTotal > 0 ? round($ocupados / $capacidadTotal * 100, 1) : 0;

        $animalesEnRetiroCount = $tablas['tratamientos']
            ? TratamientoSanitario::where('fecha_fin_retiro', '>=', now()->toDateString())->distinct('ganado_id')->count('ganado_id')
            : 0;

        $canalesEvaluadasCount    = $tablas['canal'] ? EvaluacionCanal::count() : 0;
        $rendimientoPromedioCanal = $tablas['canal'] && $canalesEvaluadasCount > 0
            ? round((float) EvaluacionCanal::avg('rendimiento_canal_caliente_pct'), 1)
            : null;

        /* ---------------- Series mensuales ---------------- */
        $mesesLista = collect(CarbonPeriod::create($desde, '1 month', now()->startOfMonth()))
            ->map(fn (Carbon $m) => $m->format('Y-m'));

        // $vacio: valor para meses sin datos (0 en sumas, null en promedios para no dibujar caídas falsas)
        $serie = function (?Collection $datos, $vacio = 0) use ($mesesLista) {
            $datos = ($datos ?? collect())->pluck('valor', 'mes');

            return $mesesLista->map(fn ($m) => isset($datos[$m]) ? round((float) $datos[$m], 2) : $vacio)->values();
        };

        $pesoMensual = $tablas['pesajes'] ? $serie($this->porMes(PesajeGanado::query(), 'fecha_pesaje', 'AVG(peso)', $desde), null) : $serie(null, null);
        $gdpMensual  = $tablas['pesajes'] ? $serie($this->porMes(PesajeGanado::where('gdp', '>', 0), 'fecha_pesaje', 'AVG(gdp)', $desde), null) : $serie(null, null);
        $ventasMensual   = $tablas['ventas'] ? $serie($this->porMes($ventasCompletadas(), 'fecha_venta', 'SUM(total_venta)', $desde)) : $serie(null);
        $alimentoMensual = $tablas['consumos'] ? $serie($this->porMes(ConsumoAlimento::query(), 'fecha', 'SUM(costo_total_mxn)', $desde)) : $serie(null);
        // Compras: costo de adquisición (peso de ingreso x precio por kg) según fecha de compra
        $comprasMensual  = $serie($this->porMes(Ganado::query(), 'fechaCompra', 'SUM(COALESCE(pesoActual, 0) * COALESCE(precioCompra, 0))', $desde));

        $nombresMes = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $labelsMeses = $mesesLista->map(function ($m) use ($nombresMes) {
            [$a, $mm] = explode('-', $m);
            return $nombresMes[(int) $mm] . ' ' . substr($a, 2);
        })->values();

        /* ---------------- Distribuciones ---------------- */
        $porCategoria = Ganado::activo()->select('categoria', DB::raw('count(*) as total'))
            ->groupBy('categoria')->orderByDesc('total')->get();
        $porSexo = Ganado::activo()->select('sexo', DB::raw('count(*) as total'))
            ->groupBy('sexo')->get();
        $bajasPorCausa = $tablas['bajas']
            ? BajaGanado::select('causa_muerte', DB::raw('count(*) as total'))->groupBy('causa_muerte')->orderByDesc('total')->limit(6)->get()
            : collect();

        /* ---------------- Rankings ---------------- */
        $proximosVenta = Ganado::activo()->with('corralLote:id,codigo')
            ->get(['GanadoID', 'areteID', 'categoria', 'raza', 'ultimoPeso', 'pesoActual', 'peso_objetivo', 'corral_lote_id', 'fechaCompra'])
            ->map(function (Ganado $g) {
                $objetivo = $g->peso_objetivo_calculado;
                $g->avance_pct = $objetivo > 0 ? min(100, round((float) $g->ultimoPeso / $objetivo * 100, 1)) : 0;
                $g->objetivo = $objetivo;
                return $g;
            })
            ->sortByDesc('avance_pct')->take(6)->values();

        $topGdp = $tablas['pesajes']
            ? PesajeGanado::with('ganado:GanadoID,areteID,categoria,raza')
                ->where('fecha_pesaje', '>=', $desde)->where('gdp', '>', 0)
                ->orderByDesc('gdp')->limit(5)->get()
            : collect();

        // Valuación a precio de mercado y utilidad (módulo de rentabilidad)
        $rentabilidad = app(\App\Services\Ganaderia\RentabilidadService::class)->resumen();
        $hayPrecios = Schema::hasTable('precios_mercado') && \App\Models\Ganaderia\PrecioMercado::exists();

        return [
            'rentabilidad'             => $rentabilidad,
            'hayPrecios'               => $hayPrecios,
            'alertasGestion'           => $this->alertasGestion(),
            'meses'                    => $meses,
            'periodos'                 => self::PERIODOS,
            // Inventario
            'ganados'                  => $ganados,
            'pesoGanados'              => $pesoGanados,
            'kgEnPie'                  => $kgEnPie,
            'ganadoExportar'           => $ganadoExportar,
            'listosVenta'              => $listosVenta,
            'totalVendidos'            => $totalVendidos,
            'totalMuertos'             => $totalMuertos,
            'mortalidadPct'            => $mortalidadPct,
            // Finanzas
            'costoCompraHato'          => $costoCompraHato,
            'valorHato'                => $valorHato,
            'plusvaliaHato'            => $plusvaliaHato,
            'plusvaliaPct'             => $plusvaliaPct,
            'ingresosPeriodo'          => $ingresosPeriodo,
            'ingresosAnterior'         => $ingresosAnterior,
            'ingresosVariacionPct'     => $ingresosAnterior > 0 ? round(($ingresosPeriodo - $ingresosAnterior) / $ingresosAnterior * 100, 1) : null,
            'ingresosHistorico'        => $ingresosHistorico,
            'alimentoPeriodo'          => $alimentoPeriodo,
            'alimentoHistorico'        => $alimentoHistorico,
            'sanidadPeriodo'           => $sanidadPeriodo,
            'kgProducidos'             => $kgProducidos,
            'costoPorKg'               => $costoPorKg,
            // Compatibilidad con nombres anteriores
            'ingresosVentasMXN'        => $ingresosHistorico,
            'inversionGanadoPieMXN'    => $valorHato,
            'inversionAlimentoMXN'     => $alimentoHistorico,
            // Zootecnia
            'gdpPromedio'              => $gdpPromedio,
            'corrales'                 => $corrales,
            'totalCorralesActivos'     => $corrales->count(),
            'capacidadTotal'           => $capacidadTotal,
            'ocupados'                 => $ocupados,
            'ocupacionPct'             => $ocupacionPct,
            'animalesEnRetiroCount'    => $animalesEnRetiroCount,
            'canalesEvaluadasCount'    => $canalesEvaluadasCount,
            'rendimientoPromedioCanal' => $rendimientoPromedioCanal,
            // Series
            'labelsMeses'              => $labelsMeses,
            'pesoMensual'              => $pesoMensual,
            'gdpMensual'               => $gdpMensual,
            'ventasMensual'            => $ventasMensual,
            'alimentoMensual'          => $alimentoMensual,
            'comprasMensual'           => $comprasMensual,
            // Distribuciones y rankings
            'porCategoria'             => $porCategoria,
            'porSexo'                  => $porSexo,
            'bajasPorCausa'            => $bajasPorCausa,
            'proximosVenta'            => $proximosVenta,
            'topGdp'                   => $topGdp,
        ];
    }

    /** Pendientes operativos para el dashboard (inventario, sanidad, cobranza). */
    private function alertasGestion(): array
    {
        $a = [];
        $bajo = \App\Models\Ganaderia\Insumo::bajoMinimo()->count();
        $negativo = \App\Models\Ganaderia\Insumo::where('activo', true)->where('existencia', '<', 0)->count();
        $caducar = \App\Models\Ganaderia\Insumo::lotesPorCaducar(30)->count();
        $atrasadas = \App\Models\Ganaderia\ProgramacionSanitaria::pendientes()->whereDate('fecha_programada', '<', today())->count();
        $semana = \App\Models\Ganaderia\ProgramacionSanitaria::pendientes()->whereBetween('fecha_programada', [today(), today()->addDays(7)])->count();
        $casos = \App\Models\Ganaderia\EventoEnfermedad::abiertos()->count();
        $vencidas = VentaGanado::where('estado', VentaGanado::ESTADO_COMPLETADA)->where('saldo_pendiente', '>', 0)->whereDate('fecha_vencimiento', '<', today());

        if ($negativo) $a[] = ['rose', 'fa-boxes-stacked', "{$negativo} insumos con existencia negativa (falta registrar entradas)", 'inventario.index', 'ganaderia.inventario'];
        if ($bajo) $a[] = ['amber', 'fa-arrow-trend-down', "{$bajo} insumos bajo el stock mínimo", 'inventario.index', 'ganaderia.inventario'];
        if ($caducar) $a[] = ['amber', 'fa-hourglass-end', "{$caducar} lotes de insumos por caducar en 30 días", 'inventario.index', 'ganaderia.inventario'];
        if ($atrasadas) $a[] = ['rose', 'fa-calendar-xmark', "{$atrasadas} aplicaciones sanitarias atrasadas", 'sanidad.calendario', 'ganaderia.sanidad'];
        if ($semana) $a[] = ['blue', 'fa-calendar-check', "{$semana} aplicaciones sanitarias esta semana", 'sanidad.calendario', 'ganaderia.sanidad'];
        if ($casos) $a[] = ['amber', 'fa-virus', "{$casos} casos de enfermedad abiertos", 'sanidad.enfermedades', 'ganaderia.sanidad'];
        if ($n = (clone $vencidas)->count()) $a[] = ['rose', 'fa-hand-holding-dollar', "{$n} ventas con pago vencido por " . formatoPesos((clone $vencidas)->sum('saldo_pendiente')), 'cobranza.index', 'ganaderia.clientes'];

        return $a;
    }

    /** Agrupa una consulta por mes (YYYY-MM) devolviendo [mes, valor]. */
    private function porMes($query, string $columnaFecha, string $agregado, Carbon $desde): Collection
    {
        $driver = DB::connection()->getDriverName();
        $mes = match ($driver) {
            'sqlite' => "strftime('%Y-%m', {$columnaFecha})",
            'pgsql'  => "to_char({$columnaFecha}, 'YYYY-MM')",
            default  => "DATE_FORMAT({$columnaFecha}, '%Y-%m')",
        };

        return $query->where($columnaFecha, '>=', $desde)
            ->selectRaw("{$mes} as mes, {$agregado} as valor")
            ->groupBy('mes')
            ->get();
    }
}
