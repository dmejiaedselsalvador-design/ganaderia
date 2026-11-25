<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Administra las plantas (divisiones) del grupo y la planta activa en sesión.
 */
class Plantas
{
    /** Todas las plantas configuradas, indexadas por clave. */
    public static function todas(): Collection
    {
        return collect(config('plantas.plantas', []))
            ->map(fn (array $p, string $clave) => $p + ['clave' => $clave]);
    }

    public static function existe(?string $clave): bool
    {
        return $clave !== null && array_key_exists($clave, config('plantas.plantas', []));
    }

    public static function info(?string $clave): ?array
    {
        return self::existe($clave) ? self::todas()->get($clave) : null;
    }

    /** Plantas a las que el usuario tiene acceso. */
    public static function accesibles(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        return self::todas()->filter(fn (array $p) => $user->can($p['permiso']));
    }

    public static function puedeAcceder(?User $user, ?string $clave): bool
    {
        return self::existe($clave) && $user && $user->can(config("plantas.plantas.$clave.permiso"));
    }

    /** Clave de la planta activa en la sesión actual (o null). */
    public static function activa(): ?string
    {
        $clave = session(config('plantas.session_key'));

        return self::existe($clave) ? $clave : null;
    }

    /** Guarda la planta activa en sesión y la recuerda en el usuario. */
    public static function activar(User $user, string $clave): void
    {
        session([config('plantas.session_key') => $clave]);

        if ($user->last_division !== $clave) {
            $user->forceFill(['last_division' => $clave])->saveQuietly();
        }
    }

    public static function olvidar(): void
    {
        session()->forget(config('plantas.session_key'));
    }

    /** URL del dashboard de una planta. */
    public static function urlDashboard(string $clave): string
    {
        return route(config("plantas.plantas.$clave.ruta"));
    }
}
