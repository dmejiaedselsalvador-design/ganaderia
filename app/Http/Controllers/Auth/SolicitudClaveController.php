<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SolicitudClave;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * "¿Olvidó su contraseña?" sin correo electrónico:
 * el usuario envía una solicitud, un administrador la aprueba o la deniega
 * dentro del sistema y, si la aprueba, el usuario ve su clave temporal en
 * ESTE MISMO navegador (cookie con un token secreto).
 */
class SolicitudClaveController extends Controller
{
    public const COOKIE = 'solicitud_clave';

    public function create(Request $request): View
    {
        return view('auth.forgot-password', [
            'tieneSolicitud' => (bool) $this->solicitudDelNavegador($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->input('email'))->first();

        // Mismo mensaje si el correo no existe, está dado de baja o tiene bloqueadas las solicitudes
        if (! $user || ! $user->puedeSolicitarClave()) {
            return back()->withInput()->withErrors([
                'email' => 'No es posible procesar una solicitud para ese correo. Comunícate con el administrador.',
            ]);
        }

        $token = Str::random(48);

        // Una sola solicitud pendiente por usuario: si ya existe, se renueva para este navegador
        $solicitud = SolicitudClave::where('user_id', $user->id)->where('estado', SolicitudClave::PENDIENTE)->first()
            ?? new SolicitudClave(['user_id' => $user->id, 'estado' => SolicitudClave::PENDIENTE]);

        $solicitud->fill([
            'token_hash' => SolicitudClave::hashToken($token),
            'ip'         => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
        ]);
        $solicitud->updated_at = now();
        $solicitud->save();

        Cookie::queue(cookie(self::COOKIE, $token, 60 * 24 * SolicitudClave::DIAS_VIGENCIA));

        return redirect()->route('password.solicitud.estado');
    }

    /** Estado de la solicitud hecha desde este navegador. */
    public function estado(Request $request): View
    {
        $solicitud = $this->solicitudDelNavegador($request);
        $clave = null;

        if ($solicitud && $solicitud->estado === SolicitudClave::APROBADA) {
            if ($solicitud->vencida() || ! $solicitud->user?->estaActivo() || ! $solicitud->clave_cifrada) {
                $solicitud->forceFill(['clave_cifrada' => null])->save();
            } else {
                // Se muestra UNA sola vez y se borra
                $clave = Crypt::decryptString($solicitud->clave_cifrada);
                $solicitud->forceFill([
                    'estado'        => SolicitudClave::ENTREGADA,
                    'clave_cifrada' => null,
                    'entregada_at'  => now(),
                ])->save();
            }
        }

        return view('auth.solicitud-clave-estado', [
            'solicitud' => $solicitud,
            'clave'     => $clave,
        ]);
    }

    private function solicitudDelNavegador(Request $request): ?SolicitudClave
    {
        $token = $request->cookie(self::COOKIE);

        return $token
            ? SolicitudClave::with('user')->where('token_hash', SolicitudClave::hashToken($token))->first()
            : null;
    }
}
