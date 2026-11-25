<?php

namespace App\Services;

use App\Models\Ganado\Ganado;
use App\Models\Ganado\PesajeGanado;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class PesajeService
{
    /**
     * Registra un pesaje histórico para un animal, calculando automáticamente
     * la ganancia de peso (kg), días transcurridos y Ganancia Diaria de Peso (GDP en kg/día).
     */
    public function registrarPesaje(
        int $ganadoId,
        float $peso,
        string $fecha,
        ?string $bascula = null,
        ?string $lote = null,
        ?string $observaciones = null,
        ?int $userId = null,
        ?float $condicionCorporal = null,
        bool $enAyuno = false
    ): PesajeGanado {
        $ganado = Ganado::findOrFail($ganadoId);

        if (!$ganado->puedeOperar()) {
            throw new Exception("No es posible registrar pesaje: El animal #{$ganado->areteID} está marcado como '{$ganado->status}'.");
        }

        if ($peso <= 0) {
            throw new Exception("El peso registrado debe ser mayor a cero.");
        }

        return DB::transaction(function () use ($ganado, $peso, $fecha, $bascula, $lote, $observaciones, $userId, $condicionCorporal, $enAyuno) {
            $fechaPesaje = Carbon::parse($fecha);

            // Obtener el pesaje anterior más reciente
            $pesajeAnterior = PesajeGanado::where('ganado_id', $ganado->GanadoID)
                ->where('fecha_pesaje', '<=', $fechaPesaje)
                ->orderBy('fecha_pesaje', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $pesoAnterior = null;
            $gananciaPeso = null;
            $diasTranscurridos = null;
            $gdp = null;

            if ($pesajeAnterior) {
                $pesoAnterior = (float) $pesajeAnterior->peso;
                $gananciaPeso = round($peso - $pesoAnterior, 2);
                $diasTranscurridos = max(1, (int) abs($fechaPesaje->diffInDays($pesajeAnterior->fecha_pesaje)));
                $gdp = round($gananciaPeso / $diasTranscurridos, 3);
            } else {
                // Si es el primer pesaje registrado en la bitácora, comparamos con el peso de ingreso
                $pesoIngreso = (float) ($ganado->pesoActual ?? $ganado->ultimoPeso ?? $peso);
                $pesoAnterior = $pesoIngreso;
                $gananciaPeso = round($peso - $pesoIngreso, 2);
                $fechaInicio = $ganado->fechaCompra ? Carbon::parse($ganado->fechaCompra) : Carbon::parse($ganado->created_at);
                $diasTranscurridos = max(1, (int) abs($fechaPesaje->diffInDays($fechaInicio)));
                $gdp = round($gananciaPeso / $diasTranscurridos, 3);
            }

            // Crear el registro de pesaje
            $pesaje = PesajeGanado::create([
                'ganado_id'          => $ganado->GanadoID,
                'peso'               => $peso,
                'fecha_pesaje'       => $fechaPesaje->toDateString(),
                'peso_anterior'      => $pesoAnterior,
                'ganancia_peso'      => $gananciaPeso,
                'dias_transcurridos' => $diasTranscurridos,
                'gdp'                => $gdp,
                'bascula'            => $bascula,
                'lote_corral'        => $lote,
                'observaciones'      => $observaciones,
                'user_id'            => $userId,
                'condicion_corporal' => $condicionCorporal,
                'en_ayuno'           => $enAyuno,
            ]);

            // Actualizar último peso en el animal (solo si es el pesaje más reciente)
            $hayPosterior = PesajeGanado::where('ganado_id', $ganado->GanadoID)->where('id', '!=', $pesaje->id)
                ->whereDate('fecha_pesaje', '>', $fechaPesaje->toDateString())->exists();
            if (! $hayPosterior) {
                $ganado->ultimoPeso = $peso;
            }
            if ($condicionCorporal !== null) {
                $ganado->condicion_corporal = $condicionCorporal;
            }
            if (!empty($lote)) {
                $ganado->lote_potrero = $lote;
            }
            $ganado->save();

            return $pesaje;
        });
    }
}
