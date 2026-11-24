<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Muestra la pantalla principal y métricas consolidadas del rancho.
     */
    public function index(): View
    {
        $metricas = $this->dashboardService->obtenerMetricas();

        return view('welcome', $metricas);
    }

    /**
     * Guarda la preferencia del tema visual (claro, metal, dark) en la caché del sistema y sesión.
     */
    public function cambiarTema(Request $request): JsonResponse
    {
        $tema = strtolower($request->input('tema', 'claro'));
        if (!in_array($tema, ['claro', 'metal', 'dark'], true)) {
            $tema = 'claro';
        }

        $userId = Auth::id() ?? 'guest';
        Cache::put("tema_usuario_{$userId}", $tema, now()->addYear());
        session(['tema_usuario' => $tema]);

        return response()->json([
            'success' => true,
            'tema'    => $tema,
            'mensaje' => "Tema {$tema} guardado en caché correctamente.",
        ]);
    }
}
