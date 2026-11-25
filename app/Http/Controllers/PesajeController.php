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
                userId: Auth::id(),
                condicionCorporal: $request->filled('condicion_corporal') ? (float) $request->input('condicion_corporal') : null,
                enAyuno: $request->boolean('en_ayuno')
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

    /**
     * Carga masiva de pesajes desde un archivo CSV exportado de la báscula.
     * Columnas: arete, peso, fecha (AAAA-MM-DD o DD/MM/AAAA) [, condicion_corporal]
     */
    public function importar(\Illuminate\Http\Request $request): RedirectResponse
    {
        $request->validate([
            'archivo' => ['required', 'file', 'max:2048', 'mimes:csv,txt'],
            'bascula' => ['nullable', 'string', 'max:100'],
            'en_ayuno'=> ['nullable', 'boolean'],
        ], ['archivo.mimes' => 'El archivo debe ser CSV (separado por comas o punto y coma).']);

        $lineas = file($request->file('archivo')->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $ok = 0;
        $errores = [];

        foreach ($lineas as $n => $linea) {
            $linea = trim(preg_replace('/^\xEF\xBB\xBF/', '', $linea));
            $col = str_getcsv($linea, str_contains($linea, ';') ? ';' : ',', '"', '\\');
            if (count($col) < 2 || ! is_numeric(str_replace(',', '.', $col[1] ?? ''))) {
                if ($n > 0) {
                    $errores[] = 'Línea ' . ($n + 1) . ': formato no válido.';
                }
                continue; // encabezado u otra línea no numérica
            }

            $arete = trim($col[0]);
            $ganado = \App\Models\Ganado\Ganado::where('areteID', $arete)->orWhere('arete_siniiga', $arete)->first();
            if (! $ganado) {
                $errores[] = "Línea " . ($n + 1) . ": arete {$arete} no encontrado.";
                continue;
            }

            try {
                $fechaTxt = trim($col[2] ?? '');
                $fecha = $fechaTxt === '' ? now()->toDateString()
                    : (str_contains($fechaTxt, '/') ? \Carbon\Carbon::createFromFormat('d/m/Y', $fechaTxt)->toDateString() : \Carbon\Carbon::parse($fechaTxt)->toDateString());

                $this->pesajeService->registrarPesaje(
                    ganadoId: $ganado->GanadoID,
                    peso: (float) str_replace(',', '.', $col[1]),
                    fecha: $fecha,
                    bascula: $request->input('bascula') ?: 'Importación CSV',
                    lote: $ganado->lote_potrero,
                    observaciones: 'Carga masiva',
                    userId: Auth::id(),
                    condicionCorporal: isset($col[3]) && is_numeric($col[3]) ? (float) $col[3] : null,
                    enAyuno: $request->boolean('en_ayuno')
                );
                $ok++;
            } catch (Exception $e) {
                $errores[] = 'Línea ' . ($n + 1) . " ({$arete}): " . $e->getMessage();
            }
        }

        return back()->with('success', "Se importaron {$ok} pesajes.")->with('errores_importacion', $errores);
    }
}
