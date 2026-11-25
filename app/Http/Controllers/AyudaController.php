<?php

namespace App\Http\Controllers;

use App\Models\PreguntaAyuda;
use App\Support\Ayuda;
use App\Support\Plantas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Botón de Ayuda (?): cualquier usuario puede enviar una pregunta sobre la pantalla
 * donde está, con captura de pantalla. Un administrador la responde aquí mismo.
 */
class AyudaController extends Controller
{
    /** Peso máximo de la captura (la imagen llega en base64 desde el navegador). */
    private const MAX_CAPTURA_BYTES = 3 * 1024 * 1024;

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pregunta' => ['required', 'string', 'min:5', 'max:2000'],
            'ruta'     => ['nullable', 'string', 'max:120'],
            'url'      => ['nullable', 'string', 'max:500'],
            'captura'  => ['nullable', 'string', 'max:' . (int) (self::MAX_CAPTURA_BYTES * 1.4)],
        ], [
            'pregunta.required' => 'Escribe tu pregunta.',
            'pregunta.min'      => 'Escribe un poco más para que podamos ayudarte.',
            'captura.max'       => 'La captura de pantalla es demasiado grande.',
        ]);

        $ruta = $data['ruta'] && Route::has($data['ruta']) ? $data['ruta'] : null;

        $pregunta = PreguntaAyuda::create([
            'user_id'      => $request->user()->id,
            'planta'       => Plantas::activa(),
            'ruta'         => $ruta,
            'pantalla'     => Ayuda::para($ruta)['titulo'],
            'url'          => $this->urlInterna($data['url'] ?? null),
            'pregunta'     => trim($data['pregunta']),
            'captura_path' => $this->guardarCaptura($data['captura'] ?? null),
            'navegador'    => Str::limit((string) $request->userAgent(), 250, ''),
            'estado'       => PreguntaAyuda::PENDIENTE,
        ]);

        return response()->json([
            'id'      => $pregunta->id,
            'mensaje' => 'Tu pregunta se envió. Te avisaremos aquí mismo cuando tenga respuesta.',
        ], 201);
    }

    /** El usuario abrió el panel: sus respuestas nuevas quedan como leídas. */
    public function marcarVistas(Request $request): JsonResponse
    {
        PreguntaAyuda::respuestasNuevas($request->user()->id)->update(['respuesta_vista_at' => now()]);

        return response()->json(['ok' => true]);
    }

    /** Bandeja de preguntas para quien responde. */
    public function index(Request $request): View
    {
        $estado = $request->input('estado', PreguntaAyuda::PENDIENTE);

        $preguntas = PreguntaAyuda::with(['usuario', 'respondidaPor'])
            ->when($estado !== 'todas', fn ($q) => $q->where('estado', $estado))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $conteo = PreguntaAyuda::selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado');

        return view('admin.ayuda.index', compact('preguntas', 'estado', 'conteo'));
    }

    public function responder(Request $request, PreguntaAyuda $pregunta): RedirectResponse
    {
        $data = $request->validate([
            'respuesta' => ['required', 'string', 'max:4000'],
            'estado'    => ['nullable', Rule::in([PreguntaAyuda::RESPONDIDA, PreguntaAyuda::CERRADA])],
        ], ['respuesta.required' => 'Escribe la respuesta.']);

        $pregunta->update([
            'respuesta'          => trim($data['respuesta']),
            'estado'             => $data['estado'] ?? PreguntaAyuda::RESPONDIDA,
            'respondida_por'     => $request->user()->id,
            'respondida_at'      => now(),
            'respuesta_vista_at' => null, // el usuario verá el aviso de respuesta nueva
        ]);

        return back()->with('exito', "Respuesta enviada a {$pregunta->usuario->name}.");
    }

    public function cerrar(PreguntaAyuda $pregunta): RedirectResponse
    {
        $pregunta->update(['estado' => PreguntaAyuda::CERRADA]);

        return back()->with('exito', 'Pregunta cerrada.');
    }

    /** Muestra la captura: solo a quien envió la pregunta o a quien responde. */
    public function captura(Request $request, PreguntaAyuda $pregunta): StreamedResponse
    {
        abort_unless($pregunta->user_id === $request->user()->id || $request->user()->can('ayuda.responder'), 403);
        abort_unless($pregunta->captura_path && Storage::disk('local')->exists($pregunta->captura_path), 404);

        return Storage::disk('local')->response($pregunta->captura_path, "captura-{$pregunta->id}.jpg", [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /** Decodifica la imagen base64 (JPEG o PNG), verifica que sea imagen real y la guarda en privado. */
    private function guardarCaptura(?string $dataUrl): ?string
    {
        if (! $dataUrl || ! preg_match('#^data:image/(jpeg|png);base64,(.+)$#s', $dataUrl, $m)) {
            return null;
        }

        $binario = base64_decode($m[2], true);
        if ($binario === false || strlen($binario) > self::MAX_CAPTURA_BYTES) {
            return null;
        }

        $info = @getimagesizefromstring($binario);
        if (! $info || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
            return null;
        }

        $ruta = 'ayuda/' . now()->format('Y/m') . '/' . Str::uuid() . ($info[2] === IMAGETYPE_PNG ? '.png' : '.jpg');
        Storage::disk('local')->put($ruta, $binario);

        return $ruta;
    }

    private function urlInterna(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        $host = parse_url($url, PHP_URL_HOST);

        return ! $host || $host === request()->getHost() ? Str::limit($url, 500, '') : null;
    }
}
