<?php

namespace App\Models;

use App\Support\Plantas;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    public const ESTADO_ACTIVO = 'active';
    public const ESTADO_INACTIVO = 'inactive';

    public const ROL_SUPER_ADMIN = 'super-admin';
    public const ROL_DUENO = 'dueno';

    public const TEMAS = ['claro', 'oscuro', 'metal'];

    protected $fillable = [
        'name',
        'email',
        'telefono',
        'password',
        'status',
        'tema',
        'last_division',
        'last_login_ip',
        'last_login_user_agent',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
    ];

    public function esSuperAdmin(): bool
    {
        return $this->hasRole(self::ROL_SUPER_ADMIN);
    }

    public function esDueno(): bool
    {
        return $this->hasRole(self::ROL_DUENO);
    }

    /** Super admin o dueño: acceso total al sistema. */
    public function tieneAccesoTotal(): bool
    {
        return $this->hasAnyRole([self::ROL_SUPER_ADMIN, self::ROL_DUENO]);
    }

    public function estaActivo(): bool
    {
        // null = atributo aún no cargado (la columna tiene 'active' por defecto)
        return $this->status === null || $this->status === self::ESTADO_ACTIVO;
    }

    /** Nivel jerárquico más alto entre sus roles. */
    public function nivel(): int
    {
        return (int) ($this->roles->max('nivel') ?? 0);
    }

    /** Tema visual normalizado (claro | oscuro | metal). Por defecto: oscuro. */
    public function temaPreferido(): string
    {
        $tema = $this->tema === 'dark' ? 'oscuro' : $this->tema;

        return in_array($tema, self::TEMAS, true) ? $tema : 'oscuro';
    }

    /** Plantas a las que tiene acceso este usuario. */
    public function plantasAccesibles()
    {
        return Plantas::accesibles($this);
    }

    public function iniciales(): string
    {
        $partes = preg_split('/\s+/', trim($this->name)) ?: [];
        $ini = mb_substr($partes[0] ?? '', 0, 1) . mb_substr($partes[1] ?? '', 0, 1);

        return mb_strtoupper($ini ?: mb_substr($this->name, 0, 2));
    }
}
