<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6">
        <x-ui.header titulo="Bitácora de auditoría" subtitulo="Quién creó, modificó o eliminó cada registro, cuándo y qué cambió." seccion="Administración" icono="fa-clipboard-list">
            <x-slot name="acciones"><x-ui.btn :href="request()->fullUrlWithQuery(['exportar' => 'csv'])" variante="secundario" icono="fa-file-excel">Exportar Excel</x-ui.btn></x-slot>
        </x-ui.header>

        <form method="GET" class="grid gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:grid-cols-3 lg:grid-cols-7">
            <x-ui.field label="Usuario" name="usuario" type="select" :options="$usuarios" :value="request('usuario')" placeholder="Todos" />
            <x-ui.field label="Registro" name="modelo" type="select" :options="$modelos" :value="request('modelo')" placeholder="Todos" />
            <x-ui.field label="Acción" name="accion" type="select" :options="['creado' => 'Creado', 'actualizado' => 'Actualizado', 'eliminado' => 'Eliminado', 'restaurado' => 'Restaurado']" :value="request('accion')" placeholder="Todas" />
            <x-ui.field label="Desde" name="desde" type="date" :value="request('desde')" />
            <x-ui.field label="Hasta" name="hasta" type="date" :value="request('hasta')" />
            <x-ui.field label="Buscar" name="q" :value="request('q')" placeholder="Arete, folio, ID..." />
            <div class="flex items-end"><x-ui.btn variante="oscuro" icono="fa-filter" class="w-full">Filtrar</x-ui.btn></div>
        </form>

        <x-ui.card :padding="false">
            <div class="divide-y divide-slate-100">
                @forelse ($registros as $a)
                    <div class="px-5 py-3" x-data="{ ver: false }">
                        <div class="flex flex-wrap items-center gap-3 text-sm">
                            <span class="w-32 shrink-0 font-mono text-xs text-slate-400">{{ $a->created_at->format('d/m/Y H:i') }}</span>
                            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ match($a->accion) { 'creado' => 'bg-emerald-50 text-emerald-700', 'eliminado' => 'bg-rose-50 text-rose-700', default => 'bg-blue-50 text-blue-700' } }}">{{ ucfirst($a->accion) }}</span>
                            <span class="font-semibold text-slate-800">{{ $a->modeloLegible() }}</span>
                            <span class="flex-1 truncate text-slate-600">{{ $a->descripcion }} <span class="text-xs text-slate-400">#{{ $a->modelo_id }}</span></span>
                            <span class="text-xs text-slate-500"><i class="fa-solid fa-user text-[10px]"></i> {{ $a->user->name ?? 'Sistema' }}</span>
                            <button type="button" @click="ver = !ver" class="text-xs font-semibold text-emerald-700">Detalle</button>
                        </div>
                        <div x-show="ver" x-cloak class="mt-3 overflow-x-auto rounded-xl bg-slate-50 p-3">
                            <table class="w-full text-xs">
                                <thead><tr class="text-left text-[10px] uppercase text-slate-400"><th class="py-1 pr-4">Campo</th><th class="pr-4">Antes</th><th>Después</th></tr></thead>
                                <tbody>
                                    @foreach (array_unique(array_merge(array_keys($a->antes ?? []), array_keys($a->despues ?? []))) as $campo)
                                        <tr class="border-t border-slate-200">
                                            <td class="py-1 pr-4 font-mono text-slate-500">{{ $campo }}</td>
                                            <td class="pr-4 text-rose-700">{{ is_scalar($a->antes[$campo] ?? null) ? $a->antes[$campo] : json_encode($a->antes[$campo] ?? null) }}</td>
                                            <td class="text-emerald-700">{{ is_scalar($a->despues[$campo] ?? null) ? $a->despues[$campo] : json_encode($a->despues[$campo] ?? null) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <p class="mt-2 text-[11px] text-slate-400">{{ $a->url }} · IP {{ $a->ip }}</p>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-10 text-center text-sm text-slate-400">Sin registros.</p>
                @endforelse
            </div>
            @if ($registros->hasPages())<div class="border-t border-slate-100 px-4 py-3">{{ $registros->links() }}</div>@endif
        </x-ui.card>
    </div>
</x-app-layout>
