<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Si el usuario ingresó con una clave temporal, solo puede usar la pantalla
 * de cambio de clave (y cerrar sesión) hasta que defina su clave personal.
 */
class ForcePasswordChange
{
    /** Rutas permitidas mientras la clave sea temporal. */
    private const PERMITIDAS = [
        'password.cambiar',
        'password.cambiar.guardar',
        'logout',
        'tema.cambiar',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->debeCambiarClave() && ! $request->routeIs(...self::PERMITIDAS)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Debes cambiar tu clave temporal antes de continuar.'], 423);
            }

            return redirect()->route('password.cambiar');
        }

        return $next($request);
    }
}
