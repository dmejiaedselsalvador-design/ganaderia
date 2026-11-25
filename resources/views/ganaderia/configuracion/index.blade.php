<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6">
        <x-ui.header titulo="Configuración ganadera" subtitulo="Precios de mercado para valuar el hato, ranchos/ubicaciones y metas o presupuesto por lote." icono="fa-sliders" />
        <x-ui.alertas />

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Precios vigentes -->
            <x-ui.card titulo="Precios de mercado vigentes" subtitulo="MXN por kg en pie · el último registrado de cada categoría" class="lg:col-span-2">
                @if ($vigentes->isEmpty())
                    <p class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                        <i class="fa-solid fa-triangle-exclamation"></i> Aún no hay precios. Sin ellos el sistema no puede calcular el <strong>valor real del hato</strong> ni la utilidad proyectada.
                        Registra al menos el precio <strong>General</strong>.
                    </p>
                @else
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach ($vigentes as $p)
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                                <p class="text-[11px] font-semibold uppercase text-slate-400">{{ $p->categoria }}</p>
                                <p class="font-mono text-lg font-black text-emerald-700">$ {{ number_format($p->precio_kg, 2) }}</p>
                                <p class="text-[11px] text-slate-400">{{ $p->fecha->format('d/m/Y') }}{{ $p->fuente ? ' · ' . $p->fuente : '' }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('configuracion.precios.store') }}" class="mt-5 grid gap-3 border-t border-slate-100 pt-5 sm:grid-cols-5">
                    @csrf
                    <x-ui.field label="Fecha" name="fecha" type="date" :value="date('Y-m-d')" required />
                    <x-ui.field label="Categoría" name="categoria" type="select" :options="$categorias" required :placeholder="false" />
                    <x-ui.field label="Precio $/kg" name="precio_kg" type="number" step="0.01" required />
                    <x-ui.field label="Fuente" name="fuente" placeholder="Subasta, comprador, SNIIM..." colspan="sm:col-span-2" />
                    <div class="sm:col-span-5"><x-ui.btn icono="fa-plus">Registrar precio</x-ui.btn></div>
                </form>
            </x-ui.card>

            <x-ui.card titulo="Historial de precios" :padding="false">
                <div class="max-h-96 divide-y divide-slate-100 overflow-y-auto">
                    @forelse ($historial as $p)
                        <div class="flex items-center justify-between px-5 py-2.5 text-sm">
                            <div>
                                <p class="font-semibold text-slate-700">{{ $p->categoria }} · <span class="font-mono">$ {{ number_format($p->precio_kg, 2) }}</span></p>
                                <p class="text-[11px] text-slate-400">{{ $p->fecha->format('d/m/Y') }} · {{ $p->usuario->name ?? '' }}</p>
                            </div>
                            <form method="POST" action="{{ route('configuracion.precios.destroy', $p) }}" data-confirm="¿Eliminar este registro de precio?" data-peligro data-boton="Eliminar">
                                @csrf @method('DELETE')
                                <button class="text-xs text-rose-500 hover:text-rose-700"><i class="fa-solid fa-trash-can"></i></button>
                            </form>
                        </div>
                    @empty
                        <p class="px-5 py-6 text-center text-xs text-slate-400">Sin registros.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <!-- Ubicaciones -->
        <x-ui.card titulo="Ranchos / ubicaciones" subtitulo="Predios o engordas con su clave UPP (SINIIGA). Cada corral puede pertenecer a una ubicación.">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($ubicaciones as $u)
                    <form method="POST" action="{{ route('configuracion.ubicaciones.update', $u) }}" class="space-y-2 rounded-xl border border-slate-100 bg-slate-50 p-4" x-data="{ editar: false }">
                        @csrf @method('PUT')
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-bold text-slate-800">{{ $u->nombre }} @unless($u->activo)<span class="text-xs font-normal text-rose-500">(inactiva)</span>@endunless</p>
                                <p class="text-xs text-slate-500">{{ $u->municipio }}{{ $u->estado ? ', ' . $u->estado : '' }} · UPP {{ $u->clave_upp ?: '—' }} · {{ $u->corrales_count }} corrales</p>
                            </div>
                            <button type="button" @click="editar = !editar" class="text-xs font-semibold text-emerald-700">Editar</button>
                        </div>
                        <div x-show="editar" x-cloak class="grid gap-2 pt-2 sm:grid-cols-2">
                            <x-ui.field label="Nombre" name="nombre" :value="$u->nombre" required />
                            <x-ui.field label="Clave UPP" name="clave_upp" :value="$u->clave_upp" />
                            <x-ui.field label="Municipio" name="municipio" :value="$u->municipio" />
                            <x-ui.field label="Estado" name="estado" :value="$u->estado" />
                            <x-ui.field label="Responsable" name="responsable" :value="$u->responsable" colspan="sm:col-span-2" />
                            <label class="flex items-center gap-2 text-xs text-slate-600"><input type="checkbox" name="activo" value="1" @checked($u->activo) class="rounded border-slate-300 text-emerald-600"> Activa</label>
                            <div class="sm:col-span-2"><x-ui.btn icono="fa-floppy-disk">Guardar</x-ui.btn></div>
                        </div>
                    </form>
                @endforeach
            </div>
            <form method="POST" action="{{ route('configuracion.ubicaciones.store') }}" class="mt-5 grid gap-3 border-t border-slate-100 pt-5 sm:grid-cols-5">
                @csrf
                <x-ui.field label="Nombre" name="nombre" required placeholder="Rancho El Mezquite" />
                <x-ui.field label="Clave UPP" name="clave_upp" />
                <x-ui.field label="Municipio" name="municipio" value="Hermosillo" />
                <x-ui.field label="Estado" name="estado" value="Sonora" />
                <x-ui.field label="Responsable" name="responsable" />
                <div class="sm:col-span-5"><x-ui.btn icono="fa-plus" variante="oscuro">Agregar ubicación</x-ui.btn></div>
            </form>
        </x-ui.card>

        <!-- Metas por lote -->
        <x-ui.card titulo="Metas y presupuesto por lote" subtitulo="Se comparan contra lo real en Reportes › Rentabilidad por lote." :padding="false">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-2.5">Corral</th><th class="px-2">Ubicación</th><th class="px-2">GDP meta</th><th class="px-2">Costo/kg meta</th>
                        <th class="px-2">Mortalidad % máx.</th><th class="px-2">Conversión meta</th><th class="px-2">Presupuesto $</th><th></th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($corrales as $c)
                            <tr>
                                    <td class="px-4 py-2 font-semibold text-slate-800">{{ $c->codigo }}<span class="block text-[11px] font-normal text-slate-400">{{ $c->nombre }}</span></td>
                                    <td class="px-2"><select form="metas-{{ $c->id }}" name="ubicacion_id" class="w-36 rounded-lg border-slate-200 py-1.5 text-xs"><option value="">—</option>@foreach ($ubicaciones as $u)<option value="{{ $u->id }}" @selected($c->ubicacion_id === $u->id)>{{ $u->nombre }}</option>@endforeach</select></td>
                                    <td class="px-2"><input form="metas-{{ $c->id }}" name="meta_gdp" type="number" step="0.01" value="{{ $c->meta_gdp }}" class="w-20 rounded-lg border-slate-200 py-1.5 text-xs" placeholder="1.50"></td>
                                    <td class="px-2"><input form="metas-{{ $c->id }}" name="meta_costo_kg" type="number" step="0.01" value="{{ $c->meta_costo_kg }}" class="w-24 rounded-lg border-slate-200 py-1.5 text-xs" placeholder="38.00"></td>
                                    <td class="px-2"><input form="metas-{{ $c->id }}" name="meta_mortalidad_pct" type="number" step="0.01" value="{{ $c->meta_mortalidad_pct }}" class="w-20 rounded-lg border-slate-200 py-1.5 text-xs" placeholder="1.5"></td>
                                    <td class="px-2"><input form="metas-{{ $c->id }}" name="meta_conversion" type="number" step="0.01" value="{{ $c->meta_conversion }}" class="w-20 rounded-lg border-slate-200 py-1.5 text-xs" placeholder="6.5"></td>
                                    <td class="px-2"><input form="metas-{{ $c->id }}" name="presupuesto_mxn" type="number" step="0.01" value="{{ $c->presupuesto_mxn }}" class="w-28 rounded-lg border-slate-200 py-1.5 text-xs"></td>
                                    <td class="px-3"><button form="metas-{{ $c->id }}" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white">Guardar</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
        @foreach ($corrales as $c)
            <form id="metas-{{ $c->id }}" method="POST" action="{{ route('configuracion.metas.update', $c) }}" class="hidden">@csrf @method('PUT')</form>
        @endforeach
    </div>
</x-app-layout>
