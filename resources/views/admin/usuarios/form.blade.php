@php
    $editando = $user->exists;
    $soyYo = $editando && auth()->user()->is($user);
    $rolSel = old('rol', $rolActual);
    $plantasSel = old('plantas', $plantasUsuario);
    $input = 'mt-1 block w-full rounded-xl border-slate-200 py-2.5 text-sm text-slate-800 shadow-xs focus:border-emerald-500 focus:ring-emerald-500';
    $label = 'block text-xs font-semibold uppercase tracking-wider text-slate-600';
@endphp
<x-app-layout>
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('usuarios.index') }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm hover:text-slate-900">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Usuarios</p>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ $editando ? 'Editar usuario' : 'Nuevo usuario' }}</h1>
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <p class="font-semibold"><i class="fa-solid fa-circle-exclamation"></i> Revisa los datos:</p>
                <ul class="mt-1 list-inside list-disc text-xs">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ $editando ? route('usuarios.update', $user) : route('usuarios.store') }}" class="space-y-6">
            @csrf
            @if ($editando) @method('PUT') @endif

            <!-- Datos personales -->
            <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <h2 class="flex items-center gap-2 text-sm font-bold text-slate-800"><i class="fa-solid fa-id-card text-emerald-600"></i> Datos del usuario</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="{{ $label }}" for="name">Nombre completo *</label>
                        <input id="name" name="name" value="{{ old('name', $user->name) }}" required class="{{ $input }}" placeholder="Ej. Juan Pérez">
                    </div>
                    <div>
                        <label class="{{ $label }}" for="email">Correo electrónico *</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required class="{{ $input }}" placeholder="correo@empresa.com">
                    </div>
                    <div>
                        <label class="{{ $label }}" for="telefono">Teléfono</label>
                        <input id="telefono" name="telefono" value="{{ old('telefono', $user->telefono) }}" class="{{ $input }}" placeholder="7000-0000">
                    </div>
                    <div>
                        <label class="{{ $label }}">Estado</label>
                        <div class="mt-1 flex gap-2">
                            @foreach (['active' => ['Activo', 'fa-user-check', 'peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700'],
                                       'inactive' => ['De baja', 'fa-user-slash', 'peer-checked:border-rose-500 peer-checked:bg-rose-50 peer-checked:text-rose-700']] as $val => [$txt, $ico, $cls])
                                <label class="flex-1">
                                    <input type="radio" name="status" value="{{ $val }}" class="peer sr-only" @checked(old('status', $user->status ?? 'active') === $val) @disabled($soyYo && $val === 'inactive')>
                                    <span class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-slate-200 py-2.5 text-sm font-semibold text-slate-500 transition {{ $cls }}">
                                        <i class="fa-solid {{ $ico }}"></i> {{ $txt }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="sm:col-span-2 rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <p class="flex items-center gap-2 text-sm font-semibold text-amber-800"><i class="fa-solid fa-key"></i> Clave temporal</p>
                        <p class="mt-1 text-xs text-amber-800/80">
                            @if ($soyYo)
                                Para cambiar tu propia clave usa «Mi perfil».
                            @elseif ($editando)
                                Déjalo vacío para no cambiarla. Si escribes una, será <strong>temporal</strong>: el usuario deberá cambiarla al ingresar. También puedes usar el botón <i class="fa-solid fa-key"></i> de la lista para generar una automática.
                            @else
                                Déjalo vacío y el sistema <strong>generará una clave temporal segura</strong> que verás una sola vez. En su primer ingreso el usuario estará obligado a cambiarla por una personal.
                            @endif
                        </p>
                    </div>
                    @unless ($soyYo)
                        <div>
                            <label class="{{ $label }}" for="password">Clave temporal (opcional)</label>
                            <input id="password" type="text" name="password" autocomplete="off" class="{{ $input }} font-mono" placeholder="Vacío = generar automáticamente">
                        </div>
                        <div>
                            <label class="{{ $label }}" for="password_confirmation">Confirmar clave temporal</label>
                            <input id="password_confirmation" type="text" name="password_confirmation" autocomplete="off" class="{{ $input }} font-mono">
                        </div>
                    @endunless
                </div>
            </section>

            <!-- Rol -->
            <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="flex items-center gap-2 text-sm font-bold text-slate-800"><i class="fa-solid fa-user-shield text-emerald-600"></i> Rol</h2>
                    @can('roles.gestionar')
                        <a href="{{ route('roles.create') }}" class="text-xs font-semibold text-emerald-700 hover:underline">+ Crear rol nuevo</a>
                    @endcan
                </div>
                @if ($soyYo)
                    <p class="mt-2 text-xs text-amber-700"><i class="fa-solid fa-circle-info"></i> No puedes cambiar tu propio rol ni darte de baja.</p>
                @endif
                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($rolesAsignables as $r)
                        <label class="block">
                            <input type="radio" name="rol" value="{{ $r->name }}" class="peer sr-only" @checked($rolSel === $r->name) @disabled($soyYo && $rolSel !== $r->name)>
                            <span class="flex h-full cursor-pointer flex-col rounded-xl border border-slate-200 p-3.5 transition hover:border-slate-300 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:ring-2 peer-checked:ring-emerald-500/20 peer-disabled:cursor-not-allowed peer-disabled:opacity-50">
                                <span class="flex items-center justify-between text-sm font-bold text-slate-800">
                                    {{ $r->display_name ?? $r->name }}
                                    @if ($r->name === 'super-admin')<i class="fa-solid fa-crown text-xs text-amber-500"></i>@endif
                                </span>
                                <span class="mt-1 text-xs leading-snug text-slate-500">{{ $r->description }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>

            <!-- Plantas -->
            <section class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <h2 class="flex items-center gap-2 text-sm font-bold text-slate-800"><i class="fa-solid fa-building text-emerald-600"></i> Acceso a plantas</h2>
                <p class="mt-1 text-xs text-slate-500">Si solo tiene una planta, entrará directo a su dashboard. Con dos o más, elegirá al iniciar sesión. Súper Admin y Dueño ven todas.</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($plantas as $clave => $p)
                        <label class="block">
                            <input type="checkbox" name="plantas[]" value="{{ $clave }}" class="peer sr-only" @checked(in_array($clave, $plantasSel, true))>
                            <span class="flex cursor-pointer items-center gap-4 rounded-xl border border-slate-200 p-4 transition hover:border-slate-300 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:ring-2 peer-checked:ring-emerald-500/20">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white" style="background: {{ $p['acento'] }}"><i class="fa-solid {{ $p['icono'] }}"></i></span>
                                <span>
                                    <span class="block text-sm font-bold text-slate-800">{{ $p['nombre'] }}</span>
                                    <span class="block text-xs text-slate-500">{{ $p['descripcion'] }}</span>
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('usuarios.index') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</a>
                <button class="flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                    <i class="fa-solid fa-floppy-disk"></i> {{ $editando ? 'Guardar cambios' : 'Crear usuario' }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
