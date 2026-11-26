<?php

namespace App\Http\Controllers\Ganaderia;

use App\Http\Controllers\Controller;
use App\Models\Ganaderia\PesoObjetivo;
use App\Models\Ganado\Ganado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Guarda el peso objetivo de venta por categoría (desde el inventario de ganado). */
class PesoObjetivoController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $categorias = array_merge(Ganado::CATEGORIAS_MACHOS, Ganado::CATEGORIAS_HEMBRAS);
        $reglas = [];
        foreach ($categorias as $c) {
            $reglas["pesos.{$c}"] = ['required', 'numeric', 'min:50', 'max:1500'];
        }
        $data = $request->validate($reglas, [], collect($categorias)->mapWithKeys(fn ($c) => ["pesos.{$c}" => "peso de {$c}"])->all());

        $cambios = [];
        foreach ($categorias as $c) {
            $kg = round((float) $data['pesos'][$c], 2);
            if ((float) (PesoObjetivo::mapa()[$c] ?? 0) !== $kg || ! PesoObjetivo::where('categoria', $c)->exists()) {
                PesoObjetivo::updateOrCreate(['categoria' => $c], ['peso_kg' => $kg, 'user_id' => $request->user()->id]);
                $cambios[] = "{$c} {$kg} kg";
            }
        }
        PesoObjetivo::limpiarCache();

        return back()->with('success', $cambios ? 'Peso objetivo actualizado: ' . implode(', ', $cambios) . '.' : 'Sin cambios en el peso objetivo.');
    }
}
