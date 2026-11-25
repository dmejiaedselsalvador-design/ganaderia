<?php

namespace App\Http\Controllers\Ganaderia;

use App\Http\Controllers\Controller;
use App\Models\Engorda\CorralLote;
use App\Models\Ganaderia\PrecioMercado;
use App\Models\Ganaderia\Ubicacion;
use App\Models\Ganado\Ganado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Precios de mercado, ubicaciones (ranchos) y metas/presupuesto por lote.
 */
class ConfiguracionController extends Controller
{
    public function index(): View
    {
        $categorias = array_merge([PrecioMercado::GENERAL], Ganado::CATEGORIAS_MACHOS, Ganado::CATEGORIAS_HEMBRAS);

        return view('ganaderia.configuracion.index', [
            'vigentes'    => PrecioMercado::query()->whereIn('id', PrecioMercado::selectRaw('MAX(id)')->groupBy('categoria'))->orderBy('categoria')->get(),
            'historial'   => PrecioMercado::with('usuario')->latest('fecha')->latest('id')->limit(30)->get(),
            'categorias'  => array_combine($categorias, $categorias),
            'ubicaciones' => Ubicacion::withCount('corrales')->orderBy('nombre')->get(),
            'corrales'    => CorralLote::with('ubicacion')->orderBy('codigo')->get(),
        ]);
    }

    public function storePrecio(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fecha'     => ['required', 'date', 'before_or_equal:today'],
            'categoria' => ['required', 'string', 'max:30'],
            'precio_kg' => ['required', 'numeric', 'min:1', 'max:1000'],
            'fuente'    => ['nullable', 'string', 'max:150'],
            'notas'     => ['nullable', 'string', 'max:500'],
        ]);

        PrecioMercado::create($data + ['user_id' => auth()->id()]);

        return back()->with('success', "Precio de {$data['categoria']} registrado: $ " . number_format($data['precio_kg'], 2) . ' /kg.');
    }

    public function destroyPrecio(PrecioMercado $precio): RedirectResponse
    {
        $precio->delete();

        return back()->with('success', 'Registro de precio eliminado.');
    }

    public function storeUbicacion(Request $request): RedirectResponse
    {
        Ubicacion::create($this->validarUbicacion($request) + ['activo' => true]);

        return back()->with('success', 'Ubicación registrada.');
    }

    public function updateUbicacion(Request $request, Ubicacion $ubicacion): RedirectResponse
    {
        $ubicacion->update($this->validarUbicacion($request) + ['activo' => $request->boolean('activo')]);

        return back()->with('success', 'Ubicación actualizada.');
    }

    public function updateMetas(Request $request, CorralLote $corral): RedirectResponse
    {
        $data = $request->validate([
            'ubicacion_id'        => ['nullable', 'exists:ubicaciones,id'],
            'meta_gdp'            => ['nullable', 'numeric', 'min:0', 'max:5'],
            'meta_costo_kg'       => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'meta_mortalidad_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'meta_conversion'     => ['nullable', 'numeric', 'min:0', 'max:30'],
            'presupuesto_mxn'     => ['nullable', 'numeric', 'min:0'],
        ]);

        $corral->update($data);

        return back()->with('success', "Metas del corral {$corral->codigo} guardadas.");
    }

    private function validarUbicacion(Request $request): array
    {
        return $request->validate([
            'nombre'      => ['required', 'string', 'max:120'],
            'clave_upp'   => ['nullable', 'string', 'max:40'],
            'municipio'   => ['nullable', 'string', 'max:100'],
            'estado'      => ['nullable', 'string', 'max:100'],
            'responsable' => ['nullable', 'string', 'max:120'],
            'direccion'   => ['nullable', 'string', 'max:500'],
        ]);
    }
}
