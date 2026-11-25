<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Dashboard de análisis de la planta Ganadería.
     */
    public function index(Request $request): View
    {
        return view('welcome', $this->dashboardService->obtenerMetricas((int) $request->input('meses', 6)));
    }
}
