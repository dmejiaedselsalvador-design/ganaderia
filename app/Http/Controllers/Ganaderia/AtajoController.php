<?php

namespace App\Http\Controllers\Ganaderia;

use App\Http\Controllers\Controller;
use App\Models\Engorda\CorralLote;
use App\Models\Ganaderia\Cliente;
use App\Models\Ganaderia\Insumo;
use App\Models\Ganado\ProveedorGanado;
use App\Support\Atajos;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Alta rápida de catálogos desde cualquier formulario (botón "+ Nuevo").
 * Responde JSON con el registro creado para agregarlo y seleccionarlo en la lista.
 */
class AtajoController extends Controller
{
    public function store(Request $request, string $tipo): JsonResponse
    {
        abort_unless(isset(Atajos::TIPOS[$tipo]), 404);
        abort_unless($request->user()->can(Atajos::TIPOS[$tipo]['permiso']), 403, 'No tienes permiso para crear este registro.');

        return match ($tipo) {
            'proveedor' => $this->proveedor($request),
            'cliente'   => $this->cliente($request),
            'insumo'    => $this->insumo($request),
            'corral'    => $this->corral($request),
        };
    }

    private function proveedor(Request $request): JsonResponse
    {
        $d = $request->validate([
            'nombreProveedor' => ['required', 'string', 'max:255'],
            'nombreContacto'  => ['nullable', 'string', 'max:255'],
            'telefono'        => ['nullable', 'string', 'max:50'],
            'lugar'           => ['nullable', 'string', 'max:255'],
        ], [], ['nombreProveedor' => 'nombre del proveedor']);
        $p = ProveedorGanado::create($d);

        return $this->ok($p->id, $p->nombreProveedor . ($p->nombreContacto ? ' · ' . $p->nombreContacto : ''));
    }

    private function cliente(Request $request): JsonResponse
    {
        $request->merge(['rfc' => $request->filled('rfc') ? mb_strtoupper(trim($request->rfc)) : null]);
        $d = $request->validate([
            'nombre'       => ['required', 'string', 'max:150'],
            'rfc'          => ['nullable', 'regex:/^([A-ZÑ&]{3,4})\d{6}([A-Z\d]{3})$/', Rule::unique('clientes', 'rfc')->whereNull('deleted_at')],
            'telefono'     => ['nullable', 'string', 'max:30'],
            'dias_credito' => ['nullable', 'integer', 'min:0', 'max:365'],
        ], ['rfc.regex' => 'El RFC no tiene un formato válido.']);
        $c = Cliente::create($d + ['activo' => true, 'dias_credito' => (int) ($d['dias_credito'] ?? 0), 'limite_credito' => 0]);

        return $this->ok($c->id, $c->nombre . ($c->rfc ? ' · ' . $c->rfc : ''), [
            'nombre' => $c->nombre, 'telefono' => $c->telefono, 'rfc' => $c->rfc, 'dias' => $c->dias_credito,
        ]);
    }

    private function insumo(Request $request): JsonResponse
    {
        $d = $request->validate([
            'nombre'           => ['required', 'string', 'max:150'],
            'tipo'             => ['required', Rule::in(array_keys(Insumo::TIPOS))],
            'unidad'           => ['required', Rule::in(Insumo::UNIDADES)],
            'materia_seca_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'dias_retiro'      => ['nullable', 'integer', 'min:0', 'max:365'],
            'controla_existencia' => ['nullable', 'boolean'],
        ]);
        $d['controla_existencia'] = (bool) ($d['controla_existencia'] ?? true);

        // Código automático a partir del nombre: MAIZ-AMA, MAIZ-AMA-2...
        $base = Str::upper(Str::substr(Str::slug($d['nombre'], '-'), 0, 12)) ?: 'INS';
        $codigo = $base;
        for ($n = 2; Insumo::withTrashed()->where('codigo', $codigo)->exists(); $n++) {
            $codigo = "{$base}-{$n}";
        }

        $i = Insumo::create($d + [
            'codigo' => $codigo, 'activo' => true, 'existencia' => 0, 'costo_promedio' => 0,
            'factor_kg' => Insumo::KG_POR_UNIDAD_PESO[$d['unidad']] ?? 1,
        ]);

        return $this->ok($i->id, "{$i->nombre} ({$i->unidad})", [
            'nombre' => $i->nombre, 'unidad' => $i->unidad, 'retiro' => $i->dias_retiro, 'costo' => 0, 'existencia' => 0,
            'factor' => $i->kgPorUnidad(), 'kgl' => $i->kgPorLitro(), 'controla' => $i->controla_existencia ? 1 : 0,
        ]);
    }

    private function corral(Request $request): JsonResponse
    {
        $request->merge(['codigo' => mb_strtoupper(trim((string) $request->codigo))]);
        $d = $request->validate([
            'codigo'           => ['required', 'string', 'max:50', 'unique:corrales_lotes,codigo'],
            'nombre'           => ['required', 'string', 'max:100'],
            'fase_engorda'     => ['required', Rule::in(['Recepcion', 'Crecimiento', 'Finalizacion'])],
            'capacidad_maxima' => ['required', 'integer', 'min:1', 'max:500'],
        ]);
        $c = CorralLote::create($d + ['estado' => 'Activo', 'peso_promedio_objetivo' => 480, 'fecha_inicio_lote' => today()]);

        return $this->ok($c->id, "{$c->codigo} · {$c->nombre}");
    }

    private function ok(int $id, string $texto, array $data = []): JsonResponse
    {
        return response()->json(['id' => $id, 'texto' => $texto, 'data' => $data], 201);
    }
}
