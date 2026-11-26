@php
    use App\Models\Correccion;
    $fmt = fn ($n, $d = 1) => number_format((float) $n, $d);
    $inp = 'mt-1 block w-full rounded-xl border-slate-200 py-2 text-sm';
    $lbl = 'block text-[11px] font-semibold uppercase tracking-wider text-slate-600';
@endphp
<x-app-layout>
    <div class="mx-auto max-w-6xl space-y-6">
        <x-ui.header titulo="Corrección de registros" subtitulo="Corrige o anula datos mal capturados. Cada cambio pide un motivo y queda registrado con el valor anterior, el nuevo y quién lo hizo." seccion="Control" icono="fa-pen-to-square" />
        <x-ui.alertas />

        <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
            <i class="fa-solid fa-circle-info"></i> Las cuentas se ajustan solas: al corregir un servido o un tratamiento se devuelve y se vuelve a descontar de bodega; al corregir un pesaje se recalcula la ganancia diaria; al anular una venta los animales regresan a los corrales y se cancelan sus abonos.
        </div>

        <!-- ============ POR ANIMAL ============ -->
        <x-ui.card titulo="Registros de un animal" subtitulo="Datos de compra, pesajes, tratamientos, baja y venta">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div class="w-64">
                    <label class="{{ $lbl }}">Arete</label>
                    <input type="text" name="arete" value="{{ $arete }}" placeholder="Escanea o escribe el arete" autofocus class="{{ $inp }} font-mono">
                </div>
                <x-ui.btn icono="fa-magnifying-glass" variante="oscuro">Buscar</x-ui.btn>
            </form>

            @if ($arete !== '' && ! $ganado)
                <p class="mt-4 text-sm text-rose-600">No se encontró el arete «{{ $arete }}».</p>
            @endif

            @if ($ganado)
                <div class="mt-5 space-y-5">
                    <div class="flex flex-wrap items-center gap-3 rounded-xl bg-slate-50 px-4 py-3">
                        <span class="font-mono text-lg font-black text-slate-900">{{ $ganado->areteID }}</span>
                        <span class="text-sm text-slate-600">{{ $ganado->categoria }} · {{ $ganado->raza }} · Corral {{ $ganado->corralLote?->codigo ?? '—' }}</span>
                        <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-bold text-slate-700">{{ $ganado->status }}</span>
                        <a href="{{ route('ganado.ficha', $ganado->GanadoID) }}" class="ml-auto text-xs font-semibold text-emerald-700 hover:underline">Ver ficha</a>
                    </div>

                    <!-- Compra -->
                    <section class="rounded-xl border border-slate-100 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Datos de compra</h3>
                                <p class="text-xs text-slate-500">{{ $ganado->fechaCompra?->format('d/m/Y') }} · {{ $fmt($ganado->pesoActual) }} kg a $ {{ $fmt($ganado->precioCompra, 2) }}/kg</p>
                            </div>
                            <x-ui.corregir :action="route('correcciones.compra', $ganado->GanadoID)" class="sm:w-auto sm:min-w-[22rem]" aviso="El flete y la comisión de la guía se vuelven a repartir entre sus animales.">
                                <div><label class="{{ $lbl }}">Peso de compra (kg)</label><input type="number" step="0.1" name="peso" required value="{{ (float) $ganado->pesoActual }}" class="{{ $inp }}"></div>
                                <div><label class="{{ $lbl }}">Precio $/kg</label><input type="number" step="0.01" name="precio" required value="{{ (float) $ganado->precioCompra }}" class="{{ $inp }}"></div>
                                <div><label class="{{ $lbl }}">Fecha de compra</label><input type="date" name="fecha" required value="{{ $ganado->fechaCompra?->toDateString() }}" class="{{ $inp }}"></div>
                                <div><label class="{{ $lbl }}">Categoría</label>
                                    <select name="categoria" class="{{ $inp }}">@foreach (array_merge(\App\Models\Ganado\Ganado::CATEGORIAS_MACHOS, \App\Models\Ganado\Ganado::CATEGORIAS_HEMBRAS) as $c)<option @selected($ganado->categoria === $c)>{{ $c }}</option>@endforeach</select>
                                </div>
                                <input type="hidden" name="raza" value="{{ $ganado->raza }}">
                            </x-ui.corregir>
                        </div>
                    </section>

                    <!-- Pesajes -->
                    <section class="rounded-xl border border-slate-100 p-4">
                        <h3 class="mb-2 text-sm font-bold text-slate-900">Pesajes ({{ $ganado->pesajes->count() }})</h3>
                        <div class="divide-y divide-slate-100">
                            @forelse ($ganado->pesajes as $p)
                                <div class="flex flex-wrap items-start justify-between gap-3 py-2.5">
                                    <p class="text-sm text-slate-700"><strong>{{ $p->fecha_pesaje->format('d/m/Y') }}</strong> · <span class="font-mono">{{ $fmt($p->peso) }} kg</span>
                                        <span class="text-xs text-slate-500">· GDP {{ $fmt($p->gdp, 2) }} kg/día @if ($p->condicion_corporal) · CC {{ $p->condicion_corporal }} @endif</span></p>
                                    <x-ui.corregir :action="route('correcciones.pesaje', $p)" anular="Anular pesaje" class="sm:w-auto sm:min-w-[22rem]">
                                        <div><label class="{{ $lbl }}">Peso (kg)</label><input type="number" step="0.1" name="peso" required value="{{ (float) $p->peso }}" class="{{ $inp }}"></div>
                                        <div><label class="{{ $lbl }}">Fecha</label><input type="date" name="fecha" required value="{{ $p->fecha_pesaje->toDateString() }}" class="{{ $inp }}"></div>
                                        <div><label class="{{ $lbl }}">Condición corporal</label><input type="number" step="0.1" min="1" max="5" name="condicion_corporal" value="{{ $p->condicion_corporal }}" class="{{ $inp }}"></div>
                                    </x-ui.corregir>
                                </div>
                            @empty
                                <p class="py-2 text-xs text-slate-400">Sin pesajes.</p>
                            @endforelse
                        </div>
                    </section>

                    <!-- Tratamientos -->
                    <section class="rounded-xl border border-slate-100 p-4">
                        <h3 class="mb-2 text-sm font-bold text-slate-900">Tratamientos ({{ $ganado->tratamientos->count() }})</h3>
                        <div class="divide-y divide-slate-100">
                            @forelse ($ganado->tratamientos as $t)
                                <div class="flex flex-wrap items-start justify-between gap-3 py-2.5">
                                    <p class="text-sm text-slate-700"><strong>{{ $t->fecha_aplicacion?->format('d/m/Y') }}</strong> · {{ $t->medicamento_producto }}
                                        <span class="text-xs text-slate-500">· {{ $t->dosis_aplicada }} @if ($t->cantidad_insumo) ({{ $fmt($t->cantidad_insumo, 2) }} de bodega) @endif · retiro {{ $t->dias_retiro_rastro }} días</span></p>
                                    <x-ui.corregir :action="route('correcciones.tratamiento', $t)" anular="Anular tratamiento" class="sm:w-auto sm:min-w-[22rem]">
                                        <div><label class="{{ $lbl }}">Fecha</label><input type="date" name="fecha" required value="{{ $t->fecha_aplicacion?->toDateString() }}" class="{{ $inp }}"></div>
                                        <div><label class="{{ $lbl }}">Cantidad de bodega</label><input type="number" step="0.001" name="cantidad_insumo" value="{{ $t->cantidad_insumo !== null ? (float) $t->cantidad_insumo : '' }}" @disabled(! $t->insumo_id) class="{{ $inp }}"></div>
                                        <div><label class="{{ $lbl }}">Dosis</label><input type="text" name="dosis" value="{{ $t->dosis_aplicada }}" class="{{ $inp }}"></div>
                                        <div><label class="{{ $lbl }}">Días de retiro</label><input type="number" name="dias_retiro_rastro" value="{{ $t->dias_retiro_rastro }}" class="{{ $inp }}"></div>
                                    </x-ui.corregir>
                                </div>
                            @empty
                                <p class="py-2 text-xs text-slate-400">Sin tratamientos.</p>
                            @endforelse
                        </div>
                    </section>

                    <!-- Baja / venta -->
                    @if ($ganado->baja)
                        <section class="flex flex-wrap items-start justify-between gap-3 rounded-xl border border-rose-100 bg-rose-50 p-4">
                            <p class="text-sm text-slate-700"><strong>Baja ({{ $ganado->baja->tipo_baja }})</strong> el {{ $ganado->baja->fecha_muerte?->format('d/m/Y') }} · {{ $ganado->baja->causa_muerte }}</p>
                            <x-ui.corregir :action="route('correcciones.baja', $ganado->baja)" solo="Revertir baja" class="sm:w-auto sm:min-w-[22rem]" aviso="El animal vuelve a estar activo en su corral." />
                        </section>
                    @endif
                    @if ($ganado->detalleVenta?->venta && $ganado->detalleVenta->venta->estado === 'completada')
                        @php $v = $ganado->detalleVenta->venta; @endphp
                        <section class="flex flex-wrap items-start justify-between gap-3 rounded-xl border border-amber-100 bg-amber-50 p-4">
                            <p class="text-sm text-slate-700"><strong>Vendido</strong> en {{ $v->folio_venta }} el {{ $v->fecha_venta?->format('d/m/Y') }} a {{ $v->cliente_nombre }} ({{ $v->total_cabezas }} cabezas, $ {{ $fmt($v->total_venta, 2) }})</p>
                            <x-ui.corregir :action="route('correcciones.venta', $v)" solo="Anular venta completa" class="sm:w-auto sm:min-w-[22rem]" aviso="Se anula toda la venta: todos sus animales regresan a los corrales y se cancelan los abonos." />
                        </section>
                    @endif
                </div>
            @endif
        </x-ui.card>

        <!-- ============ SERVIDOS ============ -->
        <x-ui.card titulo="Servidos de alimento" subtitulo="Al corregir o anular, los ingredientes se devuelven a bodega y se vuelven a descontar con los datos correctos">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="arete" value="{{ $arete }}">
                <div class="w-56"><label class="{{ $lbl }}">Corral</label>
                    <select name="corral" class="{{ $inp }}"><option value="">Todos</option>@foreach ($corrales as $c)<option value="{{ $c->id }}" @selected($corralId === $c->id)>{{ $c->codigo }} · {{ $c->nombre }}</option>@endforeach</select>
                </div>
                <div><label class="{{ $lbl }}">Desde</label><input type="date" name="desde" value="{{ $desde->toDateString() }}" class="{{ $inp }}"></div>
                <div><label class="{{ $lbl }}">Hasta</label><input type="date" name="hasta" value="{{ $hasta->toDateString() }}" class="{{ $inp }}"></div>
                <x-ui.btn icono="fa-filter" variante="secundario">Filtrar</x-ui.btn>
            </form>
            <div class="mt-4 divide-y divide-slate-100">
                @forelse ($servidos as $s)
                    <div class="flex flex-wrap items-start justify-between gap-3 py-2.5">
                        <p class="text-sm text-slate-700"><strong>{{ $s->fecha->format('d/m/Y') }}</strong> · {{ $s->corralLote?->codigo }} · {{ $s->racion?->codigo }}
                            <span class="font-mono">· {{ $fmt($s->kilos_servidos, 0) }} kg</span> <span class="text-xs text-slate-500">(rechazo {{ $fmt($s->kilos_rechazo, 0) }} kg · $ {{ $fmt($s->costo_total_mxn, 2) }})</span></p>
                        <x-ui.corregir :action="route('correcciones.servido', $s)" anular="Anular servido" class="sm:w-auto sm:min-w-[22rem]">
                            <div><label class="{{ $lbl }}">Fecha</label><input type="date" name="fecha" required value="{{ $s->fecha->toDateString() }}" class="{{ $inp }}"></div>
                            <div><label class="{{ $lbl }}">Dieta</label><select name="racion_id" class="{{ $inp }}">@foreach ($raciones as $r)<option value="{{ $r->id }}" @selected($r->id === $s->racion_id)>{{ $r->codigo }}</option>@endforeach</select></div>
                            <div><label class="{{ $lbl }}">Kg servidos</label><input type="number" step="0.1" name="kilos_servidos" required value="{{ (float) $s->kilos_servidos }}" class="{{ $inp }}"></div>
                            <div><label class="{{ $lbl }}">Kg rechazo</label><input type="number" step="0.1" name="kilos_rechazo" value="{{ (float) $s->kilos_rechazo }}" class="{{ $inp }}"></div>
                        </x-ui.corregir>
                    </div>
                @empty
                    <p class="py-3 text-xs text-slate-400">No hay servidos en ese periodo.</p>
                @endforelse
            </div>
        </x-ui.card>

        <!-- ============ VENTAS ============ -->
        <x-ui.card titulo="Ventas recientes" subtitulo="Anular una venta regresa sus animales a los corrales y cancela sus abonos">
            <div class="divide-y divide-slate-100">
                @forelse ($ventas as $v)
                    <div class="flex flex-wrap items-start justify-between gap-3 py-2.5">
                        <p class="text-sm text-slate-700"><strong>{{ $v->folio_venta }}</strong> · {{ $v->fecha_venta?->format('d/m/Y') }} · {{ $v->cliente_nombre }}
                            <span class="text-xs text-slate-500">· {{ $v->total_cabezas }} cab. · $ {{ $fmt($v->total_venta, 2) }} · saldo $ {{ $fmt($v->saldo_pendiente, 2) }}</span></p>
                        <x-ui.corregir :action="route('correcciones.venta', $v)" solo="Anular venta" class="sm:w-auto sm:min-w-[22rem]" />
                    </div>
                @empty
                    <p class="py-3 text-xs text-slate-400">Sin ventas vigentes.</p>
                @endforelse
            </div>
        </x-ui.card>

        <!-- ============ HISTORIAL ============ -->
        <x-ui.card :titulo="$ganado ? 'Correcciones de este animal' : 'Últimas correcciones'" subtitulo="Quién corrigió qué, cuándo y por qué">
            <div class="divide-y divide-slate-100">
                @forelse ($historial as $c)
                    <div class="py-2.5 text-sm" x-data="{ ver: false }">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $c->accion === 'corregido' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-700' }}">{{ ucfirst($c->accion) }}</span>
                            <span class="text-xs font-semibold text-slate-500">{{ Correccion::TIPOS[$c->tipo] ?? $c->tipo }}</span>
                            <span class="text-slate-800">{{ $c->descripcion }}</span>
                            <button type="button" @click="ver = !ver" class="ml-auto text-xs font-semibold text-emerald-700">Detalle</button>
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $c->created_at->format('d/m/Y H:i') }} · {{ $c->usuario?->name ?? 'Sistema' }} · Motivo: <em>{{ $c->motivo }}</em></p>
                        <div x-show="ver" x-cloak class="mt-2 grid gap-2 text-[11px] sm:grid-cols-2">
                            <pre class="overflow-x-auto whitespace-pre-wrap rounded-lg bg-rose-50 p-2 text-rose-900">Antes: {{ json_encode($c->antes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            <pre class="overflow-x-auto whitespace-pre-wrap rounded-lg bg-emerald-50 p-2 text-emerald-900">Después: {{ json_encode($c->despues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                @empty
                    <p class="py-3 text-xs text-slate-400">Aún no hay correcciones.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
