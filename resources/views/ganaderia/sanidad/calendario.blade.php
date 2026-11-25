@php
    $tipos = collect(\App\Http\Controllers\Ganaderia\SanidadAvanzadaController::TIPOS_EVENTO)->mapWithKeys(fn ($t) => [$t => str_replace('_', ' ', $t)])->all();
    $corralesOpt = $corrales->mapWithKeys(fn ($c) => [$c->id => "{$c->codigo} · {$c->ganados_activos_count} cab."])->all();
    $medOpt = $medicamentos->mapWithKeys(fn ($m) => [$m->id => $m->nombre])->all();
@endphp
<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6" x-data="{ nuevo: false }">
        <x-ui.header titulo="Calendario sanitario" subtitulo="Vacunaciones, desparasitaciones y refuerzos programados por corral, con aplicación masiva." seccion="Sanidad" icono="fa-calendar-check">
            <x-slot name="acciones">
                <x-ui.btn :href="route('sanidad.enfermedades')" variante="secundario" icono="fa-virus">Enfermedades</x-ui.btn>
                <x-ui.btn type="button" x-on:click="nuevo = !nuevo" icono="fa-plus">Programar</x-ui.btn>
            </x-slot>
        </x-ui.header>
        <x-ui.alertas />

        <x-ui.card titulo="Programar aplicación" x-show="nuevo" x-cloak>
            <form method="POST" action="{{ route('sanidad.programar') }}" class="grid gap-3 sm:grid-cols-4">
                @csrf
                <x-ui.field label="Título" name="titulo" required colspan="sm:col-span-2" placeholder="Refuerzo vacuna respiratoria" />
                <x-ui.field label="Tipo" name="tipo_evento" type="select" :options="$tipos" required :placeholder="false" />
                <x-ui.field label="Fecha" name="fecha_programada" type="date" :value="date('Y-m-d')" required />
                <x-ui.field label="Corral" name="corral_lote_id" type="select" :options="$corralesOpt" required />
                <x-ui.field label="Medicamento (inventario)" name="insumo_id" type="select" :options="$medOpt" placeholder="—" />
                <x-ui.field label="Dosis" name="dosis" placeholder="5 ml" />
                <x-ui.field label="Vía" name="via_administracion" placeholder="Subcutánea" />
                <x-ui.field label="Repetir cada (días)" name="repetir_dias" type="number" ayuda="Opcional: p. ej. refuerzo a los 21 días" />
                <x-ui.field label="Número de aplicaciones" name="repeticiones" type="number" value="1" />
                <x-ui.field label="Notas" name="notas" colspan="sm:col-span-2" />
                <div class="sm:col-span-4"><x-ui.btn icono="fa-calendar-plus">Programar</x-ui.btn></div>
            </form>
        </x-ui.card>

        @if ($vencidas->isNotEmpty() || $proximas->isNotEmpty())
            <div class="grid gap-4 md:grid-cols-2">
                @if ($vencidas->isNotEmpty())
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4">
                        <p class="text-sm font-bold text-rose-800"><i class="fa-solid fa-triangle-exclamation"></i> {{ $vencidas->count() }} aplicaciones atrasadas</p>
                        <ul class="mt-2 space-y-1 text-xs text-rose-700">@foreach ($vencidas->take(6) as $p)<li>{{ $p->fecha_programada->format('d/m') }} · {{ $p->titulo }} · {{ $p->corral->codigo ?? '' }}</li>@endforeach</ul>
                    </div>
                @endif
                @if ($proximas->isNotEmpty())
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                        <p class="text-sm font-bold text-amber-800"><i class="fa-solid fa-bell"></i> Próximos 7 días</p>
                        <ul class="mt-2 space-y-1 text-xs text-amber-800">@foreach ($proximas as $p)<li>{{ $p->fecha_programada->format('d/m') }} · {{ $p->titulo }} · {{ $p->corral->codigo ?? '' }}</li>@endforeach</ul>
                    </div>
                @endif
            </div>
        @endif

        <div class="flex items-center justify-between">
            <a href="{{ route('sanidad.calendario', ['mes' => $mes->copy()->subMonth()->format('Y-m')]) }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 hover:bg-slate-50"><i class="fa-solid fa-chevron-left"></i></a>
            <h2 class="text-lg font-bold capitalize text-slate-800">{{ $mes->translatedFormat('F Y') }}</h2>
            <a href="{{ route('sanidad.calendario', ['mes' => $mes->copy()->addMonth()->format('Y-m')]) }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 hover:bg-slate-50"><i class="fa-solid fa-chevron-right"></i></a>
        </div>

        <div class="space-y-3">
            @forelse ($programadas as $p)
                <div class="rounded-2xl border bg-white p-4 shadow-sm {{ $p->vencida() ? 'border-rose-200' : 'border-slate-100' }}" x-data="{ aplicar: false }">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex h-12 w-12 flex-col items-center justify-center rounded-xl {{ $p->estado === 'aplicada' ? 'bg-emerald-50 text-emerald-700' : ($p->estado === 'cancelada' ? 'bg-slate-100 text-slate-400' : ($p->vencida() ? 'bg-rose-50 text-rose-700' : 'bg-blue-50 text-blue-700')) }}">
                            <span class="text-lg font-black leading-none">{{ $p->fecha_programada->format('d') }}</span>
                            <span class="text-[10px] uppercase">{{ $p->fecha_programada->translatedFormat('M') }}</span>
                        </div>
                        <div class="min-w-[12rem] flex-1">
                            <p class="font-semibold text-slate-800">{{ $p->titulo }}</p>
                            <p class="text-xs text-slate-500">{{ str_replace('_', ' ', $p->tipo_evento) }} · Corral {{ $p->corral->codigo ?? '—' }}{{ $p->insumo ? ' · ' . $p->insumo->nombre : '' }}{{ $p->dosis ? ' · ' . $p->dosis : '' }}</p>
                        </div>
                        @if ($p->estado === 'pendiente')
                            <button type="button" @click="aplicar = !aplicar" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"><i class="fa-solid fa-syringe"></i> Aplicar al corral</button>
                            <form method="POST" action="{{ route('sanidad.cancelar', $p) }}" data-confirm="¿Cancelar esta aplicación programada?" data-peligro data-boton="Sí, cancelar">@csrf<button class="text-xs text-slate-400 hover:text-rose-600">Cancelar</button></form>
                        @else
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $p->estado === 'aplicada' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $p->estado === 'aplicada' ? "Aplicada a {$p->cabezas_aplicadas} cab. · " . $p->aplicada_at?->format('d/m/Y') : 'Cancelada' }}
                            </span>
                        @endif
                    </div>
                    @if ($p->estado === 'pendiente')
                        <form x-show="aplicar" x-cloak method="POST" action="{{ route('sanidad.aplicar', $p) }}" class="mt-4 grid gap-3 rounded-xl border border-emerald-100 bg-emerald-50/40 p-4 sm:grid-cols-4">
                            @csrf
                            <x-ui.field label="Fecha" name="fecha_aplicacion" type="date" :value="date('Y-m-d')" required />
                            <x-ui.field label="Producto" name="medicamento_producto" :value="$p->insumo->nombre ?? ''" required />
                            <x-ui.field label="Dosis" name="dosis_aplicada" :value="$p->dosis" required />
                            <x-ui.field label="Vía" name="via_administracion" :value="$p->via_administracion ?: 'Subcutánea'" required />
                            <x-ui.field label="Cantidad por cabeza ({{ $p->insumo->unidad ?? 'unidad' }})" name="cantidad_por_cabeza" type="number" step="0.001" ayuda="Se descuenta del inventario" />
                            <x-ui.field label="Costo por cabeza ($)" name="costo_por_cabeza" type="number" step="0.01" ayuda="Vacío = cantidad x costo promedio" />
                            <x-ui.field label="Días de retiro" name="dias_retiro_rastro" type="number" :value="$p->insumo->dias_retiro ?? 0" required />
                            <x-ui.field label="Lote del medicamento" name="lote_medicamento" />
                            <x-ui.field label="Caducidad" name="caducidad_medicamento" type="date" />
                            <x-ui.field label="Veterinario" name="veterinario_responsable" colspan="sm:col-span-2" />
                            <div class="flex items-end"><x-ui.btn icono="fa-check-double">Aplicar a todo el corral</x-ui.btn></div>
                        </form>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-400">No hay aplicaciones programadas este mes.</div>
            @endforelse
        </div>
    </div>
</x-app-layout>
