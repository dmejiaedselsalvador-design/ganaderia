{{--
    Lista de requisitos de la clave que se pone en verde conforme se cumplen.
    Uso: <x-ui.reglas-clave campo="password" confirmacion="password_confirmation" actual="current_password" boton="btn-guardar-clave" />
    (Debe coincidir con la política de AppServiceProvider: 8+, mayúscula, minúscula y número.)
--}}
@props(['campo' => 'password', 'confirmacion' => 'password_confirmation', 'actual' => null, 'boton' => null])
<div x-data="{
        c: '', conf: '', act: '',
        leer() {
            this.c = document.getElementById(@js($campo))?.value ?? '';
            this.conf = document.getElementById(@js($confirmacion))?.value ?? '';
            this.act = @js($actual) ? (document.getElementById(@js($actual))?.value ?? '') : '';
        },
        get reglas() {
            const r = [
                { t: 'Mínimo 8 caracteres', ok: this.c.length >= 8 },
                { t: 'Al menos una letra mayúscula (A-Z)', ok: /[A-ZÁÉÍÓÚÑ]/.test(this.c) },
                { t: 'Al menos una letra minúscula (a-z)', ok: /[a-záéíóúñ]/.test(this.c) },
                { t: 'Al menos un número (0-9)', ok: /\d/.test(this.c) },
            ];
            if (@js((bool) $actual)) r.push({ t: 'Diferente a la clave temporal', ok: this.c.length > 0 && this.c !== this.act });
            r.push({ t: 'Las dos claves coinciden', ok: this.c.length > 0 && this.c === this.conf });
            return r;
        },
        get cumplidas() { return this.reglas.filter(r => r.ok).length },
        get todo() { return this.cumplidas === this.reglas.length },
     }"
     x-init="leer(); ['input', 'change'].forEach(e => document.addEventListener(e, () => leer()))"
     @if ($boton) x-effect="const b = document.getElementById(@js($boton)); if (b) { b.disabled = !todo; b.classList.toggle('opacity-50', !todo); b.classList.toggle('cursor-not-allowed', !todo) }" @endif
     {{ $attributes->merge(['class' => 'rounded-xl border border-slate-100 bg-slate-50 p-4']) }}>

    <!-- Barra de avance -->
    <div class="mb-3 flex items-center gap-3">
        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-200">
            <div class="h-full rounded-full transition-all duration-300"
                 :class="todo ? 'bg-emerald-500' : (cumplidas >= reglas.length - 2 ? 'bg-amber-400' : 'bg-rose-400')"
                 :style="{ width: (cumplidas / reglas.length * 100) + '%' }"></div>
        </div>
        <span class="text-[11px] font-semibold" :class="todo ? 'text-emerald-600' : 'text-slate-400'" x-text="todo ? '¡Clave segura!' : cumplidas + ' de ' + reglas.length"></span>
    </div>

    <ul class="space-y-1.5 text-xs">
        <template x-for="r in reglas" :key="r.t">
            <li class="flex items-center gap-2 transition-colors duration-200" :class="r.ok ? 'font-semibold text-emerald-600' : 'text-slate-400'">
                <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full border transition-all duration-200"
                      :class="r.ok ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-300'">
                    <i class="fa-solid fa-check text-[8px]" x-show="r.ok" x-transition.scale></i>
                </span>
                <span x-text="r.t"></span>
            </li>
        </template>
    </ul>
</div>
