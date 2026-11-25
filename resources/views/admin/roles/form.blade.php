@php
    $editando = $role->exists;
    $marcados = old('permisos', $marcados);
    $input = 'mt-1 block w-full rounded-xl border-slate-200 py-2.5 text-sm text-slate-800 shadow-xs focus:border-emerald-500 focus:ring-emerald-500 disabled:opacity-60';
    $label = 'block text-xs font-semibold uppercase tracking-wider text-slate-600';
    $iconos = ['Ganadería' => 'fa-cow', 'Construcción' => 'fa-helmet-safety', 'Administración' => 'fa-user-gear'];
@endphp
<x-app-layout>
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('roles.index') }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm hover:text-slate-900">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Roles</p>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ $editando ? ($role->display_name ?? $role->name) : 'Nuevo rol' }}</h1>
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <ul class="list-inside list-disc text-xs">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @if ($sistema)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <i class="fa-solid fa-lock"></i> Rol del sistema: siempre tiene <strong>todos los permisos</strong> en todas las plantas. Solo puedes editar su descripción.
            </div>
        @endif

        <form method="POST" action="{{ $editando ? route('roles.update', $role) : route('roles.store') }}" class="space-y-6" x-data>
            @csrf
            @if ($editando) @method('PUT') @endif

            <section class="grid gap-5 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm sm:grid-cols-2">
                <div>
                    <label class="{{ $label }}" for="display_name">Nombre del rol *</label>
                    <input id="display_name" name="display_name" value="{{ old('display_name', $role->display_name) }}" @disabled($sistema) required class="{{ $input }}" placeholder="Ej. Bodeguero">
                </div>
                <div>
                    <label class="{{ $label }}" for="name">Identificador</label>
                    <input id="name" name="name" value="{{ old('name', $role->name) }}" @disabled($sistema) class="{{ $input }} font-mono" placeholder="se genera automáticamente">
                </div>
                <div class="sm:col-span-2">
                    <label class="{{ $label }}" for="description">Descripción</label>
                    <textarea id="description" name="description" rows="2" class="{{ $input }}">{{ old('description', $role->description) }}</textarea>
                </div>
                <div>
                    <label class="{{ $label }}" for="nivel">Nivel jerárquico (1-89)</label>
                    <input id="nivel" type="number" min="1" max="89" name="nivel" value="{{ old('nivel', $role->nivel ?? 10) }}" @disabled($sistema) class="{{ $input }}">
                    <p class="mt-1 text-xs text-slate-500">Un usuario solo puede asignar roles de nivel menor al suyo. Referencia: Gerente 70 · Supervisor 50 · Operario 20.</p>
                </div>
            </section>

            @unless ($sistema)
                <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <h2 class="flex items-center gap-2 text-sm font-bold text-slate-800"><i class="fa-solid fa-list-check text-emerald-600"></i> Permisos</h2>
                    <div class="mt-5 grid gap-5 lg:grid-cols-3">
                        @foreach ($grupos as $grupo => $permisos)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4" x-data>
                                <div class="mb-3 flex items-center justify-between">
                                    <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-700">
                                        <i class="fa-solid {{ $iconos[$grupo] ?? 'fa-folder' }} text-slate-400"></i> {{ $grupo }}
                                    </h3>
                                    <button type="button" class="text-[11px] font-semibold text-emerald-700 hover:underline"
                                            @click="$el.closest('div.rounded-xl').querySelectorAll('input[type=checkbox]').forEach(c => c.checked = true)">Todos</button>
                                </div>
                                <div class="space-y-2">
                                    @foreach ($permisos as $permiso => $etiqueta)
                                        <label class="flex cursor-pointer items-start gap-2.5 rounded-lg p-1.5 text-sm text-slate-700 hover:bg-white">
                                            <input type="checkbox" name="permisos[]" value="{{ $permiso }}" @checked(in_array($permiso, $marcados, true))
                                                   class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                            <span>{{ $etiqueta }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endunless

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('roles.index') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</a>
                <button class="flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar rol
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
