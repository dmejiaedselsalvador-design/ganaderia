<x-app-layout>
    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-slate-100 p-8">

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <!-- Encabezado Temático -->
                    <div class="text-center mb-8 pb-4 border-b border-slate-100">
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-emerald-50 text-emerald-700 rounded-2xl mb-3 shadow-inner text-xl">
                            🌾
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Registro de Nuevo Personal</h2>
                        <p class="text-xs font-medium text-emerald-600 mt-0.5 uppercase tracking-wider">Sistema de Control Ganadero</p>
                    </div>

                    <!-- Name -->
                    <div>
                        <x-input-label for="name" :value="__('Nombre Completo')" class="text-slate-700 font-semibold text-sm mb-1" />
                        <x-text-input id="name" class="block mt-1 w-full border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl py-2.5 text-slate-800 shadow-xs" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Ej. Juan Pérez" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Correo Electrónico')" class="text-slate-700 font-semibold text-sm mb-1" />
                        <x-text-input id="email" class="block mt-1 w-full border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl py-2.5 text-slate-800 shadow-xs" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="correo@ganaderia.com" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('Contraseña')" class="text-slate-700 font-semibold text-sm mb-1" />
                        <x-text-input id="password" class="block mt-1 w-full border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl py-2.5 text-slate-800 shadow-xs"
                                        type="password"
                                        name="password"
                                        required autocomplete="new-password" placeholder="••••••••" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" class="text-slate-700 font-semibold text-sm mb-1" />
                        <x-text-input id="password_confirmation" class="block mt-1 w-full border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl py-2.5 text-slate-800 shadow-xs"
                                        type="password"
                                        name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <!-- Acciones -->
                    <div class="flex items-center justify-end pt-4 border-t border-slate-100 gap-3">
                        <x-primary-button class="bg-emerald-600 hover:bg-emerald-700 focus:bg-emerald-800 active:bg-emerald-900 focus:ring-emerald-500 transition-all px-6 py-3 rounded-xl text-white font-semibold shadow-sm flex items-center gap-2">
                            <span>🚜</span>
                            <span>{{ __('Registrar Cuenta') }}</span>
                        </x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
