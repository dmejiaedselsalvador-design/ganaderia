@php use App\Models\SolicitudClave as S; @endphp
<x-app-layout>
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Administración</p>
                <h1 class="mt-1 flex items-center gap-3 text-2xl font-extrabold tracking-tight text-slate-900">
                    Solicitudes de cambio de clave
                    @if ($pendientes)
                        <span class="rounded-full bg-rose-600 px-2.5 py-0.5 text-sm font-bold text-white">{{ $pendientes }}</span>
                    @endif
                </h1>
                <p class="mt-1 text-sm text-slate-500">Usuarios que olvidaron su clave. Si apruebas, el usuario verá una clave temporal en su navegador; tú no la verás.</p>
            </div>
            <div class="flex rounded-xl border border-slate-200 bg-white p-1 text-xs font-semibold shadow-sm">
                @foreach (['pendientes' => 'Pendientes', 'todas' => 'Historial'] as $k => $t)
                    <a href="{{ route('solicitudes.index', ['ver' => $k]) }}" class="rounded-lg px-3 py-1.5 {{ $filtro === $k ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-900' }}">{{ $t }}</a>
                @endforeach
            </div>
        </div>

        <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <i class="fa-solid fa-shield-halved mt-0.5"></i>
            <span><strong>Antes de aprobar, confirma con la persona</strong> (llamada o en persona) que ella hizo la solicitud y pídele el folio que ve en su pantalla. Así evitas que alguien más tome su cuenta.</span>
        </div>

        <div class="space-y-4">
            @forelse ($solicitudes as $s)
                @php $u = $s->user; $vencida = $s->vencida(); @endphp
                <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm" x-data="{ denegar: false }">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
                        <div class="flex flex-1 items-center gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-bold text-slate-600">{{ $u->iniciales() }}</div>
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900">{{ $u->name }}
                                    <span class="ml-1 text-xs font-normal text-slate-500">{{ $u->roles->sortByDesc('nivel')->first()->display_name ?? '' }}</span>
                                </p>
                                <p class="truncate text-xs text-slate-500">{{ $u->email }}</p>
                                <p class="mt-1 text-[11px] text-slate-400">
                                    <span class="font-mono font-semibold text-slate-600">{{ $s->folio() }}</span> ·
                                    {{ $s->updated_at->diffForHumans() }} · IP {{ $s->ip }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            @if ($s->estado === S::PENDIENTE && ! $vencida)
                                <form method="POST" action="{{ route('solicitudes.aprobar', $s) }}"
                                      data-confirm="¿Aprobar la solicitud {{ $s->folio() }} de {{ $u->name }}? Su clave actual dejará de funcionar." data-boton="Aprobar">
                                    @csrf
                                    <button class="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"><i class="fa-solid fa-check"></i> Aprobar</button>
                                </form>
                                <button type="button" @click="denegar = !denegar" class="flex items-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50">
                                    <i class="fa-solid fa-xmark"></i> Denegar
                                </button>
                            @else
                                @php
                                    [$txt, $cls] = match (true) {
                                        $s->estado === S::PENDIENTE => ['Vencida', 'bg-slate-100 text-slate-500 border-slate-200'],
                                        $s->estado === S::APROBADA  => [$vencida ? 'Aprobada · no la vio' : 'Aprobada · esperando que la vea', 'bg-blue-50 text-blue-700 border-blue-200'],
                                        $s->estado === S::ENTREGADA => ['Aprobada · clave entregada', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                        default                     => ['Denegada', 'bg-rose-50 text-rose-700 border-rose-200'],
                                    };
                                @endphp
                                <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $cls }}">{{ $txt }}</span>
                                @if ($s->atendidaPor)
                                    <span class="text-[11px] text-slate-400">por {{ $s->atendidaPor->name }} · {{ $s->atendida_at?->diffForHumans() }}</span>
                                @endif
                            @endif
                        </div>
                    </div>

                    @if ($s->estado === S::PENDIENTE && ! $vencida)
                        <form x-show="denegar" x-cloak method="POST" action="{{ route('solicitudes.denegar', $s) }}" class="mt-4 space-y-3 rounded-xl border border-rose-100 bg-rose-50 p-4">
                            @csrf
                            <input name="motivo" maxlength="255" placeholder="Motivo (opcional, lo verá el usuario)" class="w-full rounded-xl border-slate-200 text-sm">
                            <label class="flex items-start gap-2 text-sm text-rose-800">
                                <input type="checkbox" name="bloquear" value="1" class="mt-0.5 h-4 w-4 rounded border-rose-300 text-rose-600">
                                <span><strong>Bloquear a este usuario:</strong> ya no podrá enviar solicitudes de cambio de clave (puedes desbloquearlo después).</span>
                            </label>
                            <button class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">Confirmar denegación</button>
                        </form>
                    @endif

                    @if ($s->estado === S::DENEGADA && $s->motivo)
                        <p class="mt-3 text-xs text-slate-500">Motivo: {{ $s->motivo }}</p>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                    <i class="fa-solid fa-inbox text-3xl text-slate-300"></i>
                    <p class="mt-3 text-sm text-slate-500">{{ $filtro === 'pendientes' ? 'No hay solicitudes pendientes.' : 'Aún no hay solicitudes.' }}</p>
                </div>
            @endforelse
            {{ $solicitudes->links() }}
        </div>

        <!-- Usuarios bloqueados -->
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <h2 class="flex items-center gap-2 text-sm font-bold text-slate-800"><i class="fa-solid fa-ban text-rose-500"></i> Usuarios con solicitudes bloqueadas</h2>
            <div class="mt-3 divide-y divide-slate-100">
                @forelse ($bloqueados as $b)
                    <div class="flex items-center justify-between py-2.5 text-sm">
                        <span class="text-slate-700">{{ $b->name }} <span class="text-xs text-slate-400">{{ $b->email }}</span></span>
                        <form method="POST" action="{{ route('solicitudes.bloqueo', $b) }}">
                            @csrf @method('PATCH')
                            <button class="rounded-lg border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50">Desbloquear</button>
                        </form>
                    </div>
                @empty
                    <p class="py-2 text-xs text-slate-400">Ningún usuario bloqueado.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
