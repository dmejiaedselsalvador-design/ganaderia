<?php

namespace App\Models\Ganaderia;

use App\Models\Concerns\Auditable;
use App\Models\Engorda\CorralLote;
use App\Models\Sanidad\TratamientoSanitario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Vacunación, desparasitación, refuerzo, etc. programado para un corral. */
class ProgramacionSanitaria extends Model
{
    use Auditable;

    protected $table = 'programaciones_sanitarias';

    protected $fillable = [
        'titulo', 'tipo_evento', 'corral_lote_id', 'insumo_id', 'fecha_programada', 'dosis', 'via_administracion',
        'estado', 'aplicada_at', 'cabezas_aplicadas', 'notas', 'user_id',
    ];

    protected $casts = ['fecha_programada' => 'date', 'aplicada_at' => 'datetime'];

    public function corral(): BelongsTo
    {
        return $this->belongsTo(CorralLote::class, 'corral_lote_id');
    }

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class)->withTrashed();
    }

    public function tratamientos(): HasMany
    {
        return $this->hasMany(TratamientoSanitario::class, 'programacion_id');
    }

    public function scopePendientes(Builder $q): Builder
    {
        return $q->where('estado', 'pendiente');
    }

    public function vencida(): bool
    {
        return $this->estado === 'pendiente' && $this->fecha_programada->isPast() && ! $this->fecha_programada->isToday();
    }

    public function descripcionAuditoria(): string
    {
        return $this->titulo;
    }
}
