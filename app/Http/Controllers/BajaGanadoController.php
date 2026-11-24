<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ganado\StoreBajaRequest;
use App\Models\Ganado\BajaGanado;
use App\Services\MortalidadService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BajaGanadoController extends Controller
{
    public function __construct(
        protected MortalidadService $mortalidadService
    ) {}

    /**
     * Bitácora general de mortalidad y estadísticas del rancho.
     */
    public function index(): View
    {
        $bajas = BajaGanado::with(['ganado.factura.proveedor', 'usuario'])
            ->latest('fecha_muerte')
            ->paginate(15);

        $metricas = $this->mortalidadService->obtenerEstadisticas();

        return view('animals.bajas.index', compact('bajas', 'metricas'));
    }

    /**
     * Registra el fallecimiento de un animal y bloquea sus operaciones activas.
     */
    public function store(StoreBajaRequest $request): RedirectResponse
    {
        try {
            $baja = $this->mortalidadService->registrarBaja(
                ganadoId: (int) $request->input('ganado_id'),
                fechaMuerte: $request->input('fecha_muerte'),
                causaMuerte: $request->input('causa_muerte'),
                necropsia: $request->input('necropsia'),
                observaciones: $request->input('observaciones'),
                userId: Auth::id()
            );

            return back()->with('success', "Baja por mortalidad del animal registrada correctamente.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}
