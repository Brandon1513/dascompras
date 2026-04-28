<div class="min-h-screen py-6" style="background: linear-gradient(160deg, #f8f5ff 0%, #f1f5f9 50%, #f8f5ff 100%);">
<div class="max-w-5xl px-4 mx-auto space-y-4 sm:px-6 lg:px-8">

    {{-- ══ ERRORES ══════════════════════════════════════════════════════ --}}
    @error('general')
        <div class="flex items-center gap-2 p-4 text-sm font-medium text-red-800 border border-red-200 rounded-xl bg-red-50">
            ⚠️ {{ $message }}
        </div>
    @enderror

    @if(session('status_compras'))
        <div class="flex items-center gap-2 p-4 text-sm font-medium border text-emerald-800 border-emerald-200 rounded-xl bg-emerald-50">
            ✅ {{ session('status_compras') }}
        </div>
    @endif

    {{-- ══ HEADER DE REVISIÓN ══════════════════════════════════════════ --}}
    <div class="overflow-hidden bg-white border shadow-sm rounded-xl border-violet-200">
        <div class="flex items-center gap-3 px-5 py-3"
             style="background: linear-gradient(90deg, #4A1660 0%, #7c3aed 100%);">
            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-white/10">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-white">Revisión — Coordinación de Compras</p>
                <p class="text-xs text-white/60 mt-0.5">Verifica la información, ajusta precios, impuestos y archivos antes de aprobar o rechazar.</p>
            </div>
        </div>
    </div>

    {{-- ══ RESUMEN DE LA REQUISICIÓN ═══════════════════════════════════ --}}
    <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
             style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
            <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-xs font-bold tracking-widest text-white uppercase">Información de la requisición</h3>
        </div>
        <div class="p-5">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-gray-900">{{ $requisicion->folio }}</h2>
                        @if($requisicion->es_pago_factura)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-violet-100 text-violet-700">Pago de factura</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Emitida el {{ optional($requisicion->fecha_emision)->format('d/m/Y') }}
                        por <span class="font-semibold text-gray-600">{{ $requisicion->solicitante?->name }}</span>
                    </p>
                </div>
                @if($requisicion->urgencia === 'urgente')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-red-100 text-red-700">🔴 Urgente</span>
                @endif
            </div>

            <dl class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
                <div>
                    <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Departamento</dt>
                    <dd class="font-semibold text-gray-800">{{ $requisicion->departamentoRef?->nombre ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Centro de costos</dt>
                    <dd class="font-semibold text-gray-800">{{ $requisicion->centroCostoRef?->nombre ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Urgencia</dt>
                    <dd class="font-semibold text-gray-800 capitalize">{{ $requisicion->urgencia }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tipo</dt>
                    <dd>
                        @if($requisicion->es_pago_factura)
                            <span class="text-xs font-semibold text-violet-700">Pago de factura</span>
                        @else
                            <span class="text-xs text-gray-600">Requisición de compra</span>
                        @endif
                    </dd>
                </div>
            </dl>

            @if($requisicion->justificacion)
            <div class="pt-4 mt-4 border-t border-gray-100">
                <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Justificación del solicitante</dt>
                <dd class="px-4 py-3 text-sm leading-relaxed text-gray-700 whitespace-pre-line border border-gray-100 bg-gray-50 rounded-xl">{{ $requisicion->justificacion }}</dd>
            </div>
            @endif
        </div>
    </div>

    {{-- ══ PARTIDAS EDITABLES ═══════════════════════════════════════════ --}}
    <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100"
             style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <h3 class="text-xs font-bold tracking-widest text-white uppercase">Partidas</h3>
            </div>
            <span class="text-[10px] text-white/50">
                ✎ Campos editables por Compras
            </span>
        </div>

        <div class="divide-y divide-gray-50">
            @foreach($items as $i => $row)
            <div wire:key="item-{{ $i }}" class="p-5 space-y-4">

                {{-- Número + info base del solicitante --}}
                <div class="flex items-start gap-3">
                    <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white rounded-full shrink-0"
                          style="background: linear-gradient(135deg, #4A1660, #7c3aed);">
                        {{ $i + 1 }}
                    </span>
                    <div class="grid flex-1 grid-cols-2 gap-3 p-3 text-sm border border-gray-100 md:grid-cols-4 bg-gray-50 rounded-xl">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Descripción</p>
                            <p class="font-semibold text-gray-800">{{ $row['descripcion'] }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Cantidad / Unidad</p>
                            <p class="font-semibold text-gray-800">
                                {{ rtrim(rtrim(number_format($row['cantidad'], 3, '.', ''), '0'), '.') }}
                                {{ $row['unidad_label'] ?? '' }}
                            </p>
                        </div>
                        @if($row['link_compra'])
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Link referencia</p>
                            <a href="{{ $row['link_compra'] }}" target="_blank"
                               class="text-xs text-indigo-500 hover:underline truncate block max-w-[150px]">
                                Ver link →
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Campos editables --}}
                <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                    <div>
                        <label class="block mb-1 text-[10px] font-bold text-violet-500 uppercase tracking-wider">
                            Precio unitario ✎
                        </label>
                        <div class="relative">
                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-300 text-xs font-bold">$</span>
                            <input type="number" step="0.01" min="0"
                                   wire:model.debounce.400ms="items.{{ $i }}.precio_unitario"
                                   class="w-full pl-6 text-sm text-right border-gray-200 rounded-lg shadow-sm focus:ring-violet-500 focus:border-violet-500">
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 text-[10px] font-bold text-violet-500 uppercase tracking-wider">
                            Impuesto ✎
                        </label>
                        <select wire:model="items.{{ $i }}.tipo_impuesto_id"
                                class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-violet-500 focus:border-violet-500">
                            <option value="">Sin impuesto</option>
                            @foreach($tipos_impuesto as $ti)
                                <option value="{{ $ti['id'] }}">{{ $ti['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 text-[10px] font-bold text-violet-500 uppercase tracking-wider">
                            Proveedor ✎
                        </label>
                        <input type="text" wire:model.lazy="items.{{ $i }}.proveedor_sugerido"
                               placeholder="Nombre del proveedor"
                               class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-violet-500 focus:border-violet-500">
                    </div>

                    {{-- Mini totales --}}
                    <div class="flex flex-col justify-end">
                        <div class="px-3 py-2 space-y-1 text-xs border rounded-xl border-violet-100 bg-violet-50/50">
                            <div class="flex justify-between text-gray-400">
                                <span>Subtotal</span>
                                <span>${{ number_format($row['subtotal'] ?? 0, 2) }}</span>
                            </div>
                            @if(($row['monto_impuesto'] ?? 0) > 0)
                            <div class="flex justify-between text-gray-400">
                                <span>Impuesto</span>
                                <span>${{ number_format($row['monto_impuesto'] ?? 0, 2) }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between pt-1 font-bold border-t border-violet-200" style="color: #4A1660">
                                <span>Total</span>
                                <span>${{ number_format($row['total_item'] ?? 0, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Archivos --}}
                <div>
                    <label class="block mb-2 text-[10px] font-bold text-violet-500 uppercase tracking-wider">
                        Archivos adjuntos ✎
                    </label>

                    @if(!empty($row['archivos_existentes']))
                    <div class="flex flex-wrap gap-2 mb-2">
                        @foreach($row['archivos_existentes'] as $arch)
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-gray-200 bg-gray-50 text-xs">
                            <span>{{ $arch['icono'] ?? '📎' }}</span>
                            <a href="{{ $arch['url'] }}" target="_blank"
                               class="text-indigo-500 hover:underline max-w-[140px] truncate">
                                {{ $arch['nombre_original'] }}
                            </a>
                            <span class="text-gray-300">{{ $arch['tipo_label'] }}</span>
                            <button type="button" wire:click="removeArchivoExistente({{ $i }}, {{ $arch['id'] }})"
                                    class="ml-1 text-gray-300 transition-colors hover:text-red-500">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @php $totalArch = count($row['archivos_existentes'] ?? []); @endphp
                    @if($totalArch < 5)
                    <label class="inline-flex items-center gap-2 px-3 py-2 text-xs transition-colors border border-dashed rounded-lg cursor-pointer border-violet-200 bg-violet-50/50 hover:bg-violet-100 text-violet-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        Adjuntar cotización / archivo
                        <input type="file" multiple wire:model="archivos_nuevos.{{ $i }}"
                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" class="hidden">
                    </label>
                    <div wire:loading wire:target="archivos_nuevos.{{ $i }}" class="inline-flex items-center gap-1 ml-2 text-xs text-violet-500">
                        <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Subiendo...
                    </div>
                    @endif
                </div>

            </div>
            @endforeach
        </div>

        {{-- Totales generales --}}
        <div class="flex justify-end px-5 py-4 border-t border-gray-100 bg-gray-50">
            <div class="w-full max-w-xs space-y-2">
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Subtotal</span>
                    <span class="font-semibold text-gray-700">${{ number_format($subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Impuestos</span>
                    <span class="font-semibold text-gray-700">${{ number_format($total_impuestos, 2) }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-200">
                    <span class="font-bold text-gray-800">Total</span>
                    <span class="text-xl font-black" style="color: #4A1660">${{ number_format($total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ PANEL DE COMPRAS ════════════════════════════════════════════ --}}
    <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100 bg-violet-50">
            <span class="text-sm">⚙️</span>
            <h3 class="text-xs font-bold tracking-widest uppercase text-violet-700">Información de Compras</h3>
        </div>
        <div class="p-5 space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Método de pago</label>
                    <select wire:model="metodo_pago"
                            class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-violet-500 focus:border-violet-500">
                        <option value="">— Sin asignar —</option>
                        <option value="tarjeta">💳 Tarjeta</option>
                        <option value="transferencia">🏦 Transferencia</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                    Observaciones de compras
                    <span class="ml-1 font-normal text-gray-300 normal-case">(visible para el solicitante)</span>
                </label>
                <textarea wire:model.lazy="observaciones_compras" rows="3"
                          placeholder="Notas sobre la cotización, condiciones del proveedor, etc."
                          class="w-full text-sm border-gray-200 rounded-lg shadow-sm resize-none focus:ring-violet-500 focus:border-violet-500"></textarea>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" wire:click="guardarCambios"
                        wire:loading.attr="disabled" wire:target="guardarCambios"
                        class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold transition border rounded-lg text-violet-700 bg-violet-100 border-violet-200 hover:bg-violet-200 disabled:opacity-50">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    <span wire:loading.remove wire:target="guardarCambios">Guardar cambios</span>
                    <span wire:loading wire:target="guardarCambios">Guardando…</span>
                </button>
                <p class="text-xs text-gray-400">Guarda sin cambiar el estado de la requisición.</p>
            </div>
        </div>
    </div>

    {{-- ══ DECISIÓN FINAL ══════════════════════════════════════════════ --}}
    @if($requisicion->estado === 'en_revision_compras')
    <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
             style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
            <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-xs font-bold tracking-widest text-white uppercase">Decisión de revisión</h3>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                {{-- Aprobar --}}
                <div class="p-5 space-y-3 border-2 rounded-xl border-emerald-200 bg-emerald-50">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-sm font-bold text-emerald-800">Aprobar revisión</span>
                    </div>
                    <p class="text-xs leading-relaxed text-emerald-600">
                        La información es correcta. La requisición pasará al flujo de aprobaciones por monto.
                    </p>
                    <button type="button" wire:click="aprobarRevision"
                            wire:loading.attr="disabled" wire:target="aprobarRevision"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 shadow-md hover:shadow-lg transition-all disabled:opacity-50">
                        <span wire:loading.remove wire:target="aprobarRevision">
                            ✅ Aprobar y enviar a aprobaciones
                        </span>
                        <span wire:loading wire:target="aprobarRevision" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Procesando…
                        </span>
                    </button>
                </div>

                {{-- Rechazar --}}
                <div class="p-5 space-y-3 border-2 rounded-xl border-rose-200 bg-rose-50">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-rose-100">
                            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <span class="text-sm font-bold text-rose-800">Rechazar revisión</span>
                    </div>
                    <p class="text-xs leading-relaxed text-rose-600">
                        La requisición tiene errores. Se notificará al solicitante para que haga correcciones.
                    </p>

                    @if(!$mostrarFormRechazo)
                    <button type="button" wire:click="toggleFormRechazo"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-rose-600 rounded-xl hover:bg-rose-700 shadow-md hover:shadow-lg transition-all">
                        ❌ Rechazar requisición
                    </button>
                    @else
                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-rose-600 uppercase tracking-wider">
                            Motivo del rechazo <span class="text-red-500">*</span>
                            <span class="ml-1 font-normal normal-case text-rose-400">(el solicitante verá este mensaje)</span>
                        </label>
                        <textarea wire:model.lazy="motivo_rechazo" rows="4"
                                  placeholder="Describe qué debe corregir el solicitante..."
                                  class="w-full text-sm bg-white shadow-sm resize-none rounded-xl border-rose-200 focus:ring-rose-500 focus:border-rose-500"></textarea>
                        @error('motivo_rechazo')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="flex gap-2 pt-1">
                            <button type="button" wire:click="rechazarRevision"
                                    wire:loading.attr="disabled" wire:target="rechazarRevision"
                                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-rose-600 rounded-xl hover:bg-rose-700 transition disabled:opacity-50">
                                <span wire:loading.remove wire:target="rechazarRevision">Confirmar rechazo</span>
                                <span wire:loading wire:target="rechazarRevision">Enviando…</span>
                            </button>
                            <button type="button" wire:click="toggleFormRechazo"
                                    class="px-4 py-2.5 text-sm font-semibold text-gray-500 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                                Cancelar
                            </button>
                        </div>
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
    @endif

</div>
</div>