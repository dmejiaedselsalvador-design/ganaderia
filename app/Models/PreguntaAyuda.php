<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Pregunta enviada desde el botón de Ayuda de una pantalla. */
class PreguntaAyuda extends Model
{
    protected $table = 'preguntas_ayuda';

    public const PENDIENTE  = 'pendiente';
    public const RESPONDIDA = 'respondida';
    public const CERRADA    = 'cerrada';

    public const ESTADOS = [
        self::PENDIENTE  => 'Pendiente',
        self::RESPONDIDA => 'Respondida',
        self::CERRADA    => 'Cerrada',
    ];

    protected $fillable = [
        'user_id', 'planta', 'ruta', 'pantalla', 'url', 'pregunta', 'captura_path', 'navegador',
        'estado', 'respuesta', 'respondida_por', 'respondida_at', 'respuesta_vista_at',
    ];

    protected $casts = [
        'respondida_at'      => 'datetime',
        'respuesta_vista_at' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function respondidaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'respondida_por');
    }

    public function scopePendientes(Builder $q): Builder
    {
        return $q->where('estado', self::PENDIENTE);
    }

    /** Respuestas que el usuario todavía no ha leído. */
    public function scopeRespuestasNuevas(Builder $q, int $userId): Builder
    {
        return $q->where('user_id', $userId)->whereNotNull('respondida_at')->whereNull('respuesta_vista_at');
    }
}
