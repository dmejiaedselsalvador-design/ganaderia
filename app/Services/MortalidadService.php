<?php

namespace App\Services;

use App\Models\Ganado\BajaGanado;
use App\Models\Ganado\Ganado;
use Exception;
use Illuminate\Support\Facades\DB;

class MortalidadService
{
    /**
     * Registra la baja por defunción de un animal, cambiando de forma irreversible
     * su estatus a 'Muerto' y excluyéndolo del inventario activo y corrales.
     */
    public function registrarBaja(
        int $ganadoId,
        string $fechaMuerte,
        string $causaMuerte,
        ?string $necropsia = null,
        ?string $observaciones = null,
        ?int $userId = null,
        string $tipoBaja = 'Muerte',
        float $valorRecuperado = 0
    ): BajaGanado {
        $ganado = Ganado::findOrFail($ganadoId);

        if ($ganado->isMuerto()) {
            throw new Exception("El animal #{$ganado->areteID} ya se encuentra registrado como fallecido.");
        }

        if ($ganado->isVendido()) {
            throw new Exception("El animal #{$ganado->areteID} no puede darse de baja por muerte porque ya fue vendido.");
        }

        if (! $ganado->puedeOperar()) {
            throw new Exception("El animal #{$ganado->areteID} ya no está activo (estatus {$ganado->status}).");
        }

        // Pérdida económica = costo acumulado del animal (compra + alimento + medicinas + gastos) - lo recuperado
        $costoAcumulado = app(\App\Services\Ganaderia\RentabilidadService::class)->deAnimal($ganado)['costo_total'] ?? 0;

        return DB::transaction(function () use ($ganado, $fechaMuerte, $causaMuerte, $necropsia, $observaciones, $userId, $tipoBaja, $valorRecuperado, $costoAcumulado) {
            $baja = BajaGanado::create([
                'ganado_id'     => $ganado->GanadoID,
                'tipo_baja'     => $tipoBaja,
                'valor_perdido_mxn'    => max(0, round($costoAcumulado - $valorRecuperado, 2)),
                'valor_recuperado_mxn' => $valorRecuperado,
                'fecha_muerte'  => $fechaMuerte,
                'causa_muerte'  => $causaMuerte,
                'necropsia'     => $necropsia,
                'ultimo_peso'   => $ganado->ultimoPeso,
                'observaciones' => $observaciones,
                'user_id'       => $userId,
            ]);

            $ganado->status = BajaGanado::TIPOS[$tipoBaja] ?? Ganado::STATUS_MUERTO;
            $ganado->save();

            return $baja;
        });
    }

    /**
     * Resumen y estadísticas de mortalidad del hato ganadero.
     */
    public function obtenerEstadisticas(): array
    {
        $totalHistorico = Ganado::count();
        $totalMuertes = BajaGanado::where('tipo_baja', 'Muerte')->count();
        $tasaMortalidad = $totalHistorico > 0 ? round(($totalMuertes / $totalHistorico) * 100, 2) : 0.0;

        $muertesPorCausa = BajaGanado::select('causa_muerte', DB::raw('count(*) as total'))
            ->groupBy('causa_muerte')
            ->orderBy('total', 'desc')
            ->get();

        return [
            'totalHistorico'  => $totalHistorico,
            'totalMuertes'    => $totalMuertes,
            'tasaMortalidad'  => $tasaMortalidad,
            'muertesPorCausa' => $muertesPorCausa,
        ];
    }
}
