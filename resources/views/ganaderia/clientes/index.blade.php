<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6">
        <x-ui.header titulo="Clientes" subtitulo="Compradores de ganado con sus datos fiscales (CFDI 4.0), crédito y saldo." icono="fa-address-book">
            <x-slot name="acciones">
                <x-ui.btn :href="route('cobranza.index')" variante="secundario" icono="fa-hand-holding-dollar">Cuentas por cobrar</x-ui.btn>
                <x-ui.btn :href="route('clientes.create')" icono="fa-user-plus">Nuevo cliente</x-ui.btn>
            </x-slot>
        </x-ui.header>
        <x-ui.alertas />

        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
            <x-ui.kpi label="Por cobrar" :valor="formatoPesosCorto($porCobrar)" icono="fa-sack-dollar" color="bg-amber-50 text-amber-600" />
            <x-ui.kpi label="Al corriente" :valor="formatoPesosCorto($antiguedad['corriente'])" icono="fa-circle-check" color="bg-emerald-50 text-emerald-600" />
            <x-ui.kpi label="Vencido 1-30" :valor="formatoPesosCorto($antiguedad['1_30'])" icono="fa-clock" color="bg-yellow-50 text-yellow-600" />
            <x-ui.kpi label="Vencido 31-60" :valor="formatoPesosCorto($antiguedad['31_60'])" icono="fa-clock" color="bg-orange-50 text-orange-600" />
            <x-ui.kpi label="Vencido 61-90" :valor="formatoPesosCorto($antiguedad['61_90'])" icono="fa-triangle-exclamation" color="bg-rose-50 text-rose-600" />
            <x-ui.kpi label="Más de 90" :valor="formatoPesosCorto($antiguedad['mas_90'])" icono="fa-skull-crossbones" color="bg-rose-100 text-rose-700" />
        </div>

        <form method="GET" class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
            <div class="flex-1"><x-ui.field label="Buscar" name="q" :value="request('q')" placeholder="Nombre, razón social o RFC" /></div>
            <label class="flex items-center gap-2 pb-2 text-xs text-slate-600"><input type="checkbox" name="inactivos" value="1" @checked(request('inactivos')) class="rounded border-slate-300"> Incluir inactivos</label>
            <x-ui.btn variante="oscuro" icono="fa-magnifying-glass">Buscar</x-ui.btn>
        </form>

        <x-ui.card :padding="false">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-2.5">Cliente</th><th class="px-3">RFC</th><th class="px-3">Contacto</th><th class="px-3">Crédito</th>
                        <th class="px-3 text-right">Comprado</th><th class="px-3 text-right">Saldo</th><th></th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($clientes as $c)
                            <tr class="hover:bg-slate-50 {{ $c->activo ? '' : 'opacity-50' }}">
                                <td class="px-4 py-2.5"><a href="{{ route('clientes.show', $c) }}" class="font-semibold text-slate-800 hover:text-emerald-700">{{ $c->nombre }}</a>
                                    @if ($c->razon_social)<p class="text-[11px] text-slate-400">{{ $c->razon_social }}</p>@endif</td>
                                <td class="px-3 font-mono text-xs text-slate-600">{{ $c->rfc ?: '—' }}</td>
                                <td class="px-3 text-xs text-slate-500">{{ $c->telefono }}<br>{{ $c->email }}</td>
                                <td class="px-3 text-xs text-slate-500">{{ $c->dias_credito ? $c->dias_credito . ' días' : 'Contado' }}{{ (float) $c->limite_credito > 0 ? ' · límite ' . formatoPesosCorto($c->limite_credito) : '' }}</td>
                                <td class="px-3 text-right font-mono text-xs text-slate-600">{{ formatoPesosCorto($c->comprado ?? 0) }}</td>
                                <td class="px-3 text-right font-mono font-semibold {{ ($c->saldo ?? 0) > 0 ? 'text-amber-600' : 'text-slate-400' }}">{{ formatoPesos($c->saldo ?? 0) }}</td>
                                <td class="px-3 text-right"><a href="{{ route('clientes.edit', $c) }}" class="text-slate-400 hover:text-slate-800"><i class="fa-solid fa-pen-to-square"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-sm text-slate-400">Aún no hay clientes registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($clientes->hasPages())<div class="border-t border-slate-100 px-4 py-3">{{ $clientes->links() }}</div>@endif
        </x-ui.card>
    </div>
</x-app-layout>
