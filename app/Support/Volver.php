<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Atajo "ir a crear y regresar": las pantallas reciben ?volver=<url> y, al guardar,
 * regresan al usuario a donde estaba. Solo se aceptan URLs de este mismo sistema.
 */
class Volver
{
    public static function url(?Request $request = null): ?string
    {
        $request ??= request();
        $url = (string) $request->input('volver', '');

        if ($url === '') {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);
        $propio = parse_url(config('app.url'), PHP_URL_HOST);

        // Solo rutas relativas o del mismo dominio (evita redirecciones abiertas)
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return $url;
        }

        return $host && ($host === $request->getHost() || $host === $propio) ? $url : null;
    }

    /** Agrega parámetros (p. ej. el id recién creado) a la URL de regreso. */
    public static function con(string $url, array $params): string
    {
        return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($params);
    }
}
