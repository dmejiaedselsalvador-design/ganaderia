<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6">

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
                                                @can('usuarios.baja')
                                                    <form method="POST" action="{{ route('usuarios.estado', $u) }}"
                                                          onsubmit="return confirmar(event, '{{ $u->estaActivo() ? '¿Dar de baja a ' . e($u->name) . '? Ya no podrá ingresar.' : '¿Reactivar a ' . e($u->name) . '?' }}')">
                                                        @csrf @method('PATCH')
                                                        <button class="flex h-8 w-8 items-center justify-center rounded-lg {{ $u->estaActivo() ? 'text-amber-600 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50' }}"
                                                                title="{{ $u->estaActivo() ? 'Dar de baja' : 'Reactivar' }}">
                                                            <i class="fa-solid {{ $u->estaActivo() ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                                @can('usuarios.eliminar')
                                                    <form method="POST" action="{{ route('usuarios.destroy', $u) }}"
                                                          onsubmit="return confirmar(event, '¿Eliminar definitivamente a {{ e($u->name) }}? Esta acción no se puede deshacer.', true)">
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

    <script>
        function confirmar(e, mensaje, peligro = false) {
            if (!window.Swal) return confirm(mensaje);
            e.preventDefault();
            const form = e.target;
            Swal.fire({
                title: '¿Estás seguro?', text: mensaje, icon: peligro ? 'warning' : 'question',
                showCancelButton: true, confirmButtonText: 'Sí, continuar', cancelButtonText: 'Cancelar',
                confirmButtonColor: peligro ? '#e11d48' : '#059669',
                background: getComputedStyle(document.body).getPropertyValue('--t-surface'),
                color: getComputedStyle(document.body).getPropertyValue('--t-text'),
            }).then(r => { if (r.isConfirmed) form.submit(); });
            return false;
        }
    </script>
</x-app-layout>
