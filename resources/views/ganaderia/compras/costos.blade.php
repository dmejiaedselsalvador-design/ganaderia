<x-app-layout>
    <div class="mx-auto max-w-6xl space-y-6">
        <x-ui.header :titulo="'Guía ' . $factura->numeroFactura" :subtitulo="($factura->proveedor->nombreProveedor ?? '') . ' · ' . $factura->fechaFactura?->format('d/m/Y') . ' · ' . $factura->animales->count() . ' cabezas'" seccion="Costos completos de la compra" icono="fa-truck-ramp-box">
            <x-slot name="acciones"><x-ui.btn :href="route('proveedores.facturas.index')" variante="secundario" icono="fa-arrow-left">Guías de compra</x-ui.btn></x-slot>
        </x-ui.header>
        <x-ui.alertas />

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <x-ui.kpi label="Ganado (peso x precio)" :valor="formatoPesos($compra)" icono="fa-cow" />
            <x-ui.kpi label="Costos adicionales" :valor="formatoPesos($factura->costosAdicionales())" nota="Flete + comisión + trámites + otros" icono="fa-truck" color="bg-amber-50 text-amber-600" />
            <x-ui.kpi label="Costo total de adquisición" :valor="formatoPesos($compra + $factura->costosAdicionales())" :nota="$factura->animales->count() ? 'Por cabeza ' . formatoPesos(($compra + $factura->costosAdicionales()) / $factura->animales->count()) : ''" icono="fa-sack-dollar" color="bg-rose-50 text-rose-600" />
            <x-ui.kpi label="Merma de viaje (destare)" :valor="$merma ? $merma['pct'] . '%' : '—'" :nota="$merma ? number_format($merma['kg'], 1) . ' kg · llegada ' . number_format($merma['llegada'], 0) . ' kg' : 'Captura el peso en origen'" icono="fa-weight-hanging" color="bg-blue-50 text-blue-600" />
        </div>

        <form method="POST" action="{{ route('compras.costos.update', $factura->id) }}" class="space-y-6">
            @csrf @method('PUT')
            <x-ui.card titulo="Costos y trazabilidad de la guía" subtitulo="Los costos se reparten entre los animales en proporción a su peso de llegada.">
                <div class="grid gap-3 sm:grid-cols-4">
                    <x-ui.field label="Flete ($)" name="costo_flete" type="number" step="0.01" :value="$factura->costo_flete" />
                    <x-ui.field label="Comisión del corredor ($)" name="costo_comision" type="number" step="0.01" :value="$factura->costo_comision" />
                    <x-ui.field label="Trámites ($)" name="costo_tramites" type="number" step="0.01" :value="$factura->costo_tramites" ayuda="REEMO, certificado zoosanitario, pruebas TB/BR" />
                    <x-ui.field label="Otros costos ($)" name="otros_costos" type="number" step="0.01" :value="$factura->otros_costos" />
                    <x-ui.field label="Peso total en origen (kg)" name="peso_origen_kg" type="number" step="0.01" :value="$factura->peso_origen_kg" ayuda="Báscula del vendedor" />
                    <x-ui.field label="Folio guía REEMO" name="guia_reemo" :value="$factura->guia_reemo" />
                    <x-ui.field label="UPP de origen" name="upp_origen" :value="$factura->upp_origen" ayuda="Se copia a los animales sin UPP" />
                </div>
            </x-ui.card>

            <x-ui.card titulo="Animales de la guía" subtitulo="Peso en origen individual (opcional) y su parte de los costos adicionales" :padding="false">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-4 py-2.5">Arete</th><th class="px-3">Categoría</th><th class="px-3 text-right">Peso llegada</th><th class="px-3">Peso origen</th><th class="px-3 text-right">Precio/kg</th><th class="px-3 text-right">Costo adicional</th><th class="px-3 text-right">Costo total</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($factura->animales as $a)
                                <tr>
                                    <td class="px-4 py-2 font-mono font-semibold"><a href="{{ route('ganado.ficha', $a->GanadoID) }}" class="hover:text-emerald-700">{{ $a->areteID }}</a></td>
                                    <td class="px-3 text-xs text-slate-500">{{ $a->categoria }}</td>
                                    <td class="px-3 text-right font-mono">{{ number_format($a->pesoActual, 1) }}</td>
                                    <td class="px-3"><input type="number" step="0.01" name="pesos_origen[{{ $a->GanadoID }}]" value="{{ $a->peso_origen }}" class="w-28 rounded-lg border-slate-200 py-1 text-xs font-mono"></td>
                                    <td class="px-3 text-right font-mono text-xs">$ {{ number_format($a->precioCompra, 2) }}</td>
                                    <td class="px-3 text-right font-mono text-xs text-amber-700">{{ formatoPesos($a->costo_adicional_compra) }}</td>
                                    <td class="px-3 text-right font-mono font-semibold">{{ formatoPesos($a->costoAdquisicion()) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
            <x-ui.btn icono="fa-floppy-disk">Guardar y repartir costos</x-ui.btn>
        </form>

        <x-ui.documentos :modelo="$factura" tipo="compra" />
    </div>
</x-app-layout>
