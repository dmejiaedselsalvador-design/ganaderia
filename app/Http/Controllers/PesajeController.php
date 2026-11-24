<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ganado\StorePesajeRequest;
use App\Models\Ganado\Ganado;
use App\Services\PesajeService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PesajeController extends Controller
{
    public function __construct(
        protected PesajeService $pesajeService
    ) {}

    /**
     * Registra un nuevo pesaje para un animal y actualiza su ganancia diaria de peso (GDP).
     */
    public function store(StorePesajeRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $pesaje = $this->pesajeService->registrarPesaje(
                ganadoId: (int) $request->input('ganado_id'),
                peso: (float) $request->input('peso'),
                fecha: $request->input('fecha_pesaje'),
                bascula: $request->input('bascula'),
                lote: $request->input('lote_corral'),
                observaciones: $request->input('observaciones'),
                userId: Auth::id()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pesaje registrado exitosamente.',
                    'pesaje'  => $pesaje,
                ]);
            }

            return back()->with('success', "Pesaje de {$pesaje->peso} kg registrado con éxito.");
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Devuelve el historial de pesajes en JSON para gráficas de evolución de peso.
     */
    public function historial(int $ganadoId): JsonResponse
    {
        $ganado = Ganado::with(['pesajes' => fn($q) => $q->orderBy('fecha_pesaje', 'asc')])->findOrFail($ganadoId);

        return response()->json([
            'arete'   => $ganado->areteID,
            'pesajes' => $ganado->pesajes,
        ]);
    }
}
