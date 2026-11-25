<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\User;
use App\Support\Csv;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = Auditoria::with('user')
            ->when($request->filled('usuario'), fn ($q) => $q->where('user_id', $request->usuario))
            ->when($request->filled('modelo'), fn ($q) => $q->where('modelo', $request->modelo))
            ->when($request->filled('accion'), fn ($q) => $q->where('accion', $request->accion))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('created_at', '>=', $request->desde))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('created_at', '<=', $request->hasta))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('descripcion', 'like', "%{$request->q}%")->orWhere('modelo_id', $request->q)))
            ->latest('id');

        if ($request->input('exportar') === 'csv') {
            return Csv::descargar('bitacora_auditoria', ['Fecha', 'Usuario', 'Acción', 'Registro', 'ID', 'Descripción', 'Antes', 'Después', 'IP'],
                $query->limit(20000)->get()->map(fn ($a) => [$a->created_at->format('Y-m-d H:i:s'), $a->user->name ?? 'Sistema', $a->accion, $a->modeloLegible(),
                    $a->modelo_id, $a->descripcion, json_encode($a->antes, JSON_UNESCAPED_UNICODE), json_encode($a->despues, JSON_UNESCAPED_UNICODE), $a->ip]));
        }

        return view('admin.auditoria.index', [
            'registros' => $query->paginate(40)->withQueryString(),
            'usuarios'  => User::orderBy('name')->pluck('name', 'id'),
            'modelos'   => Auditoria::select('modelo')->distinct()->orderBy('modelo')->pluck('modelo')
                ->mapWithKeys(fn ($m) => [$m => Auditoria::MODELOS[$m] ?? $m]),
        ]);
    }
}
