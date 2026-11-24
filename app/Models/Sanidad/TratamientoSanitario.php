<?php

namespace App\Models\Sanidad;

use App\Models\Ganado\Ganado;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TratamientoSanitario extends Model
{
    use HasFactory;

    protected $table = 'tratamientos_sanitarios';

    protected $fillable = [
        'ganado_id',
        'tipo_evento',
        'medicamento_producto',
        'dosis_aplicada',
        'via_administracion',
        'fecha_aplicacion',
        'dias_retiro_rastro',
        'fecha_fin_retiro',
        'costo_medicamento_mxn',
        'veterinario_responsable',
        'diagnostico_motivo',
        'user_id',
    ];

    protected $casts = [
        'fecha_aplicacion'      => 'date',
        'fecha_fin_retiro'      => 'date',
        'dias_retiro_rastro'    => 'integer',
        'costo_medicamento_mxn' => 'decimal:2',
    ];

    public function ganado(): BelongsTo
    {
        return $this->belongsTo(Ganado::class, 'ganado_id', 'GanadoID');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Determina si el animal está actualmente bajo periodo de retiro (NO apto para sacrificio).
     */
    public function getEstaEnRetiroAttribute(): bool
    {
        if (!$this->fecha_fin_retiro) {
            return false;
        }

        return $this->fecha_fin_retiro->isFuture() || $this->fecha_fin_retiro->isToday();
    }

    /**
     * Días que le faltan para cumplir el periodo de retiro.
     */
    public function getDiasRestantesRetiroAttribute(): int
    {
        if (!$this->esta_en_retiro) {
            return 0;
        }

        return max(0, (int) now()->startOfDay()->diffInDays($this->fecha_fin_retiro->endOfDay(), false));
    }
}
