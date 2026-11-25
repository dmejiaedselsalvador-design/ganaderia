<?php

namespace App\Models\Ganaderia;

use App\Models\Concerns\Auditable;
use App\Models\Ganado\Ganado;
use App\Models\Sanidad\TratamientoSanitario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Caso clínico de un animal (para medir morbilidad, recaídas y eficacia de tratamientos). */
class EventoEnfermedad extends Model
{
    use Auditable;

    protected $table = 'eventos_enfermedad';

    public const DIAGNOSTICOS = [
        'Complejo Respiratorio Bovino (Neumonía)', 'Diarrea / Enteritis', 'Acidosis ruminal', 'Timpanismo',
        'Cojera / Pododermatitis', 'Queratoconjuntivitis (Ojo rosado)', 'Fiebre de embarque', 'Anaplasmosis / Piroplasmosis',
        'Clostridiasis', 'Traumatismo / Lesión', 'Otro',
    ];

    public const RESULTADOS = ['en_tratamiento' => 'En tratamiento', 'recuperado' => 'Recuperado', 'cronico' => 'Crónico', 'murio' => 'Murió'];

    protected $fillable = [
        'ganado_id', 'fecha_inicio', 'diagnostico', 'sintomas', 'severidad', 'en_enfermeria', 'es_recaida',
        'fecha_alta', 'resultado', 'notas', 'user_id',
    ];

    protected $casts = [
        'fecha_inicio'  => 'date',
        'fecha_alta'    => 'date',
        'en_enfermeria' => 'boolean',
        'es_recaida'    => 'boolean',
    ];

    public function ganado(): BelongsTo
    {
        return $this->belongsTo(Ganado::class, 'ganado_id', 'GanadoID');
    }

    public function tratamientos(): HasMany
    {
        return $this->hasMany(TratamientoSanitario::class, 'evento_enfermedad_id');
    }

    public function scopeAbiertos(Builder $q): Builder
    {
        return $q->where('resultado', 'en_tratamiento');
    }

    public function diasEnfermo(): int
    {
        return (int) $this->fecha_inicio->diffInDays($this->fecha_alta ?? now());
    }

    public function descripcionAuditoria(): string
    {
        return $this->diagnostico;
    }
}
