<?php

namespace App\Http\Controllers;

use App\Http\Requests\Proveedor\StoreProveedorRequest;
use App\Http\Requests\Proveedor\UpdateProveedorRequest;
use App\Models\Ganado\ProveedorGanado;
use App\Services\LiquidacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    public function __construct(
        protected LiquidacionService $liquidacionService
    ) {}

    /**
     * Listado consolidado de proveedores con saldos y adelantos calculados.
     */
    public function index(): View
    {
        $proveedores = $this->liquidacionService->obtenerSaldosProveedores();

        return view('proveedores.index', compact('proveedores'));
    }

    /**
     * Formulario de creación de un nuevo proveedor / rancho.
     */
    public function create(): View
    {
        return view('proveedores.create');
    }

    /**
     * Guarda un nuevo proveedor.
     */
    public function store(StoreProveedorRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $datos['nombreProveedor'] = $request->input('nombreProveedor') ?? $request->input('nombreProoveedor');

        ProveedorGanado::create($datos);

        return redirect()
            ->route('proveedores.index')
            ->with('success', 'Proveedor creado exitosamente.');
    }

    /**
     * Formulario de edición de un proveedor existente.
     */
    public function edit(int $id): View
    {
        $proveedor = ProveedorGanado::findOrFail($id);

        return view('proveedores.edit', compact('proveedor'));
    }

    /**
     * Actualiza la información y valida la desactivación en caso de saldos deudores pendientes.
     */
    public function update(UpdateProveedorRequest $request, int $id): RedirectResponse
    {
        $proveedor = ProveedorGanado::findOrFail($id);

        $nuevoEstado = $request->has('estado') ? ProveedorGanado::ESTADO_ACTIVO : ProveedorGanado::ESTADO_INACTIVO;
        $mensajeAlerta = 'Proveedor editado exitosamente.';

        if ($nuevoEstado === ProveedorGanado::ESTADO_INACTIVO && $proveedor->tieneDeudaPendiente()) {
            $nuevoEstado = ProveedorGanado::ESTADO_ACTIVO;
            $mensajeAlerta = 'Proveedor editado exitosamente, pero NO se pudo desactivar porque tiene adelantos con saldo pendiente.';
        }

        $datos = $request->validated();
        $datos['nombreProveedor'] = $request->input('nombreProveedor') ?? $request->input('nombreProoveedor');
        $datos['estado'] = $nuevoEstado;

        $proveedor->update($datos);

        return redirect()
            ->route('proveedores.index')
            ->with('success', $mensajeAlerta);
    }
}
