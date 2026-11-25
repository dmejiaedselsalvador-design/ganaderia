<x-app-layout>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h2 class="text-lg font-medium text-gray-900">Mis otros equipos</h2>
                    <p class="mt-1 text-sm text-gray-600">Si dejaste la sesión abierta en otro celular o computadora, ciérrala desde aquí. En este equipo seguirás conectado.</p>
                    <form method="POST" action="{{ route('profile.sesiones') }}" class="mt-4" data-confirm="Se cerrará tu sesión en todos tus otros equipos. ¿Continuar?" data-boton="Cerrar sesiones">
                        @csrf @method('DELETE')
                        <button class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión en mis otros equipos</button>
                    </form>
                </div>
            </div>


        </div>
    </div>
</x-app-layout>
