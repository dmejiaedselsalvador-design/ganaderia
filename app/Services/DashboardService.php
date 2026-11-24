<?php

namespace App\Services;

use App\Models\Calidad\EvaluacionCanal;
use App\Models\Engorda\ConsumoAlimento;
use App\Models\Engorda\CorralLote;
use App\Models\Ganado\BajaGanado;
use App\Models\Ganado\Ganado;
use App\Models\Sanidad\TratamientoSanitario;
use App\Models\Venta\VentaGanado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardService
{
    /**
     * Consolida todas las métricas operativas, zootécnicas y financieras para el Dashboard principal.
     */
    public function obtenerMetricas(): array
    {
        $ganados = Ganado::activo()->count();
        $pesoGanados = round((float) (Ganado::activo()->average('ultimoPeso') ?? 0), 2);
        $ganadoExportar = Ganado::activo()->where('ultimoPeso', '>', 200)->count();

        // Métricas ganaderas clave
        $listosVenta = Ganado::listoParaVenta()->count();
        $totalVendidos = Ganado::vendido()->count();
        $totalMuertos = Schema::hasTable('bajas_ganado') ? BajaGanado::count() : Ganado::muerto()->count();

        // Finanzas en Pesos Mexicanos (MXN)
        $ingresosVentasMXN = Schema::hasTable('ventas_ganado')
            ? (float) VentaGanado::where('estado', VentaGanado::ESTADO_COMPLETADA)->sum('total_venta')
            : 0.0;

        $inversionGanadoPieMXN = (float) Ganado::activo()->sum('precioGanadoTotal');
        $inversionAlimentoMXN  = Schema::hasTable('consumos_alimento')
            ? (float) ConsumoAlimento::sum('costo_total_mxn')
            : 0.0;

        // Nuevos módulos zootécnicos
        $totalCorralesActivos = Schema::hasTable('corrales_lotes')
            ? CorralLote::where('estado', 'Activo')->count()
            : 0;

        $animalesEnRetiroCount = Schema::hasTable('tratamientos_sanitarios')
            ? TratamientoSanitario::where('fecha_fin_retiro', '>=', now()->toDateString())->count()
            : 0;

        $canalesEvaluadasCount = Schema::hasTable('evaluaciones_canal')
            ? EvaluacionCanal::count()
            : 0;

        $rendimientoPromedioCanal = Schema::hasTable('evaluaciones_canal')
            ? round((float) (EvaluacionCanal::avg('rendimiento_canal_caliente_pct') ?? 60.0), 1)
            : 60.0;

        // Agrupación por categoría (Becerro, Vaca, etc.)
        $porCategoria = Ganado::activo()
            ->select('categoria', DB::raw('count(*) as total'))
            ->groupBy('categoria')
            ->get();

        // Agrupación por sexo (Macho, Hembra)
        $porSexo = Ganado::activo()
            ->select('sexo', DB::raw('count(*) as total'))
            ->groupBy('sexo')
            ->get();

        // Tendencia de peso promedio en los últimos meses
        $driver = DB::connection()->getDriverName();
        $dateExpression = $driver === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $tendenciaRaw = Ganado::activo()
            ->select(
                DB::raw("{$dateExpression} as mes"),
                DB::raw('AVG(ultimoPeso) as peso_promedio')
            )
            ->groupBy('mes')
            ->orderBy('mes', 'asc')
            ->take(6)
            ->get();

        $labelsTendencia = $tendenciaRaw->pluck('mes');
        $dataTendencia = $tendenciaRaw->pluck('peso_promedio')->map(fn($v) => round((float) $v, 2));

        return [
            'ganados'                  => $ganados,
            'pesoGanados'              => $pesoGanados,
            'ganadoExportar'           => $ganadoExportar,
            'listosVenta'              => $listosVenta,
            'totalVendidos'            => $totalVendidos,
            'totalMuertos'             => $totalMuertos,
            'ingresosVentasMXN'        => $ingresosVentasMXN,
            'inversionGanadoPieMXN'    => $inversionGanadoPieMXN,
            'inversionAlimentoMXN'     => $inversionAlimentoMXN,
            'totalCorralesActivos'     => $totalCorralesActivos,
            'animalesEnRetiroCount'    => $animalesEnRetiroCount,
            'canalesEvaluadasCount'    => $canalesEvaluadasCount,
            'rendimientoPromedioCanal' => $rendimientoPromedioCanal,
            // Distribución y gráficos
            'distribucionSexo'         => $porCategoria,
            'distribucionCategoria'    => $porSexo,
            'labelsTendencia'          => $labelsTendencia,
            'dataTendencia'            => $dataTendencia,
        ];
    }
}
