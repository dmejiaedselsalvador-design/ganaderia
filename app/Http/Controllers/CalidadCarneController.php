<?php

namespace App\Http\Controllers;

use App\Models\Calidad\EvaluacionCanal;
use App\Models\Ganado\Ganado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CalidadCarneController extends Controller
{
    /**
     * Módulo de Calidad de Carne Bovina y Rendimiento en Canal
     * Basado en la investigación de la Dra. María Salud Rubio Lozano (UNAM / Engormix).
     */
    public function index(): View
    {
        $evaluaciones = EvaluacionCanal::with(['ganado', 'usuario'])
            ->latest('fecha_faena')
            ->latest('id')
            ->paginate(15);

        // Indicadores zootécnicos consolidados
        $totalCanales = EvaluacionCanal::count();
        $rendimientoPromedioPCC = round((float) (EvaluacionCanal::avg('rendimiento_canal_caliente_pct') ?? 0), 2);
        $phPromedio = round((float) (EvaluacionCanal::avg('ph_24h') ?? 0), 2);
        $canalesOptimasCount = EvaluacionCanal::whereBetween('ph_24h', [5.40, 5.75])->count();
        $canalesDfdCount = EvaluacionCanal::where('ph_24h', '>', 5.80)->count();

        $valorTotalCanalesMXN = (float) EvaluacionCanal::sum('valor_total_canal_mxn');

        // Ganado apto para registro de canal (vendidos o listos para faena)
        $ganadoDisponible = Ganado::with('ultimoPesaje')
            ->where(function ($q) {
                $q->where('status', Ganado::STATUS_VENDIDO)
                  ->orWhere('status', Ganado::STATUS_ACTIVO);
            })
            ->latest('GanadoID')
            ->take(50)
            ->get();

        return view('calidad_carne.index', compact(
            'evaluaciones',
            'totalCanales',
            'rendimientoPromedioPCC',
            'phPromedio',
            'canalesOptimasCount',
            'canalesDfdCount',
            'valorTotalCanalesMXN',
            'ganadoDisponible'
        ));
    }

    /**
     * Registrar una nueva evaluación de canal post-sacrificio.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ganado_id'                => 'nullable|exists:ganado,GanadoID',
            'arete_snapshot'           => 'required|string|max:50',
            'folio_rastro_canal'       => 'required|string|max:50|unique:evaluaciones_canal,folio_rastro_canal',
            'fecha_faena'              => 'required|date',
            'planta_o_rastro'          => 'nullable|string|max:150',
            'peso_vivo_bascula_kg'     => 'required|numeric|min:200|max:1000',
            'peso_canal_caliente_kg'   => 'required|numeric|min:100|max:700|lte:peso_vivo_bascula_kg',
            'merma_enfriamiento_pct'   => 'nullable|numeric|min:0|max:10',
            'marmoleo'                 => 'required|in:Abundante,Moderado,Pequeno,Ligero,Practicamente_Carente',
            'espesor_grasa_dorsal_mm'  => 'required|numeric|min:1|max:30',
            'area_ojo_costilla_cm2'    => 'nullable|numeric|min:40|max:150',
            'color_carne'              => 'required|in:Rojo_Cereza_Brillante,Rojo_Medio,Rojo_Oscuro_DFD',
            'color_grasa'              => 'required|in:Blanco_Nieve,Blanco_Cremoso,Ligeramente_Amarillo,Amarillo',
            'ph_24h'                   => 'required|numeric|min:4.8|max:7.2',
            'clasificacion_mexicana'   => 'required|in:Suprema,Selecta,Estandar,Comercial',
            'precio_canal_por_kg_mxn'  => 'required|numeric|min:10|max:300',
            'destino_comercial'        => 'nullable|string|max:150',
            'observaciones_tecnicas'   => 'nullable|string|max:1000',
        ]);

        $pesoVivo = (float) $validated['peso_vivo_bascula_kg'];
        $pcc = (float) $validated['peso_canal_caliente_kg'];
        $rendimientoPCC = round(($pcc / $pesoVivo) * 100, 2);

        $mermaPct = (float) ($validated['merma_enfriamiento_pct'] ?? 2.0);
        $pcf = round($pcc * (1 - ($mermaPct / 100)), 2);
        $rendimientoPCF = round(($pcf / $pesoVivo) * 100, 2);

        $precioKg = (float) $validated['precio_canal_por_kg_mxn'];
        $valorTotalMXN = round($pcc * $precioKg, 2);

        EvaluacionCanal::create([
            'ganado_id'                      => $validated['ganado_id'] ?? null,
            'arete_snapshot'                 => $validated['arete_snapshot'],
            'folio_rastro_canal'             => $validated['folio_rastro_canal'],
            'fecha_faena'                    => $validated['fecha_faena'],
            'planta_o_rastro'                => $validated['planta_o_rastro'] ?? null,
            'peso_vivo_bascula_kg'           => $pesoVivo,
            'peso_canal_caliente_kg'         => $pcc,
            'rendimiento_canal_caliente_pct' => $rendimientoPCC,
            'peso_canal_fria_kg'             => $pcf,
            'merma_enfriamiento_pct'         => $mermaPct,
            'rendimiento_canal_fria_pct'     => $rendimientoPCF,
            'marmoleo'                       => $validated['marmoleo'],
            'espesor_grasa_dorsal_mm'        => $validated['espesor_grasa_dorsal_mm'],
            'area_ojo_costilla_cm2'          => $validated['area_ojo_costilla_cm2'] ?? null,
            'color_carne'                    => $validated['color_carne'],
            'color_grasa'                    => $validated['color_grasa'],
            'ph_24h'                         => $validated['ph_24h'],
            'clasificacion_mexicana'         => $validated['clasificacion_mexicana'],
            'precio_canal_por_kg_mxn'        => $precioKg,
            'valor_total_canal_mxn'          => $valorTotalMXN,
            'destino_comercial'              => $validated['destino_comercial'] ?? null,
            'observaciones_tecnicas'         => $validated['observaciones_tecnicas'] ?? null,
            'user_id'                        => Auth::id(),
        ]);

        return redirect()->route('calidad.carne.index')
            ->with('success', "Evaluación de canal {$validated['folio_rastro_canal']} guardada. Rendimiento: {$rendimientoPCC}% (" . formatoPesos($valorTotalMXN) . ").");
    }
}
