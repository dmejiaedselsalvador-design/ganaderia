<x-guest-layout>
    <div class="bg-white rounded-3xl shadow-xl shadow-stone-200/70 border border-stone-200/80 p-8 sm:p-10 space-y-6">

        <!-- Encabezado -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <i class="fa-solid fa-key text-[10px]"></i>
                    Recuperación
                </span>
                <a href="{{ route('login') }}" class="text-xs text-emerald-800 hover:text-emerald-900 font-semibold hover:underline">
                    ← Volver a Iniciar Sesión
                </a>
            </div>

            <h2 class="font-serif-brand text-2xl font-bold tracking-tight text-stone-900">
                ¿Olvidó su contraseña?
            </h2>
            <p class="mt-2 text-xs sm:text-sm text-stone-500 leading-relaxed">
                Escribe tu correo registrado. Tu solicitud llegará al administrador del sistema; cuando la apruebe,
                verás tu <strong class="text-stone-700">clave temporal en este mismo navegador</strong>.
                Para agilizarla, avísale al administrador.
            </p>
        </div>

        @if ($tieneSolicitud)
            <a href="{{ route('password.solicitud.estado') }}"
               class="flex items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 hover:bg-emerald-100">
                <span><i class="fa-solid fa-clock-rotate-left"></i> Ya enviaste una solicitud desde este navegador</span>
                <span class="text-xs">Ver estado →</span>
            </a>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <!-- Correo Electrónico -->
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1.5">
                    Correo Electrónico <span class="text-emerald-700">*</span>
                </label>
                <div class="relative rounded-xl shadow-2xs">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                        <i class="fa-regular fa-envelope text-sm"></i>
                    </span>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           required autofocus autocomplete="username"
                           placeholder="tu-correo@empresa.com"
                           class="block w-full rounded-xl border border-stone-300 bg-white py-3 pl-10 pr-4 text-sm text-stone-900 placeholder-stone-400 transition focus:border-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-700/10" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-rose-600" />
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-amber-200 hover:text-white px-6 py-3.5 text-sm font-semibold uppercase tracking-wider transition-all duration-200 shadow-md hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-800/20 active:scale-[0.99]">
                    <span>Enviar solicitud al administrador</span>
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                </button>
            </div>
        </form>

    </div>
</x-guest-layout>
