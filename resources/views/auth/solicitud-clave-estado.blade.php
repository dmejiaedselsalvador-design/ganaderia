@php use App\Models\SolicitudClave as S; @endphp
<x-guest-layout>
    @if ($solicitud && $solicitud->estado === S::PENDIENTE && ! $solicitud->vencida())
        {{-- Revisa el estado automáticamente cada 30 segundos --}}
        <meta http-equiv="refresh" content="30">
    @endif

    <div class="bg-white rounded-3xl shadow-xl shadow-stone-200/70 border border-stone-200/80 p-8 sm:p-10 space-y-6" x-data="{ copiado: false }">
        <div class="flex items-center justify-between">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800">
                <i class="fa-solid fa-key text-[10px]"></i> Recuperación de clave
            </span>
            <a href="{{ route('login') }}" class="text-xs font-semibold text-emerald-800 hover:underline">← Iniciar sesión</a>
        </div>

        @if (! $solicitud)
            <h2 class="font-serif-brand text-2xl font-bold text-stone-900">Sin solicitudes</h2>
            <p class="text-sm text-stone-500">No hay ninguna solicitud de cambio de clave enviada desde este navegador.</p>
            <a href="{{ route('password.request') }}" class="block rounded-xl bg-slate-900 py-3 text-center text-sm font-semibold text-amber-200 hover:bg-slate-800">Hacer una solicitud</a>

        @elseif ($clave)
            {{-- Clave aprobada: se muestra UNA sola vez --}}
            <div class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-xl text-emerald-600"><i class="fa-solid fa-circle-check"></i></div>
                <h2 class="mt-4 font-serif-brand text-2xl font-bold text-stone-900">¡Solicitud aprobada!</h2>
                <p class="mt-1 text-sm text-stone-500">Esta es tu clave temporal. <strong class="text-stone-700">Cópiala ahora: no se volverá a mostrar.</strong></p>
            </div>
            <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4 space-y-2">
                <p class="text-xs text-stone-500">Usuario</p>
                <p class="font-mono text-sm font-semibold text-stone-800">{{ $solicitud->user->email }}</p>
                <p class="pt-2 text-xs text-stone-500">Clave temporal</p>
                <div class="flex items-center gap-2">
                    <code id="clave" class="flex-1 select-all rounded-lg border border-stone-200 bg-white px-3 py-2 font-mono text-lg font-bold tracking-wider text-stone-900">{{ $clave }}</code>
                    <button type="button" class="rounded-lg bg-slate-900 px-3 py-2.5 text-xs font-semibold text-white"
                            @click="navigator.clipboard.writeText(document.getElementById('clave').innerText.trim()); copiado = true">
                        <span x-show="!copiado">Copiar</span><span x-show="copiado" x-cloak>Copiada ✓</span>
                    </button>
                </div>
            </div>
            <p class="text-xs text-stone-500">Al ingresar con esta clave, el sistema te pedirá crear tu clave personal.</p>
            <a href="{{ route('login') }}" class="block rounded-xl bg-emerald-600 py-3 text-center text-sm font-semibold text-white hover:bg-emerald-700">Ir a iniciar sesión</a>

        @else
            @php
                $vencida = $solicitud->vencida() || ($solicitud->estado === S::APROBADA);
                [$titulo, $texto, $icono, $color] = match (true) {
                    $solicitud->estado === S::PENDIENTE && ! $vencida => ['Solicitud en revisión', 'El administrador todavía no responde. Esta página se actualiza sola; también puedes volver más tarde desde este mismo navegador.', 'fa-hourglass-half', 'bg-amber-50 text-amber-600'],
                    $solicitud->estado === S::ENTREGADA => ['Clave ya entregada', 'La clave temporal ya se mostró en este navegador. Si no la guardaste, envía una nueva solicitud.', 'fa-check-double', 'bg-blue-50 text-blue-600'],
                    $solicitud->estado === S::DENEGADA => ['Solicitud denegada', 'El administrador no aprobó tu solicitud. Comunícate directamente con él.', 'fa-circle-xmark', 'bg-rose-50 text-rose-600'],
                    default => ['Solicitud vencida', 'El tiempo para esta solicitud terminó. Envía una nueva.', 'fa-clock', 'bg-stone-100 text-stone-500'],
                };
            @endphp
            <div class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl text-xl {{ $color }}"><i class="fa-solid {{ $icono }}"></i></div>
                <h2 class="mt-4 font-serif-brand text-2xl font-bold text-stone-900">{{ $titulo }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-stone-500">{{ $texto }}</p>
            </div>
            <div class="flex items-center justify-between rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm">
                <span class="text-stone-500">Folio</span>
                <span class="font-mono font-bold text-stone-800">{{ $solicitud->folio() }}</span>
            </div>
            @if ($solicitud->estado === S::DENEGADA && $solicitud->motivo)
                <p class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-800">Motivo: {{ $solicitud->motivo }}</p>
            @endif
            @if ($solicitud->estado === S::PENDIENTE && ! $vencida)
                <a href="{{ route('password.solicitud.estado') }}" class="block rounded-xl border border-stone-200 py-3 text-center text-sm font-semibold text-stone-700 hover:bg-stone-50">
                    <i class="fa-solid fa-rotate"></i> Consultar de nuevo
                </a>
            @elseif ($solicitud->estado !== S::DENEGADA)
                <a href="{{ route('password.request') }}" class="block rounded-xl bg-slate-900 py-3 text-center text-sm font-semibold text-amber-200 hover:bg-slate-800">Enviar nueva solicitud</a>
            @endif
        @endif
    </div>
</x-guest-layout>
