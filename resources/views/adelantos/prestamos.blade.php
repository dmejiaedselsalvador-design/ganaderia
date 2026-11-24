<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 shadow-sm">
                <i class="fa-solid fa-hand-holding-dollar text-base"></i>
            </div>
            <div>
                <h1 class="text-lg font-semibold text-slate-800 leading-tight">
                    @if($proveedor)
                        Adelanto a {{ $proveedor->nombreProveedor }}
                    @else
                        Nuevo Adelanto / Préstamo
                    @endif
                </h1>
                <p class="text-xs text-slate-500">Registrar entrega de dinero o material a proveedor · MXN</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-5">

            {{-- ── Errores de Validación ── --}}
            @if ($errors->any())
                <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation mt-0.5 flex-shrink-0"></i>
                    <div>
                        <p class="text-sm font-semibold mb-1">Por favor revisa los campos obligatorios:</p>
                        <ul class="list-disc list-inside text-xs space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- ── Formulario ── --}}
            <form action="{{ route('adelantos.store') }}"
                  method="POST"
                  id="form-adelanto-proveedor">
                @csrf

                {{-- SECCIÓN 1: Datos Generales --}}
                <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-slate-100 p-6 mb-5">
                    <h2 class="text-base font-semibold text-slate-800 mb-5 flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice-dollar text-indigo-500"></i>
                        Datos Generales del Adelanto
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        {{-- ── Proveedor ── --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Proveedor <span class="text-red-500">*</span>
                            </label>

                            @if($proveedor)
                                {{-- Proveedor fijo: llega desde la guía o el sidebar --}}
                                <input type="text"
                                       value="{{ $proveedor->nombreContacto ?? $proveedor->nombreProveedor }}"
                                       disabled
                                       class="mt-1 block w-full rounded-xl border-slate-200 bg-slate-100 shadow-sm text-sm cursor-not-allowed text-slate-500">
                                <input type="hidden" name="supplier_id" value="{{ $proveedor->id }}">
                                <p class="text-xs text-slate-400 mt-1">{{ $proveedor->nombreProveedor }}</p>
                            @else
                                {{-- Sin proveedor: seleccionar de la lista --}}
                                <select name="supplier_id" required
                                        class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="">— Seleccionar proveedor —</option>
                                    @foreach($proveedores as $prov)
                                        <option value="{{ $prov->id }}"
                                            {{ old('supplier_id') == $prov->id ? 'selected' : '' }}>
                                            {{ $prov->nombreProveedor }}
                                            @if($prov->nombreContacto && $prov->nombreContacto !== $prov->nombreProveedor)
                                                · {{ $prov->nombreContacto }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>

                        {{-- ── Fecha de Adelanto ── --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Fecha de Adelanto <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="advance_date"
                                   value="{{ old('advance_date', date('Y-m-d')) }}"
                                   required
                                   class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('advance_date')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- SECCIÓN 2: Concepto y Monto --}}
                <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-slate-100 p-6 mb-5">
                    <h2 class="text-base font-semibold text-slate-800 mb-5 flex items-center gap-2">
                        <i class="fa-solid fa-peso-sign text-indigo-500"></i>
                        Concepto y Monto (MXN)
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        {{-- Concepto --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Concepto / Descripción <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="concept" id="input_concept"
                                   value="{{ old('concept') }}"
                                   placeholder="Ej. Préstamo personal, 2 sacos de concentrado, etc."
                                   required
                                   class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <p class="text-xs text-slate-400 mt-1">Describe qué se le entrega al proveedor (dinero o productos).</p>
                            @error('concept')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Monto --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Monto Equivalente ($) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 text-sm font-bold">$</span>
                                <input type="number" step="0.01" min="0.01" name="amount" id="input_amount"
                                       value="{{ old('amount') }}"
                                       placeholder="0.00"
                                       required
                                       class="pl-7 block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <p class="text-xs text-slate-400 mt-1">Valor monetario total del adelanto.</p>
                            @error('amount')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Resumen total en tiempo real --}}
                <div class="bg-white rounded-2xl border border-indigo-100 shadow-sm p-4 mb-5 flex items-center justify-between">
                    <span class="text-sm text-slate-600">Total a registrar como adelanto al proveedor:</span>
                    <span class="text-2xl font-bold text-indigo-600">$<span id="display-total">0.00</span> <small class="text-xs font-normal text-slate-400">MXN</small></span>
                </div>

                {{-- Botones --}}
                <div class="flex flex-col sm:flex-row justify-end gap-3">
                    <a href="{{ route('adelantos.index') }}"
                       class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-100 transition text-center">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow transition">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Guardar Adelanto
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- Script: total en tiempo real --}}
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const inputAmount  = document.getElementById("input_amount");
            const displayTotal = document.getElementById("display-total");

            if (!inputAmount || !displayTotal) return;

            function refresh() {
                const val = parseFloat(inputAmount.value) || 0;
                displayTotal.textContent = val.toLocaleString('es-MX', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            inputAmount.addEventListener("input", refresh);
            refresh(); // Si hay valor previo (old())
        });
    </script>

</x-app-layout>
