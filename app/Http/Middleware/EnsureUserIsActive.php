<?php

namespace App\Http\Middleware;

use App\Support\Plantas;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cierra la sesión de usuarios dados de baja, incluso si entraron
 * con la cookie de "Mantener sesión iniciada".
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && ! $user->estaActivo()) {
            Auth::guard('web')->logout();
            Plantas::olvidar();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Tu cuenta está inactiva. Contacta al administrador.',
            ]);
        }

        return $next($request);
    }
}
