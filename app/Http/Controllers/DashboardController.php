<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
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
}
