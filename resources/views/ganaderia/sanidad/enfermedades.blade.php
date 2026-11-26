@php
    $animOpt = $animales->mapWithKeys(fn ($a) => [$a->GanadoID => "{$a->areteID} · {$a->categoria}"])->all();
    $diag = array_combine(\App\Models\Ganaderia\EventoEnfermedad::DIAGNOSTICOS, \App\Models\Ganaderia\EventoEnfermedad::DIAGNOSTICOS);
    $res = \App\Models\Ganaderia\EventoEnfermedad::RESULTADOS;
@endphp
<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6" x-data="{ nuevo: {{ $errors->any() ? 'true' : 'false' }} }">
        <x-ui.header titulo="Enfermedades y morbilidad" subtitulo="Casos clínicos por animal: diagnóstico, enfermería, recaídas y resultado." seccion="Sanidad" icono="fa-virus">
            <x-slot name="acciones">
                <x-ui.btn :href="route('sanidad.calendario')" variante="secundario" icono="fa-calendar-check">Calendario</x-ui.btn>
                <x-ui.btn type="button" x-on:click="nuevo = !nuevo" icono="fa-plus">Registrar caso</x-ui.btn>
            </x-slot>
        </x-ui.header>
        <x-ui.alertas />

        <x-ui.card titulo="Nuevo caso" x-show="nuevo" x-cloak>
            <form method="POST" action="{{ route('sanidad.casos.store') }}" class="grid gap-3 sm:grid-cols-4">
                @csrf
                <x-ui.field label="Animal" name="ganado_id" type="select" :options="$animOpt" required />
                <x-ui.field label="Fecha de inicio" name="fecha_inicio" type="date" :value="date('Y-m-d')" required />
                <x-ui.field label="Diagnóstico" name="diagnostico" type="select" :options="$diag" required />
                <x-ui.field label="Severidad" name="severidad" type="select" :options="['leve' => 'Leve', 'moderada' => 'Moderada', 'grave' => 'Grave']" required :placeholder="false" />
                <x-ui.field label="Síntomas" name="sintomas" type="textarea" colspan="sm:col-span-3" />
                <label class="flex items-end gap-2 pb-2 text-sm text-slate-600"><input type="checkbox" name="en_enfermeria" value="1" class="rounded border-slate-300 text-emerald-600"> En corral de enfermería</label>
                <div class="sm:col-span-4"><x-ui.btn icono="fa-floppy-disk">Registrar caso</x-ui.btn></div>
            </form>
        </x-ui.card>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
            <x-ui.kpi label="Se enfermaron (90 días)" :valor="$morbilidad . '%'" nota="Animales enfermos / total de ganado" icono="fa-virus" color="bg-rose-50 text-rose-600" />
            <x-ui.kpi label="Casos abiertos" :valor="$abiertos" icono="fa-stethoscope" color="bg-amber-50 text-amber-600" />
            <x-ui.kpi label="En enfermería" :valor="$enEnfermeria" icono="fa-bed-pulse" color="bg-blue-50 text-blue-600" />
            <x-ui.kpi label="Recaídas 90 días" :valor="$recaidas" icono="fa-rotate-left" color="bg-orange-50 text-orange-600" />
            <x-ui.kpi label="Recuperación" :valor="$recuperacion !== null ? $recuperacion . '%' : '—'" nota="De los casos cerrados" icono="fa-heart-pulse" />
        </div>

        <div class="grid gap-6 lg:grid-cols-4">
            <x-ui.card titulo="Diagnósticos (90 días)">
                @forelse ($porDiagnostico as $d => $n)
                    <div class="flex justify-between border-b border-slate-100 py-1.5 text-xs last:border-0"><span class="text-slate-600">{{ $d }}</span><strong class="text-slate-800">{{ $n }}</strong></div>
                @empty
                    <p class="text-xs text-emerald-600"><i class="fa-solid fa-circle-check"></i> Sin casos.</p>
                @endforelse
            </x-ui.card>

            <div class="space-y-3 lg:col-span-3">
                <div class="flex rounded-xl border border-slate-200 bg-white p-1 text-xs font-semibold shadow-sm w-fit">
                    @foreach (['abiertos' => 'Abiertos', 'todos' => 'Todos'] as $k => $t)
                        <a href="{{ route('sanidad.enfermedades', ['ver' => $k]) }}" class="rounded-lg px-3 py-1.5 {{ $ver === $k ? 'bg-slate-900 text-white' : 'text-slate-500' }}">{{ $t }}</a>
                    @endforeach
                </div>
                @forelse ($casos as $c)
                    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm" x-data="{ cerrar: false }">
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex-1">
                                <p class="font-semibold text-slate-800">
                                    <a href="{{ route('ganado.ficha', $c->ganado_id) }}" class="font-mono hover:text-emerald-700">{{ $c->ganado->areteID ?? '' }}</a> · {{ $c->diagnostico }}
                                    @if ($c->es_recaida)<span class="ml-1 rounded-full bg-orange-100 px-2 py-0.5 text-[10px] font-bold text-orange-700">RECAÍDA</span>@endif
                                    @if ($c->en_enfermeria)<span class="ml-1 rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-700">ENFERMERÍA</span>@endif
                                </p>
                                <p class="text-xs text-slate-500">Desde {{ $c->fecha_inicio->format('d/m/Y') }} · {{ $c->diasEnfermo() }} días · {{ ucfirst($c->severidad) }} · Corral {{ $c->ganado->corralLote->codigo ?? '—' }} · {{ $c->tratamientos->count() }} tratamientos</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ match($c->resultado) { 'recuperado' => 'bg-emerald-50 text-emerald-700', 'murio' => 'bg-rose-50 text-rose-700', 'cronico' => 'bg-orange-50 text-orange-700', default => 'bg-amber-50 text-amber-700' } }}">{{ $res[$c->resultado] ?? $c->resultado }}</span>
                            <button type="button" @click="cerrar = !cerrar" class="text-xs font-semibold text-emerald-700">Actualizar</button>
                        </div>
                        <form x-show="cerrar" x-cloak method="POST" action="{{ route('sanidad.casos.update', $c) }}" class="mt-3 grid gap-3 rounded-xl bg-slate-50 p-3 sm:grid-cols-4">
                            @csrf @method('PUT')
                            <x-ui.field label="Resultado" name="resultado" type="select" :options="$res" :value="$c->resultado" :placeholder="false" />
                            <x-ui.field label="Fecha de alta" name="fecha_alta" type="date" :value="date('Y-m-d')" />
                            <x-ui.field label="Notas" name="notas" />
                            <div class="flex items-end"><x-ui.btn variante="oscuro">Guardar</x-ui.btn></div>
                        </form>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-400">No hay casos {{ $ver === 'abiertos' ? 'abiertos' : 'registrados' }}.</div>
                @endforelse
                {{ $casos->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
