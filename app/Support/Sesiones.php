<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Sesiones abiertas (requiere SESSION_DRIVER=database).
 * Expulsar = borrar la sesión y anular el «Mantener sesión iniciada» del usuario,
 * para que el equipo no vuelva a entrar solo con la cookie de recordar.
 */
class Sesiones
{
    /** Minutos sin actividad para considerar a alguien «en línea». */
    public const MINUTOS_EN_LINEA = 5;

    public static function disponible(): bool
    {
        return config('session.driver') === 'database' && Schema::hasTable(config('session.table', 'sessions'));
    }

    private static function tabla()
    {
        return DB::table(config('session.table', 'sessions'));
    }

    /** Sesiones de usuarios autenticados que no han vencido, la más reciente primero. */
    public static function activas(): Collection
    {
        if (! self::disponible()) {
            return collect();
        }
        $limite = now()->subMinutes((int) config('session.lifetime', 120))->getTimestamp();
        $filas = self::tabla()->whereNotNull('user_id')->where('last_activity', '>=', $limite)->orderByDesc('last_activity')->get();
        $usuarios = User::with('roles')->whereIn('id', $filas->pluck('user_id')->unique())->get()->keyBy('id');

        return $filas->map(function ($s) use ($usuarios) {
            $ultima = Carbon::createFromTimestamp($s->last_activity);

            return (object) [
                'id'          => $s->id,
                'usuario'     => $usuarios[$s->user_id] ?? null,
                'ip'          => $s->ip_address,
                'dispositivo' => self::dispositivo((string) $s->user_agent),
                'ultima'      => $ultima,
                'en_linea'    => $ultima->gt(now()->subMinutes(self::MINUTOS_EN_LINEA)),
                'planta'      => self::planta($s->payload),
            ];
        })->filter(fn ($s) => $s->usuario);
    }

    /** Cierra una sesión concreta. */
    public static function expulsar(string $sesionId): ?int
    {
        $fila = self::tabla()->where('id', $sesionId)->first();
        if (! $fila) {
            return null;
        }
        self::tabla()->where('id', $sesionId)->delete();
        if ($fila->user_id && ($u = User::find($fila->user_id))) {
            self::anularRecordar($u);
        }

        return $fila->user_id;
    }

    /** Cierra todas las sesiones de un usuario (menos, opcionalmente, una). Devuelve cuántas cerró. */
    public static function cerrarDeUsuario(User $user, ?string $excepto = null): int
    {
        self::anularRecordar($user);
        if (! self::disponible()) {
            return 0;
        }

        return self::tabla()->where('user_id', $user->id)->when($excepto, fn ($q) => $q->where('id', '!=', $excepto))->delete();
    }

    /** El «Mantener sesión iniciada» de ese usuario deja de servir en todos sus equipos. */
    private static function anularRecordar(User $user): void
    {
        $user->forceFill(['remember_token' => Str::random(60)])->saveQuietly();
    }

    /** «Chrome · Windows», «Safari · iPhone»… y si es celular. */
    public static function dispositivo(string $ua): array
    {
        $so = match (true) {
            str_contains($ua, 'iPhone') => 'iPhone', str_contains($ua, 'iPad') => 'iPad', str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Windows') => 'Windows', str_contains($ua, 'Mac OS') => 'Mac', str_contains($ua, 'Linux') => 'Linux',
            default => 'Otro',
        };
        $nav = match (true) {
            str_contains($ua, 'Edg/') => 'Edge', str_contains($ua, 'OPR/') => 'Opera', str_contains($ua, 'SamsungBrowser') => 'Samsung Internet',
            str_contains($ua, 'Firefox') => 'Firefox', str_contains($ua, 'CriOS'), str_contains($ua, 'Chrome') => 'Chrome',
            str_contains($ua, 'Safari') => 'Safari', default => 'Navegador',
        };

        return ['texto' => "{$nav} · {$so}", 'movil' => in_array($so, ['iPhone', 'Android', 'iPad'], true) || str_contains($ua, 'Mobile')];
    }

    /** Planta activa guardada en la sesión (ganadería / construcción), si se puede leer. */
    private static function planta(string $payload): ?string
    {
        try {
            $datos = @unserialize(base64_decode($payload), ['allowed_classes' => false]);

            return is_array($datos) ? ($datos[config('plantas.session_key', 'active_division')] ?? null) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
