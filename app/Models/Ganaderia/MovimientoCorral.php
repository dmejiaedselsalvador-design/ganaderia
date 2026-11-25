<?php

namespace App\Models\Ganaderia;

use App\Models\Concerns\Auditable;
use App\Models\Engorda\CorralLote;
use App\Models\Ganado\Ganado;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Historial de entradas/salidas de un animal entre corrales. */
class MovimientoCorral extends Model
{
    use Auditable;

    protected $table = 'movimientos_corral';

    protected $fillable = ['ganado_id', 'corral_origen_id', 'corral_destino_id', 'fecha', 'motivo', 'user_id'];

    protected $casts = ['fecha' => 'date'];

    public function ganado(): BelongsTo
    {
        return $this->belongsTo(Ganado::class, 'ganado_id', 'GanadoID');
    }

    public function origen(): BelongsTo
    {
        return $this->belongsTo(CorralLote::class, 'corral_origen_id');
    }

    public function destino(): BelongsTo
    {
        return $this->belongsTo(CorralLote::class, 'corral_destino_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Registra el cambio de corral de un animal (y actualiza el animal). */
    public static function mover(Ganado $ganado, ?CorralLote $destino, ?string $motivo = null, $fecha = null): self
    {
        $mov = static::create([
            'ganado_id'         => $ganado->GanadoID,
            'corral_origen_id'  => $ganado->corral_lote_id,
            'corral_destino_id' => $destino?->id,
            'fecha'             => $fecha ?? now()->toDateString(),
            'motivo'            => $motivo,
            'user_id'           => auth()->id(),
        ]);

        $ganado->forceFill([
            'corral_lote_id'       => $destino?->id,
            'lote_potrero'         => $destino?->codigo ?? $ganado->lote_potrero,
            'fecha_ingreso_corral' => $destino ? ($fecha ?? now()->toDateString()) : $ganado->fecha_ingreso_corral,
        ])->save();

        return $mov;
    }
}
