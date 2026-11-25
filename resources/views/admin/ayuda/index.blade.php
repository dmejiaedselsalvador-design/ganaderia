@php use App\Models\PreguntaAyuda as P; @endphp
<x-app-layout>
    <div class="mx-auto max-w-6xl space-y-6" x-data="{ foto: null }">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Administración</p>
                <h1 class="mt-1 flex items-center gap-3 text-2xl font-extrabold tracking-tight text-slate-900">
                    Preguntas de usuarios
                    @if ($conteo[P::PENDIENTE] ?? 0)
                        <span class="rounded-full bg-amber-500 px-2.5 py-0.5 text-sm font-bold text-white">{{ $conteo[P::PENDIENTE] }}</span>
                    @endif
                </h1>
                <p class="mt-1 text-sm text-slate-500">Dudas enviadas desde el botón <i class="fa-solid fa-circle-question"></i> de cada pantalla, con la captura de lo que la persona estaba viendo. Tu respuesta le aparece en su panel de Ayuda.</p>
            </div>
            <div class="flex rounded-xl border border-slate-200 bg-white p-1 text-xs font-semibold shadow-sm">
                @foreach (P::ESTADOS + ['todas' => 'Todas'] as $k => $t)
                    <a href="{{ route('ayuda.index', ['estado' => $k]) }}" class="rounded-lg px-3 py-1.5 {{ $estado === $k ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-900' }}">
                        {{ $t }}@if ($k !== 'todas' && ($conteo[$k] ?? 0)) <span class="opacity-70">({{ $conteo[$k] }})</span>@endif
                    </a>
                @endforeach
            </div>
        </div>

        <x-ui.alertas />

        <div class="space-y-4">
            @forelse ($preguntas as $p)
                <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-5 lg:flex-row">
                        <!-- Captura -->
                        <div class="lg:w-72 lg:shrink-0">
                            @if ($p->captura_path)
                                <button type="button" @click="foto = @js(route('ayuda.captura', $p))" class="group block w-full overflow-hidden rounded-xl border border-slate-200">
                                    <img src="{{ route('ayuda.captura', $p) }}" alt="Captura de {{ $p->pantalla }}" loading="lazy" class="w-full transition group-hover:opacity-80">
                                </button>
                                <p class="mt-1 text-center text-[11px] text-slate-400">Clic para ampliar</p>
                            @else
                                <div class="flex h-32 items-center justify-center rounded-xl border border-dashed border-slate-200 text-xs text-slate-400">Sin captura</div>
                            @endif
                        </div>

                        <!-- Pregunta y respuesta -->
                        <div class="min-w-0 flex-1 space-y-3">
                            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                <span class="font-semibold text-slate-900">{{ $p->usuario->name ?? 'Usuario eliminado' }}</span>
                                <span>·</span>
                                <span>{{ $p->created_at->format('d/m/Y H:i') }}</span>
                                <span>·</span>
                                @if ($p->url)
                                    <a href="{{ $p->url }}" class="font-semibold text-emerald-700 hover:underline" title="Abrir la pantalla"><i class="fa-solid fa-arrow-up-right-from-square"></i> {{ $p->pantalla }}</a>
                                @else
                                    <span>{{ $p->pantalla }}</span>
                                @endif
                                <span class="ml-auto rounded-full px-2 py-0.5 text-[11px] font-bold
                                    {{ ['pendiente' => 'bg-amber-100 text-amber-700', 'respondida' => 'bg-emerald-100 text-emerald-700', 'cerrada' => 'bg-slate-100 text-slate-600'][$p->estado] ?? '' }}">
                                    {{ P::ESTADOS[$p->estado] ?? $p->estado }}
                                </span>
                            </div>

                            <p class="whitespace-pre-line rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-800">{{ $p->pregunta }}</p>

                            @if ($p->respuesta)
                                <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 text-sm text-slate-700">
                                    <p class="mb-1 text-[11px] font-semibold text-emerald-700"><i class="fa-solid fa-reply"></i> {{ $p->respondidaPor->name ?? '' }} · {{ $p->respondida_at?->format('d/m/Y H:i') }}
                                        · {{ $p->respuesta_vista_at ? 'leída' : 'aún no la lee' }}</p>
                                    <p class="whitespace-pre-line">{{ $p->respuesta }}</p>
                                </div>
                            @endif

                            @if ($p->estado !== P::CERRADA)
                                <form method="POST" action="{{ route('ayuda.responder', $p) }}" class="space-y-2">
                                    @csrf @method('PUT')
                                    <textarea name="respuesta" rows="3" required maxlength="4000" class="block w-full rounded-xl border-slate-200 text-sm"
                                              placeholder="{{ $p->respuesta ? 'Agregar o corregir la respuesta…' : 'Escribe la respuesta…' }}">{{ $p->respuesta }}</textarea>
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <button type="submit" name="estado" value="{{ P::CERRADA }}" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Responder y cerrar</button>
                                        <x-ui.btn icono="fa-paper-plane">Enviar respuesta</x-ui.btn>
                                    </div>
                                </form>
                                @if ($p->estado === P::RESPONDIDA)
                                    <form method="POST" action="{{ route('ayuda.cerrar', $p) }}" class="text-right">
                                        @csrf @method('PATCH')
                                        <button class="text-xs font-semibold text-slate-500 hover:text-slate-800"><i class="fa-solid fa-check"></i> Marcar como cerrada</button>
                                    </form>
                                @endif
                            @endif

                            <p class="truncate text-[11px] text-slate-400" title="{{ $p->navegador }}">{{ $p->navegador }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-10 text-center text-sm text-slate-500">
                    <i class="fa-solid fa-circle-check mb-2 text-2xl text-emerald-500"></i>
                    <p>No hay preguntas {{ $estado === 'todas' ? '' : strtolower(P::ESTADOS[$estado] ?? '') . 's' }}.</p>
                </div>
            @endforelse
        </div>

        {{ $preguntas->links() }}

        <!-- Visor de captura -->
        <div x-show="foto" x-cloak @click="foto = null" @keydown.escape.window="foto = null" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/80 p-4">
            <img :src="foto" alt="Captura ampliada" class="max-h-full max-w-full rounded-xl shadow-2xl">
        </div>
    </div>
</x-app-layout>
