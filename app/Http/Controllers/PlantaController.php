<?php

namespace App\Http\Controllers;

use App\Support\Plantas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlantaController extends Controller
{
    /**
     * Punto de entrada después del login.
     * - 0 plantas: pantalla de "sin acceso".
     * - 1 planta: se activa automáticamente y va directo a su dashboard.
     * - 2+ plantas: si ya hay planta activa en sesión va a ella; si no, al selector.
     */
    public function inicio(Request $request): RedirectResponse|View
    {
        $user = $request->user();
        $accesibles = Plantas::accesibles($user);

        if ($accesibles->isEmpty()) {
            return view('plantas.sin-acceso');
        }

        $activa = Plantas::activa();

        if ($activa && Plantas::puedeAcceder($user, $activa)) {
            return redirect()->to(Plantas::urlDashboard($activa));
        }

        if ($accesibles->count() === 1) {
            $clave = $accesibles->keys()->first();
            Plantas::activar($user, $clave);

            return redirect()->to(Plantas::urlDashboard($clave));
        }

        return redirect()->route('plantas.selector');
    }

    /** Pantalla de selección de planta. */
    public function selector(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $plantas = Plantas::accesibles($user);

        if ($plantas->count() <= 1) {
            return redirect()->route('inicio');
        }

        return view('plantas.selector', [
            'plantas' => $plantas,
            'activa'  => Plantas::activa(),
            'ultima'  => $user->last_division,
        ]);
    }

    /** Cambia la planta activa y envía al dashboard de la nueva planta. */
    public function cambiar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'planta' => ['required', 'string', Rule::in(Plantas::todas()->keys()->all())],
        ]);

        $user = $request->user();

        if (! Plantas::puedeAcceder($user, $data['planta'])) {
            return back()->with('aviso', 'No tienes acceso a esa planta.');
        }

        Plantas::activar($user, $data['planta']);
        $info = Plantas::info($data['planta']);

        return redirect()->to(Plantas::urlDashboard($data['planta']))
            ->with('exito', "Ahora estás trabajando en {$info['nombre']}.");
    }
}
