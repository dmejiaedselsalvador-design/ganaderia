<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Cambio de clave obligatorio en el primer ingreso (clave temporal).
 */
class CambioClaveController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if (! $request->user()->debeCambiarClave()) {
            return redirect()->route('inicio');
        }

        return view('auth.cambiar-clave');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', 'different:current_password', Password::defaults()],
        ], [
            'current_password.current_password' => 'La clave temporal no es correcta.',
            'password.different'                => 'La nueva clave debe ser diferente a la clave temporal.',
        ]);

        $request->user()->establecerClavePropia($request->input('password'));
        $request->session()->regenerate();

        return redirect()->route('inicio')->with('exito', 'Tu clave fue actualizada. Ya puedes usar el sistema.');
    }
}
