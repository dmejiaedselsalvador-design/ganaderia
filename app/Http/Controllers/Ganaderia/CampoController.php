<?php

namespace App\Http\Controllers\Ganaderia;

use App\Http\Controllers\Controller;
use App\Models\Ganaderia\Insumo;
use App\Models\Ganado\Ganado;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Modo campo: pantalla simple para celular/tableta en el corral.
 * Compatible con lectores de arete RFID/código de barras que funcionan como teclado.
 */
class CampoController extends Controller
{
    public function index(Request $request): View
    {
        $arete = trim((string) $request->input('arete'));
        $ganado = $arete !== ''
            ? Ganado::with(['corralLote', 'pesajes' => fn ($q) => $q->limit(3), 'tratamientos' => fn ($q) => $q->limit(3)])
                ->where(fn ($q) => $q->where('areteID', $arete)->orWhere('arete_siniiga', $arete))->first()
            : null;

        return view('ganaderia.campo.index', [
            'arete'        => $arete,
            'g'            => $ganado,
            'medicamentos' => Insumo::activos()->where('tipo', 'medicamento')->orderBy('nombre')->get(),
        ]);
    }
}
