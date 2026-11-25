<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\User;
use App\Support\Sesiones;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Administración → Sesiones activas (solo Súper Administrador y Dueño).
 * Ver quién está conectado y expulsar sesiones.
 */
class SesionController extends Controller
{
    private function autorizar(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['super-admin', 'dueno']), 403, 'Solo el Súper Administrador o el Dueño pueden ver las sesiones.');
    }

    /** El Dueño no puede expulsar al Súper Administrador (misma regla que al eliminar). */
    private function puedeExpulsar(User $actor, User $objetivo): bool
    {
        return $actor->hasRole('super-admin') || ! $objetivo->hasRole('super-admin');
    }

    public function index(Request $request): View
    {
        $this->autorizar($request);
        $sesiones = Sesiones::activas();

        return view('admin.sesiones', [
            'disponible' => Sesiones::disponible(),
            'sesiones'   => $sesiones,
            'actual'     => $request->session()->getId(),
            'enLinea'    => $sesiones->where('en_linea', true)->pluck('usuario.id')->unique()->count(),
            'usuarios'   => $sesiones->pluck('usuario.id')->unique()->count(),
        ]);
    }

    public function expulsar(Request $request, string $sesion): RedirectResponse
    {
        $this->autorizar($request);
        if ($sesion === $request->session()->getId()) {
            return back()->with('aviso', 'Esa es tu sesión actual. Para salir usa «Cerrar sesión».');
        }
        $fila = Sesiones::activas()->firstWhere('id', $sesion);
        if (! $fila) {
            return back()->with('aviso', 'Esa sesión ya no existe.');
        }
        if (! $this->puedeExpulsar($request->user(), $fila->usuario)) {
            return back()->with('aviso', 'No puedes expulsar al Súper Administrador.');
        }

        Sesiones::expulsar($sesion);
        $this->bitacora($request, $fila->usuario, "Sesión expulsada ({$fila->dispositivo['texto']}, IP {$fila->ip})");

        return back()->with('exito', "Se cerró la sesión de {$fila->usuario->name} en {$fila->dispositivo['texto']}.");
    }

    public function expulsarUsuario(Request $request, User $user): RedirectResponse
    {
        $this->autorizar($request);
        if (! $this->puedeExpulsar($request->user(), $user)) {
            return back()->with('aviso', 'No puedes expulsar al Súper Administrador.');
        }
        $excepto = $request->user()->is($user) ? $request->session()->getId() : null;
        $n = Sesiones::cerrarDeUsuario($user, $excepto);
        $this->bitacora($request, $user, "Se cerraron {$n} sesión(es) en todos sus equipos");

        return back()->with('exito', $request->user()->is($user)
            ? "Se cerraron tus sesiones en otros equipos ({$n})."
            : "{$user->name} fue sacado de todos sus equipos ({$n} sesión(es)).");
    }

    /** Saca a todos menos a quien lo ejecuta (y, si es el Dueño, menos al Súper Administrador). */
    public function expulsarTodos(Request $request): RedirectResponse
    {
        $this->autorizar($request);
        $n = 0;
        foreach (Sesiones::activas()->pluck('usuario')->unique('id') as $u) {
            if ($u->is($request->user()) || ! $this->puedeExpulsar($request->user(), $u)) {
                continue;
            }
            $n += Sesiones::cerrarDeUsuario($u);
        }
        Sesiones::cerrarDeUsuario($request->user(), $request->session()->getId());
        $this->bitacora($request, null, "Se cerraron todas las sesiones ({$n})");

        return back()->with('exito', "Se cerraron {$n} sesión(es). Todos tendrán que volver a iniciar sesión.");
    }

    /** Desde «Mi perfil»: cualquier usuario puede cerrar sus otras sesiones. */
    public function cerrarMisOtras(Request $request): RedirectResponse
    {
        $n = Sesiones::cerrarDeUsuario($request->user(), $request->session()->getId());
        // Se renueva la cookie de «recordar» de este equipo para que siga funcionando aquí
        if ($request->hasCookie(\Illuminate\Support\Facades\Auth::guard()->getRecallerName())) {
            \Illuminate\Support\Facades\Auth::login($request->user(), true);
        }

        return back()->with('exito', $n ? "Se cerraron tus sesiones en otros {$n} equipo(s)." : 'No tenías sesiones abiertas en otros equipos.');
    }

    private function bitacora(Request $request, ?User $user, string $descripcion): void
    {
        Auditoria::create([
            'user_id' => $request->user()->id, 'accion' => 'eliminado', 'modelo' => 'Sesion', 'modelo_id' => $user?->id,
            'descripcion' => ($user ? "{$user->name}: " : '') . $descripcion, 'ip' => $request->ip(), 'url' => $request->fullUrl(),
        ]);
    }
}
