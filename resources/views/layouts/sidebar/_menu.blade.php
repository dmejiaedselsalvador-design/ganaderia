{{--
    Sidebar plegable compartido por todas las plantas.

    Parámetros:
      $marca     = ['icono' => 'fa-cow', 'titulo' => 'Ganadería<span>System</span>', 'subtitulo' => '...', 'ruta' => 'welcome']
      $secciones = [
          null       => [ ITEM, ITEM ],                                  // sin título (accesos principales)
          'Operación'=> [ ITEM, ['grupo' => 'Hato', 'icono' => 'fa-cow', 'items' => [ITEM, ...]], ... ],
      ]
      ITEM       = [permiso|null, ruta, patrón(es), icono, texto, parámetros de ruta opcionales]
      $tema      = colores (texto, acento, barra, titulo, borde, marca, blur1, blur2, activoBg)
      $pie       = texto del pie

    Comportamiento:
      - Grupos en acordeón: uno abierto a la vez; por defecto el que contiene la pantalla actual.
      - Botón « / » arriba: contrae a barra de íconos (se guarda en la sesión y en la cookie sb_colapsado).
      - Contraído: al pasar el mouse por un ícono se despliega un panel flotante con sus opciones
        (o el nombre, si es opción directa) sin expandir la barra.
      - Buscador: filtra las opciones del menú.
--}}
@php
    $u = auth()->user();
    $url = fn ($i) => route($i[1], $i[5] ?? []);
    $esActivo = fn ($i) => isset($i[5]) ? request()->url() === $url($i) : request()->routeIs(...(array) $i[2]);
    $permitido = fn ($i) => $i[0] === null || $u->can($i[0]);
    $activoBg = $tema['activoBg'] ?? 'bg-white/10';

    // Filtra por permisos y elimina grupos o secciones vacías
    $secciones = collect($secciones)->map(function ($entradas) use ($permitido) {
        return collect($entradas)->map(function ($e) use ($permitido) {
            if (isset($e['grupo'])) {
                $e['items'] = collect($e['items'])->filter($permitido)->values();
                $e['id'] = 'g' . substr(md5($e['grupo']), 0, 6);

                return $e['items']->isEmpty() ? null : $e;
            }

            return $permitido($e) ? $e : null;
        })->filter()->values();
    })->filter(fn ($s) => $s->isNotEmpty());

    // Grupo que contiene la pantalla actual (abierto por defecto)
    $grupoActivo = null;
    foreach ($secciones as $entradas) {
        foreach ($entradas as $e) {
            if (isset($e['grupo']) && $e['items']->contains(fn ($i) => $esActivo($i))) {
                $grupoActivo = $e['id'];
            }
        }
    }
    $txt = fn ($s) => mb_strtolower($s);
@endphp

<aside :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', colapsado ? 'md:w-[76px]' : 'md:w-72']"
       class="app-sidebar fixed inset-y-0 left-0 z-50 flex w-72 flex-col overflow-hidden {{ $tema['texto'] }} transition-all duration-300 ease-in-out md:static md:inset-auto md:translate-x-0 {{ $colapsadoInicial ? 'md:w-[76px]' : 'md:w-72' }}"
       x-data="{
            abierto: @js($grupoActivo), q: '',
            // Panel flotante (solo con la barra contraída en escritorio)
            fly: null, flyTop: 0, timer: null, timerAbrir: null,
            abrirFly(id, el) {
                if (!colapsado || window.innerWidth < 768) return;
                clearTimeout(this.timer); clearTimeout(this.timerAbrir);
                const mostrar = () => {
                    this.flyTop = el.getBoundingClientRect().top;
                    this.fly = id;
                    this.$nextTick(() => {
                        const panel = document.getElementById('fly-' + id);
                        if (panel) this.flyTop = Math.max(8, Math.min(this.flyTop, window.innerHeight - panel.offsetHeight - 8));
                    });
                };
                // Si ya hay un panel abierto, se espera un instante: así el mouse puede cruzar en diagonal hacia el panel sin cambiarlo
                this.fly && this.fly !== id ? this.timerAbrir = setTimeout(mostrar, 220) : mostrar();
            },
            cerrarFly(espera = 220) { clearTimeout(this.timer); this.timer = setTimeout(() => { this.fly = null }, espera) },
            mantenerFly() { clearTimeout(this.timer); clearTimeout(this.timerAbrir) },
       }"
       @keydown.escape.window="fly = null" @resize.window="fly = null" x-effect="if (!colapsado) fly = null">

    <!-- Decoración -->
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full {{ $tema['blur1'] ?? 'bg-emerald-500/15' }} blur-3xl"></div>
        <div class="absolute -bottom-28 -right-20 h-72 w-72 rounded-full {{ $tema['blur2'] ?? 'bg-amber-400/10' }} blur-3xl"></div>
        <div class="absolute inset-y-0 right-0 w-px bg-gradient-to-b from-transparent {{ $tema['borde'] }} to-transparent"></div>
    </div>

    <!-- Encabezado: marca + botón contraer -->
    <div class="relative flex h-[72px] shrink-0 items-center gap-3 px-4" :class="colapsado && 'md:justify-center md:px-0'">
        <a href="{{ route($marca['ruta']) }}" class="flex min-w-0 flex-1 items-center gap-3" :class="colapsado && 'md:hidden'">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border {{ $tema['marca'] ?? 'border-amber-300/30 bg-amber-300/10 text-amber-300' }}">
                <i class="fa-solid {{ $marca['icono'] }}"></i>
            </div>
            <div class="min-w-0 leading-tight">
                <span class="block truncate font-['Playfair_Display'] text-lg font-semibold tracking-wide text-white">{!! $marca['titulo'] !!}</span>
                <span class="block truncate text-[10px] uppercase tracking-[0.3em] {{ $tema['titulo'] }}">{{ $marca['subtitulo'] }}</span>
            </div>
        </a>
        <button type="button" @click="alternarSidebar()" :title="colapsado ? 'Expandir menú' : 'Contraer menú'"
                class="hidden h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-white/70 transition hover:bg-white/10 hover:text-white md:flex">
            <i class="fa-solid" :class="colapsado ? 'fa-angles-right' : 'fa-angles-left'"></i>
        </button>
        <!-- Cerrar en móvil -->
        <button type="button" @click="sidebarOpen = false" class="flex h-10 w-10 items-center justify-center rounded-xl text-white/60 md:hidden" aria-label="Cerrar menú">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Buscador del menú -->
    <div class="relative shrink-0 px-4 pb-3" :class="colapsado && 'md:hidden'">
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs opacity-50"></i>
            <input type="text" x-model="q" placeholder="Buscar en el menú…" @keydown.escape="q = ''"
                   class="w-full rounded-xl border border-white/10 bg-white/5 py-2 pl-9 pr-3 text-sm text-white placeholder-white/40 focus:border-white/20 focus:bg-white/10 focus:outline-none focus:ring-0">
        </div>
    </div>

    <!-- Navegación -->
    <nav class="relative flex-1 overflow-y-auto overflow-x-hidden px-3 pb-4">
        @foreach ($secciones as $tituloSeccion => $entradas)
            @php
                $textosSeccion = $entradas->map(fn ($e) => isset($e['grupo'])
                    ? $txt($e['grupo'] . ' ' . $e['items']->pluck(4)->implode(' ')) : $txt($e[4]))->implode(' ');
            @endphp
            <div class="{{ $loop->first ? '' : 'mt-4' }}" x-show="!q || @js($textosSeccion).includes(q.toLowerCase())">
                @if ($tituloSeccion)
                    <p class="mb-1.5 px-3 text-[10px] font-semibold uppercase tracking-[0.2em] {{ $tema['titulo'] }}" :class="colapsado && 'md:hidden'">{{ $tituloSeccion }}</p>
                @endif
                @unless ($loop->first)
                    <div class="mx-auto mb-3 hidden h-px w-8 bg-white/10" :class="colapsado && 'md:block'"></div>
                @endunless

                <div class="space-y-0.5">
                    @foreach ($entradas as $e)
                        @if (isset($e['grupo']))
                            {{-- Grupo plegable --}}
                            @php $tieneActivo = $grupoActivo === $e['id']; $textosGrupo = $txt($e['grupo'] . ' ' . $e['items']->pluck(4)->implode(' ')); @endphp
                            <div x-show="!q || @js($textosGrupo).includes(q.toLowerCase())"
                                 @mouseenter="abrirFly('{{ $e['id'] }}', $el)" @mouseleave="cerrarFly()">
                                <button type="button" :title="colapsado ? '' : @js($e['grupo'])"
                                        @click="if (colapsado && window.innerWidth >= 768) { fly === '{{ $e['id'] }}' ? fly = null : abrirFly('{{ $e['id'] }}', $el) } else { abierto = (abierto === '{{ $e['id'] }}' ? null : '{{ $e['id'] }}') }"
                                        class="relative flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium transition hover:bg-white/5 hover:text-white {{ $tieneActivo ? 'text-white' : '' }}"
                                        :class="[colapsado && 'md:justify-center md:px-0 {{ $tieneActivo ? $activoBg : '' }}', fly === '{{ $e['id'] }}' && 'bg-white/10 text-white']">
                                    @if ($tieneActivo)<span class="absolute left-0 top-1/2 hidden h-6 w-1 -translate-y-1/2 rounded-r-full {{ $tema['barra'] }}" :class="colapsado && 'md:block'"></span>@endif
                                    <i class="fa-solid {{ $e['icono'] }} w-5 shrink-0 text-center {{ $tieneActivo ? $tema['acento'] : 'opacity-80' }}"></i>
                                    <span class="flex-1 truncate" :class="colapsado && 'md:hidden'">{{ $e['grupo'] }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] opacity-50 transition-transform duration-200"
                                       :class="[(abierto === '{{ $e['id'] }}' || q) && 'rotate-180', colapsado && 'md:hidden']"></i>
                                </button>

                                <div x-show="(abierto === '{{ $e['id'] }}' || q) && !colapsado" x-transition.opacity.duration.150ms @unless($tieneActivo) x-cloak @endunless
                                     class="relative ml-[22px] mt-0.5 space-y-0.5 border-l border-white/10 pl-3">
                                    @foreach ($e['items'] as $it)
                                        @php $act = $esActivo($it); @endphp
                                        <a href="{{ $url($it) }}" x-show="!q || @js($txt($it[4] . ' ' . $e['grupo'])).includes(q.toLowerCase())"
                                           class="flex items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-[13px] transition {{ $act ? $activoBg . ' text-white font-semibold' : 'opacity-75 hover:bg-white/5 hover:text-white hover:opacity-100' }}">
                                            <i class="fa-solid {{ $it[3] }} w-4 text-center text-xs {{ $act ? $tema['acento'] : '' }}"></i>
                                            <span class="truncate">{{ $it[4] }}</span>
                                        </a>
                                    @endforeach
                                </div>

                                {{-- Panel flotante del grupo (barra contraída) --}}
                                <template x-teleport="body">
                                    <div id="fly-{{ $e['id'] }}" x-show="fly === '{{ $e['id'] }}'" x-cloak
                                         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-x-1" x-transition:enter-end="opacity-100 translate-x-0"
                                         x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                         @mouseenter="mantenerFly()" @mouseleave="cerrarFly()"
                                         :style="{ top: flyTop + 'px' }"
                                         class="sidebar-fly fixed left-[84px] z-[60] w-60 rounded-2xl border border-white/10 p-2 shadow-2xl shadow-black/40">
                                        <span class="absolute -left-1.5 top-4 h-3 w-3 rotate-45 border-b border-l border-white/10 sidebar-fly-flecha"></span>
                                        <p class="flex items-center gap-2 px-2.5 pb-2 pt-1 text-[11px] font-bold uppercase tracking-[0.18em] {{ $tema['titulo'] }}">
                                            <i class="fa-solid {{ $e['icono'] }} {{ $tema['acento'] }}"></i> {{ $e['grupo'] }}
                                        </p>
                                        <div class="space-y-0.5">
                                            @foreach ($e['items'] as $it)
                                                @php $act = $esActivo($it); @endphp
                                                <a href="{{ $url($it) }}"
                                                   class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-[13px] transition {{ $act ? $activoBg . ' text-white font-semibold' : 'text-white/75 hover:bg-white/10 hover:text-white' }}">
                                                    <i class="fa-solid {{ $it[3] }} w-4 text-center text-xs {{ $act ? $tema['acento'] : 'opacity-70' }}"></i>
                                                    <span class="truncate">{{ $it[4] }}</span>
                                                    @if ($act)<span class="ml-auto h-1.5 w-1.5 rounded-full {{ $tema['barra'] }}"></span>@endif
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </template>
                            </div>
                        @else
                            {{-- Opción directa --}}
                            @php $act = $esActivo($e); @endphp
                            @php $idDirecto = 'd' . substr(md5($e[1] . $e[4]), 0, 6); @endphp
                            <a href="{{ $url($e) }}" x-show="!q || @js($txt($e[4])).includes(q.toLowerCase())"
                               @mouseenter="abrirFly('{{ $idDirecto }}', $el)" @mouseleave="cerrarFly(60)"
                               class="relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                                      {{ $act ? $activoBg . ' text-white font-semibold' : 'hover:bg-white/5 hover:text-white' }}"
                               :class="colapsado && 'md:justify-center md:px-0'">
                                @if ($act)<span class="absolute left-0 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r-full {{ $tema['barra'] }}"></span>@endif
                                <i class="fa-solid {{ $e[3] }} w-5 shrink-0 text-center {{ $act ? $tema['acento'] : 'opacity-80' }}"></i>
                                <span class="truncate" :class="colapsado && 'md:hidden'">{{ $e[4] }}</span>
                            </a>
                            <template x-teleport="body">
                                <div id="fly-{{ $idDirecto }}" x-show="fly === '{{ $idDirecto }}'" x-cloak x-transition.opacity.duration.120ms
                                     :style="{ top: (flyTop + 4) + 'px' }"
                                     class="sidebar-fly pointer-events-none fixed left-[84px] z-[60] whitespace-nowrap rounded-xl border border-white/10 px-3 py-2 text-[13px] font-semibold text-white shadow-xl shadow-black/40">
                                    <span class="absolute -left-1.5 top-1/2 h-3 w-3 -translate-y-1/2 rotate-45 border-b border-l border-white/10 sidebar-fly-flecha"></span>
                                    {{ $e[4] }}
                                </div>
                            </template>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach

        <p x-show="q" x-cloak class="px-3 pt-4 text-xs opacity-50">¿No aparece? Revisa que tengas permiso para ese módulo.</p>
    </nav>

    <!-- Pie -->
    <div class="relative shrink-0 border-t border-white/5 px-4 py-3" :class="colapsado && 'md:hidden'">
        <p class="truncate text-[10px] uppercase tracking-[0.25em] {{ $tema['titulo'] }}">{{ $pie }}</p>
    </div>
</aside>
