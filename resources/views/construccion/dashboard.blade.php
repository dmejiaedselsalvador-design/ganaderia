<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-8">
        <div class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-600">Planta Construcción</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Dashboard de Obras</h1>
                <p class="mt-1 text-sm text-slate-500">Resumen de proyectos, presupuestos, materiales y personal.</p>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                <i class="fa-solid fa-person-digging"></i> Planta en implementación
            </span>
        </div>

        <!-- KPIs (se conectarán cuando existan los módulos) -->
        <div class="grid grid-cols-2 gap-5 lg:grid-cols-4">
            @foreach ([
                ['Obras activas', 'fa-building', 'bg-amber-50 text-amber-600'],
                ['Avance promedio', 'fa-chart-line', 'bg-blue-50 text-blue-600'],
                ['Presupuesto ejecutado', 'fa-sack-dollar', 'bg-emerald-50 text-emerald-600'],
                ['Personal en campo', 'fa-people-group', 'bg-indigo-50 text-indigo-600'],
            ] as [$label, $icono, $color])
                <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                        <p class="mt-1 text-2xl font-black text-slate-300">—</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $color }}"><i class="fa-solid {{ $icono }} text-lg"></i></div>
                </div>
            @endforeach
        </div>

        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-2xl text-amber-600">
                <i class="fa-solid fa-helmet-safety"></i>
            </div>
            <h2 class="mt-4 text-lg font-bold text-slate-900">La planta Construcción ya está habilitada</h2>
            <p class="mx-auto mt-2 max-w-xl text-sm text-slate-500">
                El acceso, los roles, los temas y el cambio de planta ya funcionan. Los indicadores se llenarán a medida que se construyan los módulos de abajo.
            </p>
        </div>

        <div>
            <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-slate-500">Módulos planificados</h2>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($modulos as $clave => [$titulo, $icono, $desc])
                    @can("construccion.$clave")
                        <a href="{{ route('construccion.modulo', $clave) }}" class="group rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-md">
                    @else
                        <div class="rounded-2xl border border-slate-100 bg-white p-5 opacity-60 shadow-sm">
                    @endcan
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="fa-solid {{ $icono }}"></i></div>
                        <h3 class="mt-4 font-bold text-slate-900">{{ $titulo }}</h3>
                        <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ $desc }}</p>
                    @can("construccion.$clave")
                        </a>
                    @else
                        </div>
                    @endcan
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
