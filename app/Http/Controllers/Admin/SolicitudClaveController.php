<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SolicitudClave;
use App\Models\User;
use App\Support\ClaveTemporal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

/**
 * Bandeja de solicitudes de cambio de clave (Súper Admin / Dueño o quien tenga
 * el permiso usuarios.solicitudes_clave).
 */
class SolicitudClaveController extends Controller
{
    public function index(Request $request): View
    {
        $filtro = $request->input('ver', 'pendientes');

        $solicitudes = SolicitudClave::with(['user.roles', 'atendidaPor'])
            ->when($filtro === 'pendientes', fn ($q) => $q->pendientes())
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.solicitudes.index', [
            'solicitudes' => $solicitudes,
            'filtro'      => $filtro,
            'pendientes'  => SolicitudClave::pendientesCount(),
            'bloqueados'  => User::where('bloqueo_solicitud_clave', true)->orderBy('name')->get(),
        ]);
    }

    public function aprobar(Request $request, SolicitudClave $solicitud): RedirectResponse
    {
        if ($error = $this->validarAtendible($request->user(), $solicitud)) {
            return back()->with('aviso', $error);
        }

        $clave = ClaveTemporal::generar();
        // La clave anterior deja de funcionar y el usuario deberá cambiar esta al ingresar
        $solicitud->user->asignarClaveTemporal($clave);

        $solicitud->forceFill([
            'estado'        => SolicitudClave::APROBADA,
            'clave_cifrada' => Crypt::encryptString($clave),
            'atendida_por'  => $request->user()->id,
            'atendida_at'   => now(),
        ])->save();

        return back()->with('exito', "Solicitud {$solicitud->folio()} aprobada. {$solicitud->user->name} verá su clave temporal al consultar su solicitud desde su navegador (tiene " . SolicitudClave::HORAS_PARA_VER . ' h).');
    }

    public function denegar(Request $request, SolicitudClave $solicitud): RedirectResponse
    {
        $data = $request->validate([
            'motivo'   => ['nullable', 'string', 'max:255'],
            'bloquear' => ['nullable', 'boolean'],
        ]);

        if ($error = $this->validarAtendible($request->user(), $solicitud, false)) {
            return back()->with('aviso', $error);
        }

        $solicitud->forceFill([
            'estado'       => SolicitudClave::DENEGADA,
            'motivo'       => $data['motivo'] ?? null,
            'atendida_por' => $request->user()->id,
            'atendida_at'  => now(),
        ])->save();

        $mensaje = "Solicitud {$solicitud->folio()} denegada.";

        if ($request->boolean('bloquear')) {
            $solicitud->user->forceFill(['bloqueo_solicitud_clave' => true])->save();
            $mensaje .= " {$solicitud->user->name} ya no podrá enviar solicitudes de cambio de clave.";
        }

        return back()->with('exito', $mensaje);
    }

    /** Bloquear / desbloquear las solicitudes de un usuario. */
    public function bloqueo(Request $request, User $user): RedirectResponse
    {
        if ($user->esSuperAdmin() && ! $request->user()->esSuperAdmin()) {
            return back()->with('aviso', 'Por seguridad, no puedes modificar al Súper Administrador.');
        }

        $bloquear = ! $user->bloqueo_solicitud_clave;
        $user->forceFill(['bloqueo_solicitud_clave' => $bloquear])->save();

        if ($bloquear) {
            // Las solicitudes pendientes se cierran
            SolicitudClave::where('user_id', $user->id)->where('estado', SolicitudClave::PENDIENTE)
                ->update(['estado' => SolicitudClave::DENEGADA, 'motivo' => 'Solicitudes bloqueadas por el administrador',
                          'atendida_por' => $request->user()->id, 'atendida_at' => now()]);
        }

        return back()->with('exito', $bloquear
            ? "{$user->name} ya no puede solicitar cambios de clave."
            : "{$user->name} puede volver a solicitar cambios de clave.");
    }

    private function validarAtendible(User $actor, SolicitudClave $solicitud, bool $exigirActivo = true): ?string
    {
        if ($solicitud->estado !== SolicitudClave::PENDIENTE) {
            return 'Esta solicitud ya fue atendida.';
        }
        if ($solicitud->vencida()) {
            return 'La solicitud venció. El usuario debe enviar una nueva.';
        }
        $user = $solicitud->user;
        if ($exigirActivo && (! $user->estaActivo() || $user->bloqueo_solicitud_clave)) {
            return 'El usuario está dado de baja o tiene bloqueadas las solicitudes.';
        }
        if ($user->esSuperAdmin() && ! $actor->esSuperAdmin()) {
            return 'Solo un Súper Administrador puede atender solicitudes de otro Súper Administrador.';
        }
        if ($actor->is($user)) {
            return 'No puedes atender tu propia solicitud.';
        }

        return null;
    }
}
