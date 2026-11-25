@php
    $den = array_combine(\App\Models\Ganado\Ganado::DENTICIONES, \App\Models\Ganado\Ganado::DENTICIONES);
    $corrOpt = $corrales->mapWithKeys(fn ($c) => [$c->id => "{$c->codigo} · {$c->nombre}"])->all();
    $dineroClass = fn ($v) => $v === null ? 'text-slate-400' : ($v >= 0 ? 'text-emerald-600' : 'text-rose-600');
@endphp
<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6">
        <x-ui.header :titulo="'Arete ' . $g->areteID" :subtitulo="$g->categoria . ' · ' . $g->sexo . ' · ' . ($g->raza ?: 'raza s/d') . ' · ' . $g->status . ($g->corralLote ? ' · Corral ' . $g->corralLote->codigo : '')" seccion="Ficha completa del animal" icono="fa-cow">
            <x-slot name="acciones">
                <x-ui.btn :href="route('compras.ganado.perfil', ['arete' => $g->areteID])" variante="secundario" icono="fa-weight-scale">Pesajes y acciones</x-ui.btn>
            </x-slot>
        </x-ui.header>
        <x-ui.alertas />

        <!-- Rentabilidad del animal -->
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
            <x-ui.kpi label="Costo de compra" :valor="formatoPesos($r['compra'])" :nota="'Incluye flete/comisión: ' . formatoPesos($g->costo_adicional_compra)" icono="fa-truck" />
            <x-ui.kpi label="Alimento" :valor="formatoPesos($r['alimento'])" icono="fa-wheat-awn" color="bg-amber-50 text-amber-600" />
            <x-ui.kpi label="Medicinas" :valor="formatoPesos($r['medicina'])" icono="fa-syringe" color="bg-teal-50 text-teal-600" />
            <x-ui.kpi label="Gastos prorrateados" :valor="formatoPesos($r['gasto_corral'] + $r['gasto_general'])" icono="fa-receipt" color="bg-slate-100 text-slate-600" />
            <x-ui.kpi label="Costo total" :valor="formatoPesos($r['costo_total'])" :nota="$r['dias'] . ' días · ' . number_format($r['kg_ganados'], 1) . ' kg ganados'" icono="fa-sack-dollar" color="bg-rose-50 text-rose-600" />
            <x-ui.kpi :label="$r['tipo_ingreso'] === 'venta' ? 'Utilidad real' : ($r['tipo_ingreso'] === 'baja' ? 'Pérdida' : 'Utilidad proyectada')"
                      :valor="$r['utilidad'] !== null ? formatoPesos($r['utilidad']) : 'Sin precio'" :valorClass="$dineroClass($r['utilidad'])"
                      :nota="$r['ingreso'] !== null ? ($r['tipo_ingreso'] === 'venta' ? 'Venta ' : ($r['tipo_ingreso'] === 'baja' ? 'Recuperado ' : 'Valor mercado ')) . formatoPesos($r['ingreso']) : 'Registra precios de mercado'"
                      icono="fa-chart-line" color="bg-emerald-50 text-emerald-600" />
        </div>
        @if ($r['costo_kg'])
            <p class="-mt-2 text-xs text-slate-500">Costo de producción por kg ganado: <strong class="font-mono text-slate-700">$ {{ number_format($r['costo_kg'], 2) }}</strong>{{ $r['margen_pct'] !== null ? ' · margen ' . $r['margen_pct'] . '%' : '' }}</p>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Datos ampliados -->
            <x-ui.card titulo="Identificación y origen" class="lg:col-span-2">
                <form method="POST" action="{{ route('ganado.ficha.update', $g->GanadoID) }}" class="grid gap-3 sm:grid-cols-3">
                    @csrf @method('PUT')
                    <x-ui.field label="Arete SINIIGA" name="arete_siniiga" :value="$g->arete_siniiga" class="font-mono" />
                    <x-ui.field label="Fierro / marca" name="fierro" :value="$g->fierro" />
                    <x-ui.field label="Raza" name="raza" :value="$g->raza" />
                    <x-ui.field label="Color y señas" name="color_senas" :value="$g->color_senas" colspan="sm:col-span-2" />
                    <x-ui.field label="Dentición" name="denticion" type="select" :options="$den" :value="$g->denticion" />
                    <x-ui.field label="Procedencia (rancho/municipio)" name="procedencia" :value="$g->procedencia ?? $g->factura?->proveedor?->lugar" />
                    <x-ui.field label="UPP de origen" name="upp_origen" :value="$g->upp_origen" />
                    <x-ui.field label="Fecha de nacimiento" name="fecha_nacimiento" type="date" :value="$g->fecha_nacimiento?->format('Y-m-d')" />
                    <x-ui.field label="Peso en origen (kg)" name="peso_origen" type="number" step="0.01" :value="$g->peso_origen"
                                :ayuda="$merma ? 'Merma de viaje: ' . $merma['kg'] . ' kg (' . $merma['pct'] . '%)' : 'Báscula del vendedor, para calcular el destare'" />
                    <x-ui.field label="Peso objetivo (kg)" name="peso_objetivo" type="number" step="0.01" :value="$g->peso_objetivo" />
                    <x-ui.field label="Notas" name="notas" :value="$g->notas" />
                    <div class="sm:col-span-3"><x-ui.btn icono="fa-floppy-disk">Guardar datos</x-ui.btn></div>
                </form>
                <div class="mt-4 grid gap-2 border-t border-slate-100 pt-4 text-xs text-slate-500 sm:grid-cols-3">
                    <p>Compra: <strong class="text-slate-700">{{ $g->fechaCompra?->format('d/m/Y') }}</strong> · guía {{ $g->factura->numeroFactura ?? '—' }}</p>
                    <p>Proveedor: <strong class="text-slate-700">{{ $g->factura?->proveedor?->nombreProveedor ?? '—' }}</strong></p>
                    <p>Peso ingreso → actual: <strong class="font-mono text-slate-700">{{ number_format($g->pesoActual, 0) }} → {{ number_format($g->ultimoPeso, 0) }} kg</strong></p>
                </div>
            </x-ui.card>

            <!-- Corral -->
            <x-ui.card titulo="Corral e historial">
                @if ($g->puedeOperar())
                    <form method="POST" action="{{ route('ganado.mover', $g->GanadoID) }}" class="space-y-3">
                        @csrf
                        <x-ui.field label="Mover a corral" name="corral_lote_id" type="select" :options="$corrOpt" :value="$g->corral_lote_id" placeholder="Sin corral" />
                        <x-ui.atajo tipo="corral" target="select[name=corral_lote_id]" texto="+ Crear corral" />
                        <div class="grid grid-cols-2 gap-3">
                            <x-ui.field label="Fecha" name="fecha" type="date" :value="date('Y-m-d')" required />
                            <x-ui.field label="Motivo" name="motivo" placeholder="Cambio de fase" />
                        </div>
                        <x-ui.btn variante="oscuro" icono="fa-right-left" class="w-full">Mover</x-ui.btn>
                    </form>
                @endif
                <ol class="mt-4 space-y-2 border-t border-slate-100 pt-4">
                    @forelse ($g->movimientosCorral as $m)
                        <li class="text-xs text-slate-600">
                            <span class="font-mono text-slate-400">{{ $m->fecha->format('d/m/Y') }}</span>
                            {{ $m->origen->codigo ?? 'Sin corral' }} <i class="fa-solid fa-arrow-right text-[9px] text-slate-400"></i> <strong>{{ $m->destino->codigo ?? 'Sin corral' }}</strong>
                            <span class="block text-[11px] text-slate-400">{{ $m->motivo }} · {{ $m->usuario->name ?? '' }}</span>
                        </li>
                    @empty
                        <li class="text-xs text-slate-400">Sin movimientos registrados{{ $g->corralLote ? '; está en ' . $g->corralLote->codigo . ' desde su ingreso' : '' }}.</li>
                    @endforelse
                </ol>
            </x-ui.card>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Fotos -->
            <x-ui.card titulo="Fotografías">
                <div class="grid grid-cols-3 gap-2">
                    @foreach ($g->fotos as $f)
                        <div class="group relative">
                            <a href="{{ route('ganado.fotos.ver', $f) }}" target="_blank"><img src="{{ route('ganado.fotos.ver', $f) }}" alt="{{ $f->descripcion }}" class="h-24 w-full rounded-lg object-cover"></a>
                            <form method="POST" action="{{ route('ganado.fotos.destroy', $f) }}" class="absolute right-1 top-1 hidden group-hover:block" data-confirm="¿Eliminar esta foto?" data-peligro data-boton="Eliminar">
                                @csrf @method('DELETE')<button class="rounded bg-black/60 px-1.5 text-xs text-white"><i class="fa-solid fa-xmark"></i></button>
                            </form>
                        </div>
                    @endforeach
                </div>
                <form method="POST" action="{{ route('ganado.fotos.store', $g->GanadoID) }}" enctype="multipart/form-data" class="mt-3 space-y-2">
                    @csrf
                    <input type="file" name="foto" accept="image/*" capture="environment" required class="block w-full text-xs file:mr-2 file:rounded-lg file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-white">
                    <x-ui.field label="Descripción" name="descripcion" placeholder="Lado izquierdo, fierro..." />
                    <x-ui.btn variante="oscuro" icono="fa-camera">Subir foto</x-ui.btn>
                </form>
            </x-ui.card>

            <!-- Salud -->
            <x-ui.card titulo="Salud" class="lg:col-span-2">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Casos de enfermedad</p>
                        @forelse ($g->eventosEnfermedad as $c)
                            <p class="border-b border-slate-100 py-1.5 text-xs text-slate-600">{{ $c->fecha_inicio->format('d/m/Y') }} · <strong>{{ $c->diagnostico }}</strong> · {{ \App\Models\Ganaderia\EventoEnfermedad::RESULTADOS[$c->resultado] ?? $c->resultado }}{{ $c->es_recaida ? ' · recaída' : '' }}</p>
                        @empty
                            <p class="text-xs text-emerald-600">Sin enfermedades registradas.</p>
                        @endforelse
                    </div>
                    <div>
                        <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Tratamientos</p>
                        @forelse ($g->tratamientos->take(8) as $t)
                            <p class="border-b border-slate-100 py-1.5 text-xs text-slate-600">{{ $t->fecha_aplicacion->format('d/m/Y') }} · {{ $t->medicamento_producto }} {{ $t->lote_medicamento ? '(lote ' . $t->lote_medicamento . ')' : '' }} · <span class="font-mono">{{ formatoPesos($t->costo_medicamento_mxn) }}</span>
                                @if ($t->fecha_fin_retiro && $t->fecha_fin_retiro->isFuture())<span class="font-semibold text-rose-600"> · retiro hasta {{ $t->fecha_fin_retiro->format('d/m') }}</span>@endif</p>
                        @empty
                            <p class="text-xs text-slate-400">Sin tratamientos.</p>
                        @endforelse
                    </div>
                </div>
            </x-ui.card>
        </div>

        <x-ui.documentos :modelo="$g" tipo="animal" />
    </div>
</x-app-layout>
