<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6">

        @if ($ct = session('clave_temporal'))
            <!-- Clave temporal: se muestra UNA sola vez -->
            <div x-data="{ open: true, copiado: false }" x-show="open" x-cloak
                 class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
                <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-7 shadow-2xl">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-lg text-amber-600"><i class="fa-solid fa-key"></i></div>
                    <h2 class="mt-4 text-lg font-bold text-slate-900">Clave temporal de {{ $ct['nombre'] }}</h2>
                    <p class="mt-1 text-sm text-slate-500">Entrégasela por un medio seguro. <strong class="text-slate-700">No se volverá a mostrar</strong>; en su primer ingreso deberá cambiarla.</p>

                    <div class="mt-5 space-y-2 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs text-slate-500">Usuario</p>
                        <p class="font-mono text-sm font-semibold text-slate-800">{{ $ct['email'] }}</p>
                        <p class="pt-2 text-xs text-slate-500">Clave temporal</p>
                        <div class="flex items-center gap-2">
                            <code id="clave-temporal" class="flex-1 select-all rounded-lg bg-white px-3 py-2 font-mono text-lg font-bold tracking-wider text-slate-900 border border-slate-200">{{ $ct['clave'] }}</code>
                            <button type="button" class="rounded-lg bg-slate-900 px-3 py-2.5 text-xs font-semibold text-white hover:bg-slate-800"
                                    @click="navigator.clipboard.writeText(document.getElementById('clave-temporal').innerText.trim()); copiado = true">
                                <span x-show="!copiado"><i class="fa-regular fa-copy"></i> Copiar</span>
                                <span x-show="copiado" x-cloak><i class="fa-solid fa-check"></i> Copiada</span>
                            </button>
                        </div>
                    </div>

                    <button type="button" @click="open = false" class="mt-6 w-full rounded-xl bg-emerald-600 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                        Ya la entregué, cerrar
                    </button>
                </div>
            </div>
        @endif

        <!-- Encabezado -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Administración</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Usuarios del sistema</h1>
                <p class="mt-1 text-sm text-slate-500">Crea, edita, da de baja y asigna roles y plantas a tu equipo.</p>
            </div>
            <div class="flex gap-2">
                @can('roles.gestionar')
                    <a href="{{ route('roles.index') }}" class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                        <i class="fa-solid fa-user-shield text-slate-400"></i> Roles y permisos
                    </a>
                @endcan
                @can('usuarios.crear')
                    <a href="{{ route('usuarios.create') }}" class="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                        <i class="fa-solid fa-user-plus"></i> Nuevo usuario
                    </a>
                @endcan
            </div>
        </div>

        @can('usuarios.solicitudes_clave')
            @php $pend = \App\Models\SolicitudClave::pendientesCount(); @endphp
            @if ($pend)
                <a href="{{ route('solicitudes.index') }}" class="flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-800 hover:bg-rose-100">
                    <i class="fa-solid fa-bell"></i>
                    <span class="flex-1">Tienes {{ $pend }} {{ $pend === 1 ? 'solicitud' : 'solicitudes' }} de cambio de clave esperando respuesta.</span>
                    <span class="text-xs">Revisar →</span>
                </a>
            @endif
        @endcan

        <!-- Resumen -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                ['Total', $resumen['total'], 'fa-users', 'bg-blue-50 text-blue-600'],
                ['Activos', $resumen['activos'], 'fa-user-check', 'bg-emerald-50 text-emerald-600'],
                ['De baja', $resumen['inactivos'], 'fa-user-slash', 'bg-rose-50 text-rose-600'],
                ['Roles', $resumen['roles'], 'fa-user-shield', 'bg-amber-50 text-amber-600'],
            ] as [$label, $valor, $icono, $color])
                <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                        <p class="mt-1 text-2xl font-black text-slate-800">{{ $valor }}</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $color }}"><i class="fa-solid {{ $icono }}"></i></div>
                </div>
            @endforeach
        </div>

        <!-- Filtros -->
        <form method="GET" class="flex flex-col gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm lg:flex-row lg:items-center">
            <div class="flex rounded-xl border border-slate-200 bg-slate-50 p-1 text-xs font-semibold">
                @foreach (['activos' => 'Activos', 'inactivos' => 'De baja', 'todos' => 'Todos'] as $clave => $texto)
                    <a href="{{ request()->fullUrlWithQuery(['estado' => $clave, 'page' => null]) }}"
                       class="rounded-lg px-3 py-1.5 transition {{ $estado === $clave ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">{{ $texto }}</a>
                @endforeach
            </div>
            <input type="hidden" name="estado" value="{{ $estado }}">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre o correo..."
                       class="w-full rounded-xl border-slate-200 py-2 pl-9 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <select name="rol" class="rounded-xl border-slate-200 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" onchange="this.form.submit()">
                <option value="">Todos los roles</option>
                @foreach ($roles as $r)
                    <option value="{{ $r->name }}" @selected(request('rol') === $r->name)>{{ $r->display_name ?? $r->name }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Filtrar</button>
        </form>

        <!-- Tabla -->
        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3.5">Usuario</th>
                            <th class="px-5 py-3.5">Rol</th>
                            <th class="px-5 py-3.5">Plantas</th>
                            <th class="px-5 py-3.5">Estado</th>
                            <th class="px-5 py-3.5">Último acceso</th>
                            <th class="px-5 py-3.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($users as $u)
                            @php
                                $rolU = $u->roles->sortByDesc('nivel')->first();
                                $esSuper = $u->hasRole('super-admin');
                                $protegido = $esSuper && ! $actor->esSuperAdmin();
                                $soyYo = $actor->is($u);
                                $tienePlanta = fn ($p) => $u->tieneAccesoTotal() || $u->permissions->contains('name', $p['permiso']);
                            @endphp
                            <tr class="transition hover:bg-slate-50 {{ $u->estaActivo() ? '' : 'opacity-60' }}">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $esSuper ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $u->iniciales() }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-slate-900">
                                                {{ $u->name }}
                                                @if ($soyYo)<span class="ml-1 text-[10px] font-semibold uppercase text-emerald-600">(tú)</span>@endif
                                            </p>
                                            <p class="truncate text-xs text-slate-500">{{ $u->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    @if ($rolU)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold
                                            {{ $esSuper ? 'bg-amber-50 text-amber-700 border-amber-200' : ($u->hasRole('dueno') ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200') }}">
                                            @if ($esSuper)<i class="fa-solid fa-crown text-[10px]"></i>@endif
                                            {{ $rolU->display_name ?? $rolU->name }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">Sin rol</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($plantas as $p)
                                            @if ($tienePlanta($p))
                                                <span class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-medium text-slate-600" title="{{ $p['nombre'] }}">
                                                    <i class="fa-solid {{ $p['icono'] }}" style="color: {{ $p['acento'] }}"></i> {{ $p['nombre'] }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    @if ($u->estaActivo())
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Activo</span>
                                        @if ($u->bloqueo_solicitud_clave)
                                            <span class="mt-1 flex items-center gap-1 text-[11px] font-semibold text-rose-600" title="No puede solicitar cambios de clave"><i class="fa-solid fa-ban"></i> Solicitudes bloqueadas</span>
                                        @endif
                                        @if ($u->debeCambiarClave())
                                            <span class="mt-1 flex items-center gap-1 text-[11px] font-semibold text-amber-600" title="Aún no cambia su clave temporal"><i class="fa-solid fa-key"></i> Clave temporal</span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-700"><span class="h-2 w-2 rounded-full bg-rose-500"></span> De baja</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-500">
                                    {{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'Nunca' }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        @if ($protegido)
                                            <span class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs text-slate-400" title="Solo un Súper Administrador puede modificar esta cuenta">
                                                <i class="fa-solid fa-lock"></i> Protegido
                                            </span>
                                        @else
                                            @can('usuarios.editar')
                                                <a href="{{ route('usuarios.edit', $u) }}" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900" title="Editar">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                            @endcan
                                            @if (! $soyYo)
                                                @can('usuarios.editar')
                                                    <form method="POST" action="{{ route('usuarios.clave-temporal', $u) }}"
                                                          data-confirm="¿Generar una nueva clave temporal para {{ $u->name }}? Su clave actual dejará de funcionar y deberá cambiarla al ingresar.">
                                                        @csrf
                                                        <button class="flex h-8 w-8 items-center justify-center rounded-lg text-indigo-600 hover:bg-indigo-50" title="Generar clave temporal">
                                                            <i class="fa-solid fa-key"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                                @can('usuarios.solicitudes_clave')
                                                    <form method="POST" action="{{ route('solicitudes.bloqueo', $u) }}"
                                                          data-confirm="{{ $u->bloqueo_solicitud_clave ? '¿Permitir que ' . $u->name . ' vuelva a solicitar cambios de clave?' : '¿Bloquear las solicitudes de cambio de clave de ' . $u->name . '?' }}">
                                                        @csrf @method('PATCH')
                                                        <button class="flex h-8 w-8 items-center justify-center rounded-lg {{ $u->bloqueo_solicitud_clave ? 'text-rose-600 bg-rose-50' : 'text-slate-400 hover:bg-slate-100' }}"
                                                                title="{{ $u->bloqueo_solicitud_clave ? 'Desbloquear solicitudes de clave' : 'Bloquear solicitudes de clave' }}">
                                                            <i class="fa-solid fa-ban"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                                @can('usuarios.baja')
                                                    <form method="POST" action="{{ route('usuarios.estado', $u) }}"
                                                          data-confirm="{{ $u->estaActivo() ? '¿Dar de baja a ' . $u->name . '? Ya no podrá ingresar.' : '¿Reactivar a ' . $u->name . '?' }}">
                                                        @csrf @method('PATCH')
                                                        <button class="flex h-8 w-8 items-center justify-center rounded-lg {{ $u->estaActivo() ? 'text-amber-600 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50' }}"
                                                                title="{{ $u->estaActivo() ? 'Dar de baja' : 'Reactivar' }}">
                                                            <i class="fa-solid {{ $u->estaActivo() ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                                @can('usuarios.eliminar')
                                                    <form method="POST" action="{{ route('usuarios.destroy', $u) }}"
                                                          data-confirm="¿Eliminar definitivamente a {{ $u->name }}? Esta acción no se puede deshacer." data-peligro data-boton="Eliminar">
                                                        @csrf @method('DELETE')
                                                        <button class="flex h-8 w-8 items-center justify-center rounded-lg text-rose-600 hover:bg-rose-50" title="Eliminar">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">No hay usuarios con esos filtros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="border-t border-slate-100 px-5 py-3">{{ $users->links() }}</div>
            @endif
        </div>
    </div>

</x-app-layout>
