<?php

namespace App\Services;

use App\Models\Ganado\BajaGanado;
use App\Models\Ganado\Ganado;
use App\Models\Venta\VentaGanado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardService
{
    /**
     * Consolida todas las métricas operativas y datos de gráficos para el Dashboard principal.
     */
    public function obtenerMetricas(): array
    {
        $ganados = Ganado::activo()->count();
        $pesoGanados = round((float) (Ganado::activo()->average('ultimoPeso') ?? 0), 2);
        $ganadoExportar = Ganado::activo()->where('ultimoPeso', '>', 200)->count();

        // Nuevas métricas ganaderas clave
        $listosVenta = Ganado::listoParaVenta()->count();
        $totalVendidos = Ganado::vendido()->count();
        $totalMuertos = Schema::hasTable('bajas_ganado') ? BajaGanado::count() : Ganado::muerto()->count();
        $ingresosVentasMXN = Schema::hasTable('ventas_ganado')
            ? (float) VentaGanado::where('estado', VentaGanado::ESTADO_COMPLETADA)->sum('total_venta')
            : 0.0;

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
            'ganados'               => $ganados,
            'pesoGanados'           => $pesoGanados,
            'ganadoExportar'        => $ganadoExportar,
            'listosVenta'           => $listosVenta,
            'totalVendidos'         => $totalVendidos,
            'totalMuertos'          => $totalMuertos,
            'ingresosVentasMXN'     => $ingresosVentasMXN,
            // Mantener compatibilidad con las variables que lee welcome.blade.php
            'distribucionSexo'      => $porCategoria,
            'distribucionCategoria' => $porSexo,
            'labelsTendencia'       => $labelsTendencia,
            'dataTendencia'         => $dataTendencia,
        ];
    }
}
