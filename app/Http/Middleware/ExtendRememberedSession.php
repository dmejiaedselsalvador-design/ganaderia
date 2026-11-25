<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Si el usuario marcó "Mantener sesión iniciada" (existe la cookie remember_*),
 * se amplía la vida de la sesión para que no expire tras unas horas de inactividad
 * y no aparezca el error 419 al enviar formularios.
 */
class ExtendRememberedSession
{
    public function handle(Request $request, Closure $next): Response
    {
        foreach (array_keys($request->cookies->all()) as $nombre) {
            if (Str::startsWith($nombre, 'remember_web_')) {
                config(['session.lifetime' => (int) config('auth.remember_minutes', 43200)]);
                break;
            }
        }

        return $next($request);
    }
}
