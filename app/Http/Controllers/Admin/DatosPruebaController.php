<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Simulacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

/**
 * Administración → Datos de prueba (solo Súper Administrador).
 * Carga una operación simulada de ~3 meses para ver el sistema lleno y la borra con un clic.
 */
class DatosPruebaController extends Controller
{
    private function soloSuperAdmin(): void
    {
        abort_unless(request()->user()?->hasRole('super-admin'), 403, 'Solo el Súper Administrador puede manejar los datos de prueba.');
    }

    public function index(): View
    {
        $this->soloSuperAdmin();
        return view('admin.datos-prueba', [
            'activa'      => Simulacion::activa(),
            'sePuede'     => Simulacion::sePuedeCargar(),
            'resumen'     => Simulacion::resumen(),
        ]);
    }

    public function cargar(): RedirectResponse
    {
        $this->soloSuperAdmin();
        if (! Simulacion::sePuedeCargar()) {
            return back()->with('aviso', 'No se puede cargar: ya hay ganado registrado o ya hay una simulación cargada.');
        }

        try {
            $clave = Simulacion::cargar();
        } catch (Throwable $e) {
            report($e);
            // Si algo falló a medias, se limpia lo que alcanzó a crear
            Simulacion::eliminar();

            return back()->with('aviso', 'No se pudo cargar la simulación: ' . $e->getMessage());
        }

        return redirect()->route('datos-prueba.index')->with('exito',
            "Datos de prueba cargados. Usuarios de prueba: demo.dueno@simulacion.local, demo.gerente@…, demo.supervisor@…, demo.operario@…, demo.vendedor@…, demo.comprador@… · clave: {$clave}");
    }

    public function eliminar(Request $request): RedirectResponse
    {
        $this->soloSuperAdmin();
        $request->validate(['confirmacion' => ['required', 'in:ELIMINAR']], [
            'confirmacion.in' => 'Escribe ELIMINAR en mayúsculas para confirmar.', 'confirmacion.required' => 'Escribe ELIMINAR para confirmar.',
        ]);

        $total = Simulacion::eliminar();

        return redirect()->route('datos-prueba.index')->with('exito', "Datos de prueba eliminados ({$total} registros). El sistema quedó listo para capturar datos reales.");
    }
}
