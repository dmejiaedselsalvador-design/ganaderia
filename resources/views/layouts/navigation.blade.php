@php
    $usuario    = Auth::user();
    $otras      = \App\Support\Plantas::accesibles($usuario)->except($plantaClave);
    $rolUsuario = $usuario->roles->sortByDesc('nivel')->first();
    $solicitudesPendientes = $usuario->can('usuarios.solicitudes_clave') ? \App\Models\SolicitudClave::pendientesCount() : null;
    $temas      = ['claro' => ['Claro', 'fa-sun'], 'oscuro' => ['Oscuro', 'fa-moon'], 'metal' => ['Metal', 'fa-gears']];
@endphp

<nav class="topnav sticky top-0 z-30 flex h-16 items-center justify-between gap-3 border-b px-4 sm:px-6 shadow-xs">
    <div class="flex min-w-0 flex-1 items-center gap-3">
        <!-- Botón Hamburguesa para Móviles -->
        <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-slate-600 hover:text-slate-900 focus:outline-none" aria-label="Abrir menú">
            <i class="fa-solid fa-bars text-lg"></i>
        </button>

        <!-- Planta activa -->
        <span class="hidden sm:inline-flex shrink-0 items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700">
            <span class="h-2 w-2 rounded-full" style="background: {{ $planta['acento'] ?? '#10b981' }}"></span>
            <i class="fa-solid {{ $planta['icono'] ?? 'fa-building' }} text-slate-500"></i>
            {{ $planta['nombre'] ?? '' }}
        </span>

        @if ($plantaClave === 'ganaderia' && $usuario->can('ganaderia.ganado'))
            <!-- Buscador de aretes -->
            <form action="{{ route('compras.ganado.perfil') }}" method="GET" class="flex w-full max-w-md items-center gap-2">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="arete" value="{{ request('arete') }}" placeholder="Escanear o digitar arete..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <button type="submit" class="hidden sm:block shrink-0 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-slate-800">
                    Buscar
                </button>
            </form>
        @endif
    </div>

    <div class="flex shrink-0 items-center gap-2 sm:gap-3">

        <!-- Selector de tema -->
        <div class="hidden md:flex items-center rounded-xl border border-slate-200 bg-slate-50 p-1" role="group" aria-label="Tema visual">
            @foreach ($temas as $clave => [$etiqueta, $icono])
                <button type="button" data-tema-btn="{{ $clave }}" onclick="window.cambiarTema('{{ $clave }}')" title="Tema {{ $etiqueta }}"
                    class="tema-btn flex h-8 items-center gap-1.5 rounded-lg px-2.5 text-xs font-semibold transition {{ $tema === $clave ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                    <i class="fa-solid {{ $icono }}"></i><span class="hidden lg:inline">{{ $etiqueta }}</span>
                </button>
            @endforeach
        </div>

        <!-- Ayuda de la pantalla y preguntas con captura -->
        @include('layouts._ayuda')

        <!-- Notificación: solicitudes de cambio de clave -->
        @if ($solicitudesPendientes !== null)
            <a href="{{ route('solicitudes.index') }}" title="{{ $solicitudesPendientes ? $solicitudesPendientes . ' solicitud(es) de cambio de clave' : 'Sin solicitudes de clave' }}"
               class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 transition hover:text-slate-900">
                <i class="fa-solid fa-bell"></i>
                @if ($solicitudesPendientes)
                    <span class="absolute -right-1.5 -top-1.5 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-bold text-white ring-2 ring-white animate-pulse">{{ $solicitudesPendientes }}</span>
                @endif
            </a>
        @endif

        <!-- Menú de usuario -->
        <div class="relative" x-data="{ open: false, plantas: false }" @keydown.escape.window="open = false">
            <button @click="open = !open" class="flex items-center gap-2 rounded-xl px-1.5 py-1 transition hover:bg-slate-100 focus:outline-none">
                <div class="flex h-9 w-9 items-center justify-center rounded-full border border-emerald-200 bg-emerald-100 text-sm font-bold text-emerald-700 shadow-xs">
                    {{ $usuario->iniciales() }}
                </div>
                <div class="hidden text-left leading-tight lg:block">
                    <p class="max-w-[10rem] truncate text-sm font-semibold text-slate-800">{{ $usuario->name }}</p>
                    <p class="text-[11px] text-slate-500">{{ $rolUsuario->display_name ?? 'Sin rol' }}</p>
                </div>
                <i class="fa-solid fa-chevron-down hidden text-[10px] text-slate-400 lg:block"></i>
            </button>

            <div x-show="open" x-cloak @click.outside="open = false" x-transition.origin.top.right
                class="absolute right-0 z-50 mt-2 w-72 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">

                <div class="border-b border-slate-100 px-4 py-3">
                    <p class="text-xs text-slate-500">Conectado como</p>
                    <p class="truncate text-sm font-semibold text-slate-900">{{ $usuario->name }}</p>
                    <p class="truncate text-xs text-slate-500">{{ $usuario->email }}</p>
                </div>

                <div class="py-1 text-sm">
                    <!-- Cambiar de planta -->
                    @if ($otras->isNotEmpty())
                        <button type="button" @click="plantas = !plantas" class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-slate-700 hover:bg-slate-50">
                            <i class="fa-solid fa-right-left w-4 text-center text-slate-400"></i>
                            <span class="flex-1 font-medium">Cambiar de planta</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition" :class="plantas && 'rotate-180'"></i>
                        </button>
                        <div x-show="plantas" x-cloak class="mx-3 mb-2 space-y-1 rounded-xl border border-slate-100 bg-slate-50 p-1.5">
                            @foreach ($otras as $clave => $p)
                                <form method="POST" action="{{ route('plantas.cambiar') }}">
                                    @csrf
                                    <input type="hidden" name="planta" value="{{ $clave }}">
                                    <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left hover:bg-white">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg text-white" style="background: {{ $p['acento'] }}">
                                            <i class="fa-solid {{ $p['icono'] }} text-xs"></i>
                                        </span>
                                        <span class="leading-tight">
                                            <span class="block text-sm font-semibold text-slate-800">{{ $p['nombre'] }}</span>
                                            <span class="block text-[11px] text-slate-500">Ir al dashboard</span>
                                        </span>
                                    </button>
                                </form>
                            @endforeach
                            <a href="{{ route('plantas.selector') }}" class="block px-3 py-1.5 text-center text-xs font-medium text-slate-500 hover:text-slate-800">Ver todas las plantas</a>
                        </div>
                    @endif

                    <!-- Tema (móvil) -->
                    <div class="flex items-center gap-1 px-4 py-2 md:hidden">
                        @foreach ($temas as $clave => [$etiqueta, $icono])
                            <button type="button" data-tema-btn="{{ $clave }}" onclick="window.cambiarTema('{{ $clave }}')"
                                class="tema-btn flex-1 rounded-lg border border-slate-200 py-1.5 text-xs font-semibold {{ $tema === $clave ? 'bg-slate-100 text-slate-900' : 'text-slate-500' }}">
                                <i class="fa-solid {{ $icono }}"></i> {{ $etiqueta }}
                            </button>
                        @endforeach
                    </div>

                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-slate-50">
                        <i class="fa-solid fa-user-gear w-4 text-center text-slate-400"></i> Mi perfil
                    </a>
                    <a href="{{ route('profile.edit') }}#clave" class="flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-slate-50">
                        <i class="fa-solid fa-key w-4 text-center text-slate-400"></i> Cambiar mi clave
                    </a>

                    @can('usuarios.ver')
                        <a href="{{ route('usuarios.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-slate-50">
                            <i class="fa-solid fa-users w-4 text-center text-slate-400"></i> Usuarios
                        </a>
                    @endcan
                    @can('usuarios.solicitudes_clave')
                        <a href="{{ route('solicitudes.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-slate-50">
                            <i class="fa-solid fa-key w-4 text-center text-slate-400"></i>
                            <span class="flex-1">Solicitudes de clave</span>
                            @if ($solicitudesPendientes)
                                <span class="rounded-full bg-rose-600 px-2 py-0.5 text-[10px] font-bold text-white">{{ $solicitudesPendientes }}</span>
                            @endif
                        </a>
                    @endcan
                    @can('ayuda.responder')
                        @php $preguntasPendientes = \App\Models\PreguntaAyuda::pendientes()->count(); @endphp
                        <a href="{{ route('ayuda.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-slate-50">
                            <i class="fa-solid fa-circle-question w-4 text-center text-slate-400"></i>
                            <span class="flex-1">Preguntas de usuarios</span>
                            @if ($preguntasPendientes)
                                <span class="rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ $preguntasPendientes }}</span>
                            @endif
                        </a>
                    @endcan
                    @role('super-admin')
                        <a href="{{ route('datos-prueba.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-slate-50">
                            <i class="fa-solid fa-flask-vial w-4 text-center text-slate-400"></i>
                            <span class="flex-1">Datos de prueba</span>
                            @if (\App\Support\Simulacion::activa())
                                <span class="rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-bold text-white">cargados</span>
                            @endif
                        </a>
                    @endrole
                    @can('auditoria.ver')
                        <a href="{{ route('auditoria.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-slate-50">
                            <i class="fa-solid fa-clipboard-list w-4 text-center text-slate-400"></i> Bitácora de auditoría
                        </a>
                    @endcan
                    @can('roles.gestionar')
                        <a href="{{ route('roles.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-slate-700 hover:bg-slate-50">
                            <i class="fa-solid fa-user-shield w-4 text-center text-slate-400"></i> Roles y permisos
                        </a>
                    @endcan
                </div>

                <!-- Cerrar sesión -->
                <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm font-semibold text-rose-600 hover:bg-rose-50">
                        <i class="fa-solid fa-arrow-right-from-bracket w-4 text-center"></i> Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
