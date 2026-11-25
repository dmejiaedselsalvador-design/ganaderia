{{--
    Botón de Ayuda (?) de la barra superior.
    Muestra la explicación de la pantalla actual (App\Support\Ayuda) y permite enviar una pregunta
    con captura de lo que el usuario está viendo. Las respuestas aparecen aquí mismo.
--}}
@php
    $rutaActual    = request()->route()?->getName();
    $ayuda         = \App\Support\Ayuda::para($rutaActual);
    $misPreguntas  = \App\Models\PreguntaAyuda::where('user_id', $usuario->id)->latest()->take(5)->get();
    $respNuevas    = $misPreguntas->whereNotNull('respondida_at')->whereNull('respuesta_vista_at')->count();
    $porResponder  = $usuario->can('ayuda.responder') ? \App\Models\PreguntaAyuda::pendientes()->count() : 0;
    $listaInicial  = $misPreguntas->map(fn ($p) => [
        'id' => $p->id, 'pantalla' => $p->pantalla, 'pregunta' => $p->pregunta, 'estado' => $p->estado,
        'respuesta' => $p->respuesta, 'fecha' => $p->created_at->format('d/m/Y H:i'),
        'nueva' => $p->respondida_at && ! $p->respuesta_vista_at,
        'captura' => $p->captura_path ? route('ayuda.captura', $p) : null,
    ])->values();
@endphp
<div x-data="{
        abierto: false, escribiendo: false, capturando: false, enviando: false,
        texto: '', captura: null, incluirCaptura: true, error: '', enviado: '',
        nuevas: {{ $respNuevas }}, lista: @js($listaInicial),
        abrir() {
            this.abierto = true;
            if (this.nuevas > 0) { window.axios.post(@js(route('ayuda.vistas'))).catch(() => {}); this.nuevas = 0; }
        },
        async escribir() {
            this.escribiendo = true; this.error = ''; this.enviado = '';
            this.$nextTick(() => this.$refs.texto?.focus());
            if (!this.captura) await this.tomarCaptura();
        },
        async tomarCaptura() {
            this.capturando = true;
            try { this.captura = await window.capturarPantalla(); this.incluirCaptura = true; }
            catch (e) { this.captura = null; this.incluirCaptura = false; }
            finally { this.capturando = false; }
        },
        async enviar() {
            if (this.texto.trim().length < 5) { this.error = 'Escribe un poco más para que podamos ayudarte.'; return; }
            this.enviando = true; this.error = '';
            try {
                const r = await window.axios.post(@js(route('ayuda.store')), {
                    pregunta: this.texto, ruta: @js($rutaActual), url: window.location.href,
                    captura: this.incluirCaptura ? this.captura : null,
                });
                this.lista.unshift({ id: r.data.id, pantalla: @js($ayuda['titulo']), pregunta: this.texto, estado: 'pendiente',
                                     respuesta: null, fecha: 'Ahora', nueva: false, captura: null });
                this.enviado = r.data.mensaje; this.texto = ''; this.captura = null; this.escribiendo = false;
            } catch (e) {
                this.error = e.response?.data?.errors ? Object.values(e.response.data.errors)[0][0]
                           : (e.response?.status === 429 ? 'Enviaste varias preguntas seguidas. Espera un minuto.' : 'No se pudo enviar. Revisa tu conexión e intenta de nuevo.');
            } finally { this.enviando = false; }
        }
     }" @keydown.escape.window="abierto = false" @abrir-ayuda.window="abrir()">

    <button type="button" @click="abrir()" title="Ayuda de esta pantalla" data-ayuda-ignorar
            class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 transition hover:text-slate-900">
        <i class="fa-solid fa-circle-question"></i>
        <span x-show="nuevas > 0" x-cloak class="absolute -right-1 -top-1 h-3 w-3 rounded-full bg-emerald-500 ring-2 ring-white" title="Tienes respuestas nuevas"></span>
        @if ($porResponder)
            <span class="absolute -right-1.5 -top-1.5 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-amber-500 px-1 text-[10px] font-bold text-white ring-2 ring-white"
                  title="{{ $porResponder }} pregunta(s) de usuarios por responder">{{ $porResponder }}</span>
        @endif
    </button>

    <template x-teleport="body">
        <div x-show="abierto" x-cloak data-ayuda-ignorar class="fixed inset-0 z-[60] flex justify-end">
            <div x-show="abierto" x-transition.opacity @click="abierto = false" class="absolute inset-0 bg-black/40"></div>

            <aside x-show="abierto" x-transition:enter="transition duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                   x-transition:leave="transition duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                   class="relative flex h-full w-full max-w-md flex-col border-l border-slate-200 bg-white shadow-2xl">

                <header class="flex items-start justify-between gap-3 border-b border-slate-100 px-5 py-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600"><i class="fa-solid fa-circle-question"></i> Ayuda de esta pantalla</p>
                        <h2 class="mt-0.5 text-lg font-bold text-slate-900">{{ $ayuda['titulo'] }}</h2>
                    </div>
                    <button type="button" @click="abierto = false" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
                </header>

                <div class="flex-1 space-y-5 overflow-y-auto px-5 py-4 text-sm text-slate-700">
                    @if ($porResponder)
                        <a href="{{ route('ayuda.index') }}" class="flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 hover:bg-amber-100">
                            <span><i class="fa-solid fa-inbox"></i> {{ $porResponder }} pregunta(s) de usuarios por responder</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @endif

                    <p class="leading-relaxed">{{ $ayuda['que'] }}</p>

                    @foreach (['pasos' => ['Cómo se usa', 'fa-list-ol'], 'numeros' => ['De dónde salen los números', 'fa-calculator'], 'consejos' => ['Consejos', 'fa-lightbulb']] as $clave => [$titulo, $icono])
                        @if (! empty($ayuda[$clave]))
                            <section>
                                <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500"><i class="fa-solid {{ $icono }} mr-1"></i> {{ $titulo }}</h3>
                                <ul class="space-y-1.5">
                                    @foreach ($ayuda[$clave] as $linea)
                                        <li class="flex gap-2 leading-relaxed"><span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-500"></span><span>{{ $linea }}</span></li>
                                    @endforeach
                                </ul>
                            </section>
                        @endif
                    @endforeach

                    <!-- Pregunta -->
                    <section class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <h3 class="font-bold text-slate-900">¿No te quedó claro?</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Escribe tu duda. Se envía con una captura de esta pantalla para que veamos exactamente lo mismo que tú.</p>

                        <p x-show="enviado" x-cloak class="mt-3 rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700"><i class="fa-solid fa-circle-check"></i> <span x-text="enviado"></span></p>

                        <button type="button" x-show="!escribiendo" @click="escribir()"
                                class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                            <i class="fa-solid fa-pen"></i> Escribir mi pregunta
                        </button>

                        <form x-show="escribiendo" x-cloak @submit.prevent="enviar()" class="mt-3 space-y-3">
                            <textarea x-ref="texto" x-model="texto" rows="4" maxlength="2000" placeholder="Ej. ¿Por qué el corral 3 dice que necesita 700 kg si yo le sirvo 600?"
                                      class="block w-full rounded-xl border-slate-200 text-sm"></textarea>

                            <div class="rounded-xl border border-slate-200 bg-white p-2">
                                <template x-if="capturando">
                                    <p class="py-6 text-center text-xs text-slate-500"><i class="fa-solid fa-spinner fa-spin"></i> Tomando captura de la pantalla…</p>
                                </template>
                                <template x-if="!capturando && captura">
                                    <div>
                                        <img :src="captura" alt="Captura de la pantalla" class="w-full rounded-lg border border-slate-100" :class="!incluirCaptura && 'opacity-30 grayscale'">
                                        <div class="mt-2 flex items-center justify-between text-xs">
                                            <label class="flex items-center gap-2 text-slate-600"><input type="checkbox" x-model="incluirCaptura" class="rounded border-slate-300 text-emerald-600"> Enviar esta captura</label>
                                            <button type="button" @click="tomarCaptura()" class="font-semibold text-emerald-700 hover:underline">
                                                <i class="fa-solid fa-camera"></i> Volver a tomar
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!capturando && !captura">
                                    <button type="button" @click="tomarCaptura()" class="w-full py-3 text-xs font-semibold text-slate-500 hover:text-slate-800"><i class="fa-solid fa-camera"></i> Agregar captura de pantalla</button>
                                </template>
                            </div>

                            <p x-show="error" x-cloak class="text-xs text-rose-600" x-text="error"></p>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="escribiendo = false" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600">Cancelar</button>
                                <button type="submit" :disabled="enviando || capturando" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50">
                                    <span x-show="!enviando"><i class="fa-solid fa-paper-plane"></i> Enviar pregunta</span><span x-show="enviando">Enviando…</span>
                                </button>
                            </div>
                        </form>
                    </section>

                    <!-- Mis preguntas -->
                    <section x-show="lista.length" x-cloak>
                        <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500"><i class="fa-solid fa-comments mr-1"></i> Mis preguntas</h3>
                        <div class="space-y-2">
                            <template x-for="p in lista" :key="p.id">
                                <div class="rounded-xl border p-3 text-xs" :class="p.nueva ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200'">
                                    <div class="flex items-center justify-between gap-2 text-[11px] text-slate-500">
                                        <span><span x-text="p.pantalla"></span> · <span x-text="p.fecha"></span></span>
                                        <span class="rounded-full px-2 py-0.5 font-bold"
                                              :class="{ 'bg-amber-100 text-amber-700': p.estado === 'pendiente', 'bg-emerald-100 text-emerald-700': p.estado === 'respondida', 'bg-slate-100 text-slate-600': p.estado === 'cerrada' }"
                                              x-text="p.nueva ? 'Respuesta nueva' : ({ pendiente: 'Pendiente', respondida: 'Respondida', cerrada: 'Cerrada' })[p.estado]"></span>
                                    </div>
                                    <p class="mt-1.5 text-slate-800" x-text="p.pregunta"></p>
                                    <template x-if="p.respuesta">
                                        <p class="mt-2 whitespace-pre-line rounded-lg bg-white px-3 py-2 text-slate-700 ring-1 ring-slate-100"><i class="fa-solid fa-reply mr-1 text-emerald-600"></i><span x-text="p.respuesta"></span></p>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </section>
                </div>
            </aside>
        </div>
    </template>
</div>
