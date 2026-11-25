<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Solicitud de cambio de clave hecha desde "¿Olvidó su contraseña?".
 * La atiende un administrador dentro del sistema (sin correo).
 */
class SolicitudClave extends Model
{
    protected $table = 'solicitudes_clave';

    public const PENDIENTE = 'pendiente';
    public const APROBADA  = 'aprobada';   // clave generada, esperando que el usuario la vea
    public const ENTREGADA = 'entregada';  // el usuario ya vio la clave
    public const DENEGADA  = 'denegada';

    /** Días que una solicitud pendiente sigue vigente. */
    public const DIAS_VIGENCIA = 7;
    /** Horas que el usuario tiene para ver la clave aprobada. */
    public const HORAS_PARA_VER = 24;

    protected $fillable = [
        'user_id', 'token_hash', 'estado', 'ip', 'user_agent',
        'clave_cifrada', 'atendida_por', 'atendida_at', 'entregada_at', 'motivo',
    ];

    protected $hidden = ['token_hash', 'clave_cifrada'];

    protected $casts = [
        'atendida_at'  => 'datetime',
        'entregada_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function atendidaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atendida_por');
    }

    /** Pendientes vigentes de usuarios activos y no bloqueados. */
    public function scopePendientes(Builder $q): Builder
    {
        return $q->where('estado', self::PENDIENTE)
            ->where('updated_at', '>=', now()->subDays(self::DIAS_VIGENCIA))
            ->whereHas('user', fn ($u) => $u->where('status', User::ESTADO_ACTIVO)->where('bloqueo_solicitud_clave', false));
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public function folio(): string
    {
        return 'SOL-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function vencida(): bool
    {
        return match ($this->estado) {
            self::PENDIENTE => $this->updated_at->lt(now()->subDays(self::DIAS_VIGENCIA)),
            self::APROBADA  => $this->atendida_at?->lt(now()->subHours(self::HORAS_PARA_VER)) ?? true,
            default         => false,
        };
    }

    /** Cuántas solicitudes esperan respuesta (para la campana de notificaciones). */
    public static function pendientesCount(): int
    {
        return static::pendientes()->count();
    }
}
