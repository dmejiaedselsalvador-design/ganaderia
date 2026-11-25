<?php

namespace App\Http\Middleware;

use App\Support\Plantas;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Uso: ->middleware('division:ganaderia')
 *
 * - Verifica que el usuario tenga permiso sobre la planta de la ruta.
 * - Mantiene la planta activa en sesión; si el usuario intenta entrar a rutas
 *   de otra planta sin cambiarla desde el menú, lo regresa a su planta activa.
 */
class EnsureDivisionAccess
{
    public function handle(Request $request, Closure $next, string $division): Response
    {
        $user = Auth::user();
        $activa = Plantas::activa();

        // La planta en sesión ya no es válida (p. ej. le quitaron el permiso).
        if ($activa && ! Plantas::puedeAcceder($user, $activa)) {
            Plantas::olvidar();
            $activa = null;
        }

        if (! $activa) {
            $accesibles = Plantas::accesibles($user);

            if ($accesibles->isEmpty()) {
                return redirect()->route('inicio');
            }

            if ($accesibles->count() === 1) {
                // Solo una planta: se asigna automáticamente.
                $activa = $accesibles->keys()->first();
            } elseif (Auth::viaRemember() && Plantas::puedeAcceder($user, $user->last_division)) {
                // Sesión restaurada con "Mantener sesión iniciada": recordar la última planta.
                $activa = $user->last_division;
            } else {
                return redirect()->route('plantas.selector');
            }

            Plantas::activar($user, $activa);
        }

        if ($activa !== $division) {
            $nombre = config("plantas.plantas.$division.nombre", $division);
            $actual = config("plantas.plantas.$activa.nombre");

            return redirect()->to(Plantas::urlDashboard($activa))->with('aviso',
                Plantas::puedeAcceder($user, $division)
                    ? "Estás trabajando en {$actual}. Usa «Cambiar de planta» en tu menú para entrar a {$nombre}."
                    : "No tienes acceso a la planta {$nombre}."
            );
        }

        View::share('plantaActiva', Plantas::info($activa));

        return $next($request);
    }
}
