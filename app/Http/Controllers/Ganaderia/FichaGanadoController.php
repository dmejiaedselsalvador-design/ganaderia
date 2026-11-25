<?php

namespace App\Http\Controllers\Ganaderia;

use App\Http\Controllers\Controller;
use App\Models\Engorda\CorralLote;
use App\Models\Ganaderia\GanadoFoto;
use App\Models\Ganaderia\MovimientoCorral;
use App\Models\Ganado\Ganado;
use App\Services\Ganaderia\RentabilidadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Ficha completa del animal: identificación oficial, origen, historial de corrales,
 * fotos, enfermedades, documentos y rentabilidad individual.
 */
class FichaGanadoController extends Controller
{
    public function show(int $id, RentabilidadService $rentabilidad): View
    {
        $ganado = Ganado::with([
            'factura.proveedor', 'corralLote', 'pesajes', 'tratamientos.insumo', 'baja', 'detalleVenta.venta',
            'movimientosCorral.origen', 'movimientosCorral.destino', 'movimientosCorral.usuario', 'fotos', 'eventosEnfermedad',
        ])->findOrFail($id);

        return view('ganaderia.ficha.show', [
            'g'           => $ganado,
            'r'           => $rentabilidad->deAnimal($ganado),
            'corrales'    => CorralLote::where('estado', '!=', 'Cerrado')->orderBy('codigo')->get(),
            'merma'       => $ganado->mermaViaje(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $ganado = Ganado::findOrFail($id);

        $data = $request->validate([
            'arete_siniiga'    => ['nullable', 'string', 'max:30', Rule::unique('ganado', 'arete_siniiga')->ignore($ganado->GanadoID, 'GanadoID')],
            'fierro'           => ['nullable', 'string', 'max:80'],
            'color_senas'      => ['nullable', 'string', 'max:255'],
            'raza'             => ['nullable', 'string', 'max:255'],
            'procedencia'      => ['nullable', 'string', 'max:150'],
            'upp_origen'       => ['nullable', 'string', 'max:40'],
            'denticion'        => ['nullable', Rule::in(Ganado::DENTICIONES)],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'peso_origen'      => ['nullable', 'numeric', 'min:1', 'max:2000'],
            'peso_objetivo'    => ['nullable', 'numeric', 'min:1', 'max:2000'],
            'notas'            => ['nullable', 'string', 'max:1000'],
        ], ['arete_siniiga.unique' => 'Ese arete SINIIGA ya está asignado a otro animal.']);

        $ganado->update($data);

        return back()->with('success', 'Datos del animal actualizados.');
    }

    public function mover(Request $request, int $id): RedirectResponse
    {
        $ganado = Ganado::findOrFail($id);
        abort_unless($ganado->puedeOperar(), 422, 'El animal ya no está activo.');

        $data = $request->validate([
            'corral_lote_id' => ['nullable', 'exists:corrales_lotes,id'],
            'fecha'          => ['required', 'date', 'before_or_equal:today'],
            'motivo'         => ['nullable', 'string', 'max:150'],
        ]);

        $destino = $data['corral_lote_id'] ? CorralLote::find($data['corral_lote_id']) : null;
        if ($destino?->id === $ganado->corral_lote_id) {
            return back()->withErrors(['corral_lote_id' => 'El animal ya está en ese corral.']);
        }

        MovimientoCorral::mover($ganado, $destino, $data['motivo'] ?? 'Cambio de corral', $data['fecha']);

        return back()->with('success', $destino ? "Animal movido a {$destino->codigo}." : 'Animal retirado del corral.');
    }

    public function subirFoto(Request $request, int $id): RedirectResponse
    {
        $ganado = Ganado::findOrFail($id);
        $request->validate([
            'foto'        => ['required', 'image', 'max:8192'],
            'descripcion' => ['nullable', 'string', 'max:150'],
        ], ['foto.image' => 'El archivo debe ser una imagen.', 'foto.max' => 'La foto no debe pasar de 8 MB.']);

        $ruta = $request->file('foto')->store('ganado/' . $ganado->GanadoID, 'local');
        GanadoFoto::create(['ganado_id' => $ganado->GanadoID, 'ruta' => $ruta, 'descripcion' => $request->descripcion, 'user_id' => auth()->id()]);

        return back()->with('success', 'Foto agregada.');
    }

    public function verFoto(GanadoFoto $foto): StreamedResponse
    {
        return Storage::disk('local')->response($foto->ruta);
    }

    public function eliminarFoto(GanadoFoto $foto): RedirectResponse
    {
        Storage::disk('local')->delete($foto->ruta);
        $foto->delete();

        return back()->with('success', 'Foto eliminada.');
    }
}
