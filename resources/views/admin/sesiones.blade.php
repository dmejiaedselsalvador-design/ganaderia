<x-app-layout>
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Administración</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Sesiones activas</h1>
                <p class="mt-1 text-sm text-slate-500">Quién tiene la sesión abierta y en qué equipo. Al expulsar, esa persona tendrá que volver a iniciar sesión (también se anula su «Mantener sesión iniciada»).</p>
            </div>
            @if ($disponible && $sesiones->count() > 1)
                <form method="POST" action="{{ route('sesiones.todas') }}" data-confirm="Todos los demás usuarios tendrán que volver a iniciar sesión. ¿Continuar?" data-peligro data-boton="Sí, sacar a todos">
                    @csrf @method('DELETE')
                    <button class="flex items-center gap-2 whitespace-nowrap rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700"><i class="fa-solid fa-power-off"></i> Cerrar todas las sesiones</button>
                </form>
            @endif
        </div>

        @unless ($disponible)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <i class="fa-solid fa-triangle-exclamation"></i> Las sesiones se guardan en archivos. Para verlas aquí pon <code class="font-mono">SESSION_DRIVER=database</code> en el archivo <code>.env</code> y corre <code class="font-mono">php artisan migrate</code>.
            </div>
        @endunless

        @if ($disponible)
            <div class="grid gap-4 sm:grid-cols-3">
                <x-ui.kpi label="En línea ahora" :valor="$enLinea" icono="fa-signal" :nota="'Activos en los últimos ' . \App\Support\Sesiones::MINUTOS_EN_LINEA . ' min'" />
                <x-ui.kpi label="Usuarios con sesión" :valor="$usuarios" icono="fa-users" color="bg-blue-50 text-blue-600" />
                <x-ui.kpi label="Sesiones abiertas" :valor="$sesiones->count()" icono="fa-laptop" color="bg-amber-50 text-amber-600" nota="Un usuario puede tener varios equipos" />
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <tr><th class="px-5 py-3">Usuario</th><th class="px-5 py-3">Equipo</th><th class="px-5 py-3">Planta</th><th class="px-5 py-3">Última actividad</th><th class="px-5 py-3 text-right">Acciones</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($sesiones as $s)
                            @php
                                $u = $s->usuario; $esMia = $s->id === $actual;
                                $protegido = $u->hasRole('super-admin') && ! auth()->user()->hasRole('super-admin');
                            @endphp
                            <tr class="{{ $esMia ? 'bg-emerald-50' : '' }}">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600">
                                            {{ $u->iniciales() }}
                                            <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full ring-2 ring-white {{ $s->en_linea ? 'bg-emerald-500' : 'bg-slate-300' }}" title="{{ $s->en_linea ? 'En línea' : 'Inactivo' }}"></span>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900">{{ $u->name }} @if ($esMia)<span class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">Tú · este equipo</span>@endif</p>
                                            <p class="truncate text-xs text-slate-500">{{ $u->roles->sortByDesc('nivel')->first()->display_name ?? 'Sin rol' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-slate-700">
                                    <i class="fa-solid {{ $s->dispositivo['movil'] ? 'fa-mobile-screen' : 'fa-desktop' }} mr-1 text-slate-400"></i> {{ $s->dispositivo['texto'] }}
                                    <p class="font-mono text-[11px] text-slate-400">{{ $s->ip }}</p>
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ $s->planta ? (\App\Support\Plantas::info($s->planta)['nombre'] ?? ucfirst($s->planta)) : '—' }}</td>
                                <td class="px-5 py-3">
                                    <span class="{{ $s->en_linea ? 'font-semibold text-emerald-600' : 'text-slate-500' }}">{{ $s->en_linea ? 'En línea' : $s->ultima->diffForHumans() }}</span>
                                    <p class="text-[11px] text-slate-400">{{ $s->ultima->format('d/m/Y H:i') }}</p>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-2">
                                        @if ($esMia)
                                            <span class="text-xs text-slate-400">Sesión actual</span>
                                        @elseif ($protegido)
                                            <span class="text-xs text-slate-400" title="El Dueño no puede expulsar al Súper Administrador"><i class="fa-solid fa-lock"></i> Protegido</span>
                                        @else
                                            <form method="POST" action="{{ route('sesiones.expulsar', $s->id) }}" data-confirm="¿Sacar a {{ $u->name }} de {{ $s->dispositivo['texto'] }}?" data-peligro data-boton="Expulsar">
                                                @csrf @method('DELETE')
                                                <button class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50"><i class="fa-solid fa-right-from-bracket"></i> Expulsar</button>
                                            </form>
                                            <form method="POST" action="{{ route('sesiones.usuario', $u) }}" data-confirm="¿Sacar a {{ $u->name }} de todos sus equipos?" data-peligro data-boton="Sí, de todos">
                                                @csrf @method('DELETE')
                                                <button class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50" title="Cerrar todas sus sesiones"><i class="fa-solid fa-user-slash"></i> Todos sus equipos</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">No hay sesiones abiertas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
