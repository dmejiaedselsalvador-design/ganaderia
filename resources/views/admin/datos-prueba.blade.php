<x-app-layout>
    <div class="mx-auto max-w-4xl space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Administración</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Datos de prueba</h1>
            <p class="mt-1 text-sm text-slate-500">Carga una operación simulada para ver cómo se ve el sistema lleno, enseñarlo o capacitar al personal. Cuando termines, elimínala con un clic y el sistema queda limpio para la operación real.</p>
        </div>

        <x-ui.alertas />

        @if ($activa)
            <x-ui.card titulo="Simulación cargada" subtitulo="Cargada el {{ $resumen['desde']?->format('d/m/Y H:i') }} · {{ number_format($resumen['total']) }} registros de prueba">
                <div class="grid gap-2 text-xs sm:grid-cols-3">
                    @foreach (array_slice($resumen['tablas'], 0, 12, true) as $tabla => $total)
                        <div class="flex justify-between rounded-lg bg-slate-50 px-3 py-2"><span class="text-slate-500">{{ str_replace('_', ' ', $tabla) }}</span><strong class="font-mono text-slate-800">{{ number_format($total) }}</strong></div>
                    @endforeach
                </div>

                <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    <i class="fa-solid fa-users"></i> Usuarios de prueba (uno por rol): <strong>demo.dueno@simulacion.local</strong>, demo.gerente@…, demo.supervisor@…, demo.operario@…, demo.vendedor@…, demo.comprador@simulacion.local
                    · clave <strong class="font-mono">{{ \Database\Seeders\SimulacionSeeder::CLAVE_USUARIOS }}</strong>. Entra con ellos para ver qué ve cada rol.
                </div>

                <form method="POST" action="{{ route('datos-prueba.eliminar') }}" class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-4" x-data="{ texto: '' }">
                    @csrf @method('DELETE')
                    <h3 class="font-bold text-rose-800"><i class="fa-solid fa-trash-can"></i> Eliminar los datos de prueba</h3>
                    <p class="mt-1 text-sm text-rose-700">Se borra <strong>solo</strong> lo que creó la simulación (animales, guías, ventas, servidos, usuarios demo…). Si capturaste algo real encima de un registro de prueba (por ejemplo, un pesaje a un animal de prueba), también se pierde.</p>
                    <div class="mt-3 flex flex-wrap items-end gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-rose-700">Escribe ELIMINAR para confirmar</label>
                            <input type="text" name="confirmacion" x-model="texto" autocomplete="off" class="mt-1 w-48 rounded-xl border-rose-200 py-2 font-mono text-sm">
                        </div>
                        <button type="submit" :disabled="texto !== 'ELIMINAR'" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-40">
                            Eliminar datos de prueba
                        </button>
                    </div>
                </form>
            </x-ui.card>
        @else
            <x-ui.card titulo="Cargar simulación" subtitulo="Unos 3 meses de una engorda en Hermosillo, Sonora">
                <ul class="grid gap-x-6 gap-y-1.5 text-sm text-slate-600 sm:grid-cols-2">
                    @foreach ([
                        '10 proveedores y 10 guías de compra (100 animales)', '10 corrales con metas y dieta asignada', '10 dietas con receta (una capturada por cantidades)',
                        '10 ingredientes y 10 medicamentos con compras', 'Pesajes, movimientos de corral y pesos estimados', '45 días de servidos que descuentan bodega',
                        'Protocolos sanitarios, calendario y 10 casos de enfermedad', 'Bajas por muerte, sacrificio, robo y descarte', '10 clientes, 10 ventas de contado y crédito con abonos',
                        '10 evaluaciones de canal, gastos y precios de mercado', 'Un usuario de prueba por rol', 'Preguntas de ayuda de ejemplo',
                    ] as $item)
                        <li class="flex gap-2"><i class="fa-solid fa-check mt-1 text-emerald-500"></i><span>{{ $item }}</span></li>
                    @endforeach
                </ul>

                @if ($sePuede)
                    <form method="POST" action="{{ route('datos-prueba.cargar') }}" class="mt-6" x-data="{ cargando: false }" @submit="cargando = true">
                        @csrf
                        <button type="submit" :disabled="cargando" class="flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60">
                            <i class="fa-solid" :class="cargando ? 'fa-spinner fa-spin' : 'fa-flask-vial'"></i>
                            <span x-text="cargando ? 'Cargando simulación… (tarda unos segundos)' : 'Cargar datos de prueba'"></span>
                        </button>
                    </form>
                @else
                    <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        <i class="fa-solid fa-lock"></i> No se puede cargar porque <strong>ya hay ganado registrado</strong>. La simulación solo se carga sobre una operación vacía, para no mezclarse con datos reales.
                    </div>
                @endif
            </x-ui.card>
        @endif
    </div>
</x-app-layout>
