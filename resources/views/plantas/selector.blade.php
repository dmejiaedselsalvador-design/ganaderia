<x-portal-layout titulo="Selecciona tu planta">
    <div class="mx-auto max-w-3xl pt-6 text-center sm:pt-12">
        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600 shadow-sm">
            <i class="fa-solid fa-hand text-amber-500"></i> Hola, {{ explode(' ', auth()->user()->name)[0] }}
        </span>
        <h1 class="mt-5 font-['Playfair_Display'] text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">¿En qué planta vas a trabajar hoy?</h1>
        <p class="mt-3 text-sm text-slate-500">Tu elección se mantiene durante toda la sesión. Puedes cambiarla cuando quieras desde el menú de tu usuario.</p>
    </div>

    @if (session('aviso'))
        <div class="mx-auto mt-6 max-w-3xl rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ session('aviso') }}</div>
    @endif

    <div class="mx-auto mt-10 grid max-w-4xl gap-6 sm:grid-cols-2">
        @foreach ($plantas as $clave => $p)
            <form method="POST" action="{{ route('plantas.cambiar') }}">
                @csrf
                <input type="hidden" name="planta" value="{{ $clave }}">
                <button type="submit"
                    class="group relative flex h-full w-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white p-7 text-left shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-slate-300/40">
                    <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full opacity-20 blur-2xl transition group-hover:opacity-40" style="background: {{ $p['acento'] }}"></div>

                    <div class="relative flex items-start justify-between">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl text-xl text-white shadow-lg" style="background: {{ $p['acento'] }}">
                            <i class="fa-solid {{ $p['icono'] }}"></i>
                        </div>
                        @if ($activa === $clave)
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 border border-emerald-200">Planta actual</span>
                        @elseif ($ultima === $clave)
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600 border border-slate-200">Última usada</span>
                        @endif
                    </div>

                    <h2 class="relative mt-6 text-xl font-bold text-slate-900">{{ $p['nombre'] }}</h2>
                    <p class="relative text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $p['marca'] }}</p>
                    <p class="relative mt-3 flex-1 text-sm leading-relaxed text-slate-600">{{ $p['descripcion'] }}</p>

                    <span class="relative mt-6 inline-flex items-center gap-2 text-sm font-semibold text-slate-900">
                        Entrar a {{ $p['nombre'] }}
                        <i class="fa-solid fa-arrow-right text-xs transition group-hover:translate-x-1"></i>
                    </span>
                </button>
            </form>
        @endforeach
    </div>
</x-portal-layout>
