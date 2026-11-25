<x-portal-layout titulo="Cambiar clave temporal">
    <div class="mx-auto mt-6 max-w-lg sm:mt-12">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-xl text-amber-600">
                <i class="fa-solid fa-key"></i>
            </div>
            <h1 class="mt-5 text-2xl font-extrabold tracking-tight text-slate-900">Crea tu clave personal</h1>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                Ingresaste con una <strong class="text-slate-700">clave temporal</strong> asignada por el administrador.
                Por seguridad y protección de tus datos personales, debes reemplazarla por una clave que solo tú conozcas antes de continuar.
            </p>

            @if ($errors->any())
                <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <ul class="list-inside list-disc space-y-0.5 text-xs">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.cambiar.guardar') }}" class="mt-6 space-y-5" x-data="{ ver: false }">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600">Clave temporal</label>
                    <input id="current_password" name="current_password" :type="ver ? 'text' : 'password'" required autocomplete="current-password" autofocus
                           class="mt-1 block w-full rounded-xl border-slate-200 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600">Nueva clave</label>
                    <input id="password" name="password" :type="ver ? 'text' : 'password'" required autocomplete="new-password"
                           class="mt-1 block w-full rounded-xl border-slate-200 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-600">Confirmar nueva clave</label>
                    <input id="password_confirmation" name="password_confirmation" :type="ver ? 'text' : 'password'" required autocomplete="new-password"
                           class="mt-1 block w-full rounded-xl border-slate-200 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <label class="flex items-center gap-2 text-xs text-slate-500">
                    <input type="checkbox" x-model="ver" class="h-4 w-4 rounded border-slate-300 text-emerald-600"> Mostrar claves
                </label>

                <x-ui.reglas-clave actual="current_password" boton="btn-guardar-clave" />

                <button id="btn-guardar-clave" class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 transition px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                    <i class="fa-solid fa-lock"></i> Guardar mi clave y continuar
                </button>
            </form>
        </div>
        <p class="mt-4 text-center text-xs text-slate-400">Nadie del equipo, ni el administrador, podrá ver tu nueva clave.</p>
    </div>
</x-portal-layout>
