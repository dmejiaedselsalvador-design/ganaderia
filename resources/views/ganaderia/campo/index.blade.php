<x-app-layout>
    <div class="mx-auto max-w-xl space-y-4" x-data x-init="$refs.arete && $refs.arete.focus()">
        <div class="rounded-2xl bg-slate-900 p-5 text-white shadow-lg">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-300">Modo campo</p>
            <form method="GET" action="{{ route('campo.index') }}" class="mt-3 flex gap-2">
                <input x-ref="arete" name="arete" value="{{ $arete }}" inputmode="text" autocomplete="off" placeholder="Escanea o escribe el arete"
                       class="flex-1 rounded-xl border-0 bg-white/10 px-4 py-4 font-mono text-xl text-white placeholder-white/40 focus:ring-2 focus:ring-emerald-400">
                <button class="rounded-xl bg-emerald-500 px-5 text-lg font-bold"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
            <p class="mt-2 text-[11px] text-white/50">Funciona con lectores de arete RFID o de código de barras que escriben como teclado.</p>
        </div>
        <x-ui.alertas />

        @if ($arete !== '' && ! $g)
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 text-center text-rose-800"><i class="fa-solid fa-circle-xmark text-2xl"></i><p class="mt-2 font-semibold">No se encontró el arete {{ $arete }}</p></div>
        @elseif ($g)
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-mono text-2xl font-black text-slate-900">{{ $g->areteID }}</p>
                        <p class="text-sm text-slate-500">{{ $g->categoria }} · {{ $g->raza }} · Corral {{ $g->corralLote->codigo ?? '—' }}</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $g->puedeOperar() ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $g->status }}</span>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-[10px] uppercase text-slate-400">Último peso</p><p class="font-mono text-lg font-bold">{{ number_format($g->ultimoPeso, 0) }}</p></div>
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-[10px] uppercase text-slate-400">Meta</p><p class="font-mono text-lg font-bold">{{ number_format($g->peso_objetivo_calculado, 0) }}</p></div>
                    <div class="rounded-xl {{ $g->esta_en_retiro ? 'bg-rose-50' : 'bg-slate-50' }} p-3"><p class="text-[10px] uppercase text-slate-400">Retiro</p><p class="text-sm font-bold {{ $g->esta_en_retiro ? 'text-rose-600' : 'text-emerald-600' }}">{{ $g->esta_en_retiro ? $g->dias_restantes_retiro . ' días' : 'Libre' }}</p></div>
                </div>
            </div>

            @if ($g->puedeOperar())
                <div x-data="{ accion: 'pesaje' }" class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="grid grid-cols-2 gap-1 rounded-xl bg-slate-100 p-1 text-sm font-semibold">
                        <button type="button" @click="accion = 'pesaje'" :class="accion === 'pesaje' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500'" class="rounded-lg py-2.5"><i class="fa-solid fa-weight-scale"></i> Pesar</button>
                        <button type="button" @click="accion = 'tratamiento'" :class="accion === 'tratamiento' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500'" class="rounded-lg py-2.5"><i class="fa-solid fa-syringe"></i> Tratar</button>
                    </div>

                    <form x-show="accion === 'pesaje'" method="POST" action="{{ route('ganado.pesaje.store') }}" class="mt-4 space-y-3">
                        @csrf
                        <input type="hidden" name="ganado_id" value="{{ $g->GanadoID }}">
                        <input type="hidden" name="fecha_pesaje" value="{{ date('Y-m-d') }}">
                        <input type="hidden" name="bascula" value="Modo campo">
                        <input type="hidden" name="lote_corral" value="{{ $g->lote_potrero }}">
                        <label class="block text-xs font-semibold uppercase text-slate-500">Peso (kg)</label>
                        <input type="number" name="peso" step="0.1" min="1" required inputmode="decimal" class="w-full rounded-xl border-slate-200 py-4 text-center font-mono text-3xl font-black">
                        <div class="grid grid-cols-2 gap-3">
                            <input type="number" name="condicion_corporal" step="0.5" min="1" max="9" placeholder="CC (1-9)" class="rounded-xl border-slate-200 py-3 text-center">
                            <label class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 text-sm"><input type="checkbox" name="en_ayuno" value="1" class="rounded"> En ayuno</label>
                        </div>
                        <button class="w-full rounded-xl bg-emerald-600 py-4 text-lg font-bold text-white">Guardar pesaje</button>
                    </form>

                    <form x-show="accion === 'tratamiento'" x-cloak method="POST" action="{{ route('sanidad.store') }}" class="mt-4 space-y-3">
                        @csrf
                        <input type="hidden" name="ganado_id" value="{{ $g->GanadoID }}">
                        <input type="hidden" name="fecha_aplicacion" value="{{ date('Y-m-d') }}">
                        <select name="tipo_evento" class="w-full rounded-xl border-slate-200 py-3">
                            @foreach (\App\Http\Controllers\Ganaderia\SanidadAvanzadaController::TIPOS_EVENTO as $t)<option value="{{ $t }}">{{ str_replace('_', ' ', $t) }}</option>@endforeach
                        </select>
                        <select name="insumo_id" class="w-full rounded-xl border-slate-200 py-3" onchange="const o = this.options[this.selectedIndex]; if (o.value) { this.form.medicamento_producto.value = o.dataset.nombre; this.form.dias_retiro_rastro.value = o.dataset.retiro || 0; }">
                            <option value="">Medicamento del inventario…</option>
                            @foreach ($medicamentos as $m)<option value="{{ $m->id }}" data-nombre="{{ $m->nombre }}" data-retiro="{{ $m->dias_retiro }}">{{ $m->nombre }}</option>@endforeach
                        </select>
                        <input name="medicamento_producto" required placeholder="Producto" class="w-full rounded-xl border-slate-200 py-3">
                        <div class="grid grid-cols-2 gap-3">
                            <input name="dosis_aplicada" required placeholder="Dosis (5 ml)" class="rounded-xl border-slate-200 py-3">
                            <input type="number" step="0.001" name="cantidad_insumo" placeholder="Cantidad a descontar" class="rounded-xl border-slate-200 py-3">
                            <select name="via_administracion" class="rounded-xl border-slate-200 py-3"><option>Subcutánea</option><option>Intramuscular</option><option>Oral</option><option>Tópica / Pour-on</option></select>
                            <input type="number" name="dias_retiro_rastro" value="0" min="0" required placeholder="Días retiro" class="rounded-xl border-slate-200 py-3">
                        </div>
                        <input type="number" step="0.01" name="costo_medicamento_mxn" value="0" min="0" required placeholder="Costo $" class="w-full rounded-xl border-slate-200 py-3">
                        <button class="w-full rounded-xl bg-teal-600 py-4 text-lg font-bold text-white">Guardar tratamiento</button>
                    </form>
                </div>
            @endif

            <a href="{{ route('ganado.ficha', $g->GanadoID) }}" class="block rounded-xl border border-slate-200 bg-white py-3 text-center text-sm font-semibold text-slate-700">Ver ficha completa</a>
        @endif

        <!-- Carga masiva desde báscula -->
        <details class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <summary class="cursor-pointer text-sm font-semibold text-slate-700"><i class="fa-solid fa-file-import"></i> Importar pesajes de la báscula (CSV)</summary>
            <form method="POST" action="{{ route('ganado.pesajes.importar') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                @csrf
                <p class="text-xs text-slate-500">Columnas: <code>arete, peso, fecha, condicion_corporal</code> (la fecha y la CC son opcionales). Acepta comas o punto y coma.</p>
                <input type="file" name="archivo" accept=".csv,.txt" required class="block w-full text-xs">
                <input name="bascula" placeholder="Nombre de la báscula" class="w-full rounded-xl border-slate-200 py-2 text-sm">
                <label class="flex items-center gap-2 text-xs text-slate-600"><input type="checkbox" name="en_ayuno" value="1" class="rounded"> Pesados en ayuno</label>
                <button class="w-full rounded-xl bg-slate-900 py-3 text-sm font-semibold text-white">Importar</button>
            </form>
            @if (session('errores_importacion'))
                <ul class="mt-3 max-h-40 list-inside list-disc overflow-y-auto text-xs text-rose-700">@foreach (session('errores_importacion') as $e)<li>{{ $e }}</li>@endforeach</ul>
            @endif
        </details>
    </div>
</x-app-layout>
