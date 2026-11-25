<?php

namespace App\Http\Controllers\Ganaderia;

use App\Http\Controllers\Controller;
use App\Models\Ganaderia\Documento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentoController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'adjuntable'    => ['required', Rule::in(array_keys(Documento::ADJUNTABLES))],
            'adjuntable_id' => ['required'],
            'tipo'          => ['required', Rule::in(array_keys(Documento::TIPOS))],
            'folio'         => ['nullable', 'string', 'max:100'],
            'archivo'       => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,xml'],
        ], ['archivo.mimes' => 'Solo se permiten PDF, imágenes (JPG/PNG/WEBP) o XML.', 'archivo.max' => 'El archivo no debe pasar de 10 MB.']);

        $clase = Documento::ADJUNTABLES[$data['adjuntable']];
        $modelo = $clase::findOrFail($data['adjuntable_id']);

        $archivo = $request->file('archivo');
        $ruta = $archivo->store('documentos/' . $data['adjuntable'] . '/' . now()->format('Y/m'), Documento::DISCO);

        $modelo->documentos()->create([
            'tipo'            => $data['tipo'],
            'folio'           => $data['folio'] ?? null,
            'fecha'           => now()->toDateString(),
            'ruta'            => $ruta,
            'nombre_original' => mb_substr($archivo->getClientOriginalName(), 0, 250),
            'mime'            => $archivo->getMimeType(),
            'tamano'          => $archivo->getSize(),
            'user_id'         => auth()->id(),
        ]);

        return back()->with('success', 'Documento adjuntado.');
    }

    public function descargar(Documento $documento): StreamedResponse
    {
        abort_unless(Storage::disk(Documento::DISCO)->exists($documento->ruta), 404, 'El archivo ya no existe.');

        return Storage::disk(Documento::DISCO)->response($documento->ruta, $documento->nombre_original);
    }

    public function destroy(Documento $documento): RedirectResponse
    {
        $documento->delete();

        return back()->with('success', 'Documento eliminado.');
    }
}
