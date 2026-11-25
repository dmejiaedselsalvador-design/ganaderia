<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Administración</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Roles y permisos</h1>
                <p class="mt-1 text-sm text-slate-500">Define qué puede hacer cada tipo de usuario. El acceso a plantas se asigna en cada usuario.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('usuarios.index') }}" class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    <i class="fa-solid fa-users text-slate-400"></i> Usuarios
                </a>
                <a href="{{ route('roles.create') }}" class="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                    <i class="fa-solid fa-plus"></i> Nuevo rol
                </a>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($roles as $r)
                @php
                    $esSistema = in_array($r->name, $sistema, true);
                    $n = $esSistema ? $totalPermisos : $r->permissions->count();
                    $pct = $totalPermisos ? round($n / $totalPermisos * 100) : 0;
                @endphp
                <div class="flex flex-col rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="flex items-center gap-2 text-base font-bold text-slate-900">
                                @if ($r->name === 'super-admin')<i class="fa-solid fa-crown text-amber-500"></i>@elseif($r->name === 'dueno')<i class="fa-solid fa-key text-indigo-500"></i>@endif
                                {{ $r->display_name ?? $r->name }}
                            </h3>
                            <p class="font-mono text-[11px] text-slate-400">{{ $r->name }} · nivel {{ $r->nivel }}</p>
                        </div>
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
                            <i class="fa-solid fa-user text-[10px]"></i> {{ $r->users_count }}
                        </span>
                    </div>
                    <p class="mt-3 flex-1 text-sm leading-relaxed text-slate-500">{{ $r->description }}</p>

                    <div class="mt-4">
                        <div class="flex justify-between text-xs font-semibold text-slate-500">
                            <span>Permisos</span><span>{{ $esSistema ? 'Todos' : "$n de $totalPermisos" }}</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full {{ $esSistema ? 'bg-amber-500' : 'bg-emerald-500' }}" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                        @if ($esSistema)
                            <span class="mr-auto text-xs text-slate-400"><i class="fa-solid fa-lock"></i> Rol del sistema</span>
                        @endif
                        <a href="{{ route('roles.edit', $r) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                            <i class="fa-solid fa-pen-to-square"></i> {{ $esSistema ? 'Ver' : 'Editar' }}
                        </a>
                        @unless ($esSistema)
                            <form method="POST" action="{{ route('roles.destroy', $r) }}" onsubmit="return confirm('¿Eliminar el rol {{ e($r->display_name ?? $r->name) }}?')">
                                @csrf @method('DELETE')
                                <button class="rounded-lg px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50"><i class="fa-solid fa-trash-can"></i></button>
                            </form>
                        @endunless
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
