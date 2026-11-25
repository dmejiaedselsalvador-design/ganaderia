{{-- Lista y carga de documentos adjuntos. Uso: <x-ui.documentos :modelo="$venta" tipo="venta" /> --}}
@props(['modelo', 'tipo'])
@php $docs = $modelo->documentos()->with('usuario')->get(); @endphp
<x-ui.card titulo="Documentos adjuntos" subtitulo="Guías REEMO, certificados, CFDI, comprobantes (PDF o imagen, máx. 10 MB)">
    <div class="space-y-2">
        @forelse ($docs as $d)
            <div class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 text-sm">
                <i class="fa-solid {{ str_contains((string) $d->mime, 'pdf') ? 'fa-file-pdf text-rose-500' : (str_contains((string) $d->mime, 'image') ? 'fa-file-image text-blue-500' : 'fa-file text-slate-400') }} text-lg"></i>
                <div class="min-w-0 flex-1">
                    <a href="{{ route('documentos.descargar', $d) }}" class="block truncate font-semibold text-slate-800 hover:text-emerald-700">{{ $d->tipoLegible() }}{{ $d->folio ? ' · ' . $d->folio : '' }}</a>
                    <p class="truncate text-[11px] text-slate-400">{{ $d->nombre_original }} · {{ $d->tamanoLegible() }} · {{ $d->created_at->format('d/m/Y') }} · {{ $d->usuario->name ?? '' }}</p>
                </div>
                <form method="POST" action="{{ route('documentos.destroy', $d) }}" data-confirm="¿Eliminar el documento {{ $d->nombre_original }}?" data-peligro data-boton="Eliminar">
                    @csrf @method('DELETE')
                    <button class="flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50" title="Eliminar"><i class="fa-solid fa-trash-can"></i></button>
                </form>
            </div>
        @empty
            <p class="py-2 text-xs text-slate-400">Sin documentos.</p>
        @endforelse
    </div>
    <form method="POST" action="{{ route('documentos.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-2">
        @csrf
        <input type="hidden" name="adjuntable" value="{{ $tipo }}">
        <input type="hidden" name="adjuntable_id" value="{{ $modelo->getKey() }}">
        <x-ui.field label="Tipo" name="tipo" type="select" :options="\App\Models\Ganaderia\Documento::TIPOS" required :placeholder="false" />
        <x-ui.field label="Folio" name="folio" placeholder="Opcional" />
        <div class="sm:col-span-2">
            <input type="file" name="archivo" required accept=".pdf,.jpg,.jpeg,.png,.webp,.xml" class="block w-full text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white">
        </div>
        <div class="sm:col-span-2"><x-ui.btn icono="fa-paperclip" variante="oscuro">Adjuntar documento</x-ui.btn></div>
    </form>
</x-ui.card>
