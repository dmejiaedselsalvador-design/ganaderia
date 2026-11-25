<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\Rule;

class PreferenciasController extends Controller
{
    /**
     * Guarda el tema visual (claro | oscuro | metal) en el usuario y en una cookie
     * para que también se aplique en la pantalla de login.
     */
    public function tema(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'tema' => ['required', Rule::in(User::TEMAS)],
        ]);

        if ($user = $request->user()) {
            $user->forceFill(['tema' => $data['tema']])->saveQuietly();
        }

        Cookie::queue(cookie('tema', $data['tema'], 60 * 24 * 365, null, null, null, false));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'tema' => $data['tema']]);
        }

        return back();
    }
}
