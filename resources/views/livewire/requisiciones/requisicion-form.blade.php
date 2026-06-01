<div class="min-h-screen py-6" style="background: linear-gradient(160deg, #f8f5ff 0%, #f1f5f9 50%, #f8f5ff 100%);">
<div class="max-w-5xl px-4 mx-auto space-y-4 sm:px-6 lg:px-8">

    @error('general')
        <div class="flex items-center gap-2 p-4 text-sm font-medium text-red-800 border border-red-200 rounded-xl bg-red-50">
            ⚠️ {{ $message }}
        </div>
    @enderror

    {{-- ══ LEYENDA ══════════════════════════════════════════════════════ --}}
    <div class="flex items-start gap-3 px-4 py-3 border rounded-xl border-amber-200/80 bg-amber-50/80">
        <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <p class="text-xs font-medium text-amber-800">
            El seguimiento al flujo de aprobación es responsabilidad del solicitante y superiores.
        </p>
    </div>

    {{-- Banner rechazo por compras --}}
    @if($estado_actual === 'rechazada_compras')
    <div class="overflow-hidden border border-orange-200 rounded-xl bg-orange-50">
        <div class="flex items-center gap-2 px-4 py-2 bg-orange-100 border-b border-orange-200">
            <span class="text-sm">↩️</span>
            <span class="text-xs font-bold tracking-wider text-orange-800 uppercase">Requisición rechazada — Requiere correcciones</span>
        </div>
        @if(isset($requisicion) && $requisicion->motivo_rechazo_compras)
        <div class="px-4 py-3">
            <p class="text-sm text-orange-700 whitespace-pre-line">{{ $requisicion->motivo_rechazo_compras }}</p>
            @if($requisicion->revisadoPor)
            <p class="mt-2 text-xs text-orange-400">
                Revisado por {{ $requisicion->revisadoPor->name }}
                @if($requisicion->revisado_en)· {{ $requisicion->revisado_en->format('d/m/Y H:i') }}@endif
            </p>
            @endif
        </div>
        @endif
    </div>
    @endif

    {{-- ══ DATOS GENERALES ══════════════════════════════════════════════ --}}
    <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
             style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
            <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-xs font-bold tracking-widest text-white uppercase">Datos generales</h3>
        </div>
        <div class="p-5 space-y-4">

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Fecha de elaboración</label>
                    <input type="date" wire:model.live="fecha_emision"
                           class="w-full rounded-lg border-gray-200 text-sm shadow-sm focus:ring-purple-500 focus:border-purple-500
                                  @error('fecha_emision') border-red-400 @enderror">
                    @error('fecha_emision') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Solicitante</label>
                    <input type="text" value="{{ $solicitante_nombre }}" readonly
                           class="w-full text-sm text-gray-400 border-gray-100 rounded-lg shadow-sm cursor-not-allowed bg-gray-50">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                        Departamento solicitante <span class="text-red-400">*</span>
                    </label>
                    <select wire:model.live="departamento_id"
                            class="w-full rounded-lg border-gray-200 text-sm shadow-sm focus:ring-purple-500 focus:border-purple-500
                                   @error('departamento_id') border-red-400 @enderror">
                        <option value="">— Selecciona —</option>
                        @foreach ($departamentos as $dep)
                            <option value="{{ $dep['id'] }}">{{ $dep['nombre'] }}</option>
                        @endforeach
                    </select>
                    @error('departamento_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                        Centro de costos <span class="text-red-400">*</span>
                    </label>
                    <select wire:model.live="centro_costo_id"
                            class="w-full rounded-lg border-gray-200 text-sm shadow-sm focus:ring-purple-500 focus:border-purple-500
                                   @error('centro_costo_id') border-red-400 @enderror">
                        <option value="">— Selecciona —</option>
                        @foreach ($departamentos as $dep)
                            <option value="{{ $dep['id'] }}">{{ $dep['nombre'] }}</option>
                        @endforeach
                    </select>
                    @error('centro_costo_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                    Justificación de la compra <span class="text-red-400">*</span>
                </label>
                <textarea rows="3" wire:model.live="justificacion"
                          placeholder="Describe la necesidad o motivo de la compra..."
                          class="w-full rounded-lg border-gray-200 text-sm shadow-sm focus:ring-purple-500 focus:border-purple-500 resize-none
                                 @error('justificacion') border-red-400 @enderror"></textarea>
                @error('justificacion') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Leyendas --}}
            <div class="flex flex-wrap pt-1 gap-x-8 gap-y-2">
                <div class="flex items-start gap-2">
                    <span class="text-sm shrink-0 mt-0.5">📅</span>
                    <p class="text-xs leading-relaxed text-gray-500">
                        <span class="font-semibold text-gray-700">Plazo de entrega:</span>
                        El plazo máximo es de <strong class="text-gray-700">15 días naturales</strong>
                        a partir de la orden de compra, salvo casos especiales informados al solicitante.
                    </p>
                </div>
                <div class="flex items-start gap-2">
                    <span class="text-sm shrink-0 mt-0.5">💰</span>
                    <p class="text-xs leading-relaxed text-gray-500">
                        <span class="font-semibold text-gray-700">Programación de pagos:</span>
                        Para que el pago aplique en la semana en curso, la factura debe recibirse a más tardar el
                        <strong class="text-gray-700">lunes a las 12:00 pm</strong>.
                    </p>
                </div>
            </div>

            {{-- Checkboxes --}}
            <div class="pt-1 space-y-3">
                <label class="flex items-start gap-3 p-3 rounded-xl border border-orange-100 bg-orange-50/50 cursor-pointer hover:bg-orange-50 transition-colors">
                    <div class="flex items-center h-5 mt-0.5">
                        <input type="checkbox" wire:model.live="afecta_produccion"
                               class="w-4 h-4 text-orange-600 border-orange-300 rounded focus:ring-orange-500">
                    </div>
                    <div>
                        <span class="text-sm font-semibold text-orange-800">⚠️ Afecta al proceso de producción</span>
                        <p class="text-xs text-orange-600 mt-0.5">Márcalo si esta compra impacta directamente en la línea de producción.</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 bg-gray-50/50 cursor-pointer hover:bg-gray-50 transition-colors">
                    <div class="flex items-center h-5 mt-0.5">
                        <input type="checkbox" wire:model.live="es_pago_factura"
                               class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                    </div>
                    <div>
                        <span class="text-sm font-semibold text-gray-700">Es pago de factura</span>
                        <p class="text-xs text-gray-400 mt-0.5">Actívalo si es para pagar una factura existente. <strong class="text-gray-600">Se requiere adjuntar la factura.</strong></p>
                    </div>
                </label>
            </div>

            {{-- Zona de factura --}}
            @if($es_pago_factura)
            <div class="p-4 space-y-3 border-2 border-purple-300 border-dashed rounded-xl bg-purple-50/50">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-sm font-bold text-purple-700">Factura adjunta <span class="text-red-500">*</span></span>
                    <span class="text-xs text-purple-400">(PDF, JPG o PNG · máx. 10 MB)</span>
                </div>

                @if($factura_path)
                    <div class="flex items-center justify-between p-3 bg-white border border-purple-200 rounded-xl">
                        <div class="flex items-center min-w-0 gap-2">
                            <span class="text-xl">📄</span>
                            <div class="min-w-0">
                                <a href="{{ Storage::disk('public')->url($factura_path) }}" target="_blank"
                                   class="block max-w-xs text-sm font-semibold text-purple-600 truncate hover:underline">
                                    {{ $factura_nombre ?? 'Factura' }}
                                </a>
                                <p class="text-xs text-gray-400">Factura adjunta</p>
                            </div>
                        </div>
                        <button type="button" wire:click="removeFactura" wire:confirm="¿Eliminar la factura?"
                                class="ml-3 text-xs font-semibold text-red-500 hover:text-red-700 shrink-0">
                            Eliminar
                        </button>
                    </div>
                @else
                    <label class="flex items-center justify-center gap-3 p-4 transition-colors bg-white border border-purple-200 cursor-pointer rounded-xl hover:bg-purple-50">
                        <svg class="w-6 h-6 text-purple-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        <div class="text-center">
                            <span class="text-sm font-semibold text-purple-600">Subir factura</span>
                            <p class="text-xs text-purple-400 mt-0.5">PDF, JPG o PNG</p>
                        </div>
                        <input type="file" wire:model="factura_nueva" accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                    </label>
                    <div wire:loading wire:target="factura_nueva" class="flex items-center gap-2 text-xs text-purple-500">
                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Subiendo...
                    </div>
                    @if($factura_nueva)
                    <div class="flex items-center gap-2 p-2 text-xs text-gray-500 bg-white border border-purple-100 rounded-lg">
                        <span>📄</span>
                        <span class="truncate">{{ $factura_nueva->getClientOriginalName() }}</span>
                    </div>
                    @endif
                @endif

                @error('factura_nueva')
                    <div class="flex items-center gap-2 p-3 text-xs font-medium text-red-700 border border-red-200 rounded-lg bg-red-50">
                        ⚠️ {{ $message }}
                    </div>
                @enderror
            </div>
            @endif

        </div>
    </div>

    {{-- ══ PARTIDAS ══════════════════════════════════════════════════════ --}}
    <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100"
             style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <h3 class="text-xs font-bold tracking-widest text-white uppercase">Partidas</h3>
            </div>
            <span class="text-[10px] text-white/50 font-medium">Máx. 5 archivos por partida</span>
        </div>

        <div class="divide-y divide-gray-50">
            @foreach ($items as $i => $row)
            <div wire:key="item-{{ $i }}" class="p-5 space-y-4">

                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white rounded-full"
                          style="background: linear-gradient(135deg, #4A1660, #7c3aed);">
                        {{ $i + 1 }}
                    </span>
                    @if(count($items) > 1)
                    <button type="button" wire:click="removeItem({{ $i }})"
                            class="inline-flex items-center gap-1 text-xs text-red-400 transition-colors hover:text-red-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Eliminar partida
                    </button>
                    @endif
                </div>

                {{-- Fila 1 --}}
                <div class="grid grid-cols-12 gap-3">
                    <div class="col-span-12 sm:col-span-5">
                        <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                            Descripción <span class="text-red-400">*</span>
                        </label>
                        <input type="text" wire:model.live="items.{{ $i }}.descripcion"
                               placeholder="Producto o servicio"
                               class="w-full rounded-lg border-gray-200 text-sm shadow-sm focus:ring-purple-500 focus:border-purple-500
                                      @error("items.$i.descripcion") border-red-400 @enderror">
                        @error("items.$i.descripcion") <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-4 sm:col-span-2">
                        <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Cantidad</label>
                        <input type="number" step="1" min="1"
                               wire:model.blur="items.{{ $i }}.cantidad"
                               class="w-full text-sm text-right border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                    </div>

                    <div class="col-span-8 sm:col-span-3">
                        <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Unidad</label>
                        <select wire:model.live="items.{{ $i }}.unidad_medida_id"
                                class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                            <option value="">— Unidad —</option>
                            @foreach ($unidades_medida as $um)
                                <option value="{{ $um['id'] }}">{{ $um['abreviatura'] }} — {{ $um['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-span-6 sm:col-span-2">
                        <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Precio unit.</label>
                        <div class="relative">
                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-300 text-xs font-bold">$</span>
                            <input type="number" step="0.01" min="0"
                                   wire:model.blur="items.{{ $i }}.precio_unitario"
                                   class="w-full pl-6 text-sm text-right border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                        </div>
                    </div>
                </div>

                {{-- Fila 2 --}}
                <div class="grid grid-cols-12 gap-3">
                    <div class="col-span-12 sm:col-span-3">
                        <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Impuesto 1</label>
                        <select wire:model.live="items.{{ $i }}.tipo_impuesto_id"
                                class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Sin impuesto</option>
                            @foreach ($tipos_impuesto as $ti)
                                <option value="{{ $ti['id'] }}">{{ $ti['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-span-12 sm:col-span-3">
                        <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                            Impuesto 2 <span class="ml-1 font-normal text-gray-300 normal-case">(opcional)</span>
                        </label>
                        <select wire:model.live="items.{{ $i }}.tipo_impuesto_id_2"
                                class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Sin segundo impuesto</option>
                            @foreach ($tipos_impuesto as $ti)
                                <option value="{{ $ti['id'] }}">{{ $ti['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-span-12 sm:col-span-4">
                        <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Proveedor sugerido</label>
                        <input type="text" wire:model.live="items.{{ $i }}.proveedor_sugerido"
                               placeholder="Nombre del proveedor"
                               class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                    </div>

                    <div class="flex flex-col justify-end col-span-12 sm:col-span-2">
                        <div class="px-3 py-2 space-y-1 text-xs border border-purple-100 rounded-xl bg-purple-50/50">
                            <div class="flex justify-between text-gray-400">
                                <span>Subtotal</span>
                                <span>${{ number_format($row['subtotal'] ?? 0, 2) }}</span>
                            </div>
                            @if(($row['monto_impuesto'] ?? 0) > 0)
                            <div class="flex justify-between text-gray-400">
                                <span>Imp. 1</span>
                                <span>${{ number_format($row['monto_impuesto'] ?? 0, 2) }}</span>
                            </div>
                            @endif
                            @if(($row['monto_impuesto_2'] ?? 0) > 0)
                            <div class="flex justify-between text-gray-400">
                                <span>Imp. 2</span>
                                <span>${{ number_format($row['monto_impuesto_2'] ?? 0, 2) }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between pt-1 font-bold border-t border-purple-200" style="color: #4A1660">
                                <span>Total</span>
                                <span>${{ number_format($row['total_item'] ?? 0, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Retenciones --}}
                @if($es_pago_factura && count($tipos_retencion) > 0)
                <div class="rounded-xl border border-rose-100 bg-rose-50/40 p-4 space-y-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                        </svg>
                        <span class="text-xs font-bold text-rose-700 uppercase tracking-wider">Retenciones</span>
                        <span class="text-xs text-rose-400 font-normal">Revisa el pdf que adjuntas para verificar las retenciones</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($tipos_retencion as $ret)
                        <label class="flex items-start gap-2.5 p-2.5 rounded-lg border cursor-pointer transition-colors
                                    {{ in_array($ret['id'], $row['retenciones_ids'] ?? [])
                                        ? 'border-rose-300 bg-rose-50'
                                        : 'border-gray-200 bg-white hover:border-rose-200 hover:bg-rose-50/50' }}">
                            <input type="checkbox"
                                wire:model.live="items.{{ $i }}.retenciones_ids"
                                value="{{ $ret['id'] }}"
                                class="mt-0.5 w-4 h-4 text-rose-600 border-gray-300 rounded focus:ring-rose-500 shrink-0">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-gray-800 leading-tight">{{ $ret['nombre'] }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ number_format($ret['porcentaje'], 3) }}% sobre subtotal</p>
                                @if(in_array($ret['id'], $row['retenciones_ids'] ?? []) && ($row['subtotal'] ?? 0) > 0)
                                    @php $montoRetCalc = round(($row['subtotal'] ?? 0) * ($ret['porcentaje'] / 100), 2); @endphp
                                    <p class="text-[10px] font-semibold text-rose-600 mt-0.5">= ${{ number_format($montoRetCalc, 2) }}</p>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>

                    @if(($row['monto_retenciones'] ?? 0) > 0)
                    <div class="flex items-center justify-between pt-2 border-t border-rose-100 text-sm">
                        <span class="text-xs text-rose-600 font-medium">Total retenciones esta partida:</span>
                        <span class="font-bold text-rose-700">- ${{ number_format($row['monto_retenciones'], 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-xs text-gray-600 font-medium">Total neto a pagar esta partida:</span>
                        <span class="font-bold text-gray-800">${{ number_format($row['total_neto'] ?? 0, 2) }}</span>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Link --}}
                <div class="grid grid-cols-12 gap-3">
                    <div class="col-span-12 sm:col-span-8">
                        <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Link de referencia</label>
                        <input type="text" wire:model.live="items.{{ $i }}.link_compra"
                               placeholder="https://... (pega aquí el link del producto)"
                               class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                    </div>
                </div>

                {{-- Archivos --}}
                <div>
                    <label class="block mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                        Archivos adjuntos
                        <span class="font-normal text-gray-300 normal-case">(fichas técnicas, cotizaciones — máx. 5)</span>
                    </label>

                    @if(!empty($row['archivos_existentes']))
                    <div class="flex flex-wrap gap-2 mb-2">
                        @foreach($row['archivos_existentes'] as $arch)
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-gray-200 bg-gray-50 text-xs group">
                            <span>{{ $arch['icono'] ?? '📎' }}</span>
                            <a href="{{ $arch['url'] }}" target="_blank"
                            class="text-indigo-500 hover:underline max-w-[140px] truncate">
                                {{ $arch['nombre_original'] }}
                            </a>
                            <button type="button"
                                    wire:click="removeArchivoExistente({{ $i }}, {{ $arch['id'] }})"
                                    class="ml-1 text-gray-300 transition-colors hover:text-red-500"
                                    title="Eliminar">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if(!empty($row['ficha_tecnica_path']) && empty($row['archivos_existentes']))
                    <div class="mb-2">
                        <a href="{{ Storage::disk('public')->url($row['ficha_tecnica_path']) }}" target="_blank"
                        class="inline-flex items-center gap-1 text-xs text-indigo-500 hover:underline">
                            📄 {{ $row['ficha_tecnica_nombre'] ?? 'Ficha técnica' }}
                            <span class="text-gray-300">(anterior)</span>
                        </a>
                    </div>
                    @endif

                    @if(!empty($archivos_nuevos[$i]))
                    <div class="flex flex-wrap gap-2 mb-2">
                        @foreach($archivos_nuevos[$i] as $j => $nuevoArch)
                        @if($nuevoArch)
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-emerald-200 bg-emerald-50 text-xs">
                            <span>📎</span>
                            <span class="text-emerald-700 max-w-[140px] truncate font-medium">
                                {{ $nuevoArch->getClientOriginalName() }}
                            </span>
                            <span class="text-emerald-400 shrink-0 text-[10px]">
                                ({{ round($nuevoArch->getSize() / 1024, 0) }} KB)
                            </span>
                            <button type="button"
                                    wire:click="removeArchivoNuevo({{ $i }}, {{ $j }})"
                                    class="ml-1 text-emerald-400 hover:text-red-500 transition-colors"
                                    title="Quitar">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @endif

                    @php
                        $totalArch = count($row['archivos_existentes'] ?? [])
                                + count(array_filter($archivos_nuevos[$i] ?? []));
                    @endphp

                    @if($totalArch < 5)
                    <div class="flex items-center gap-2">
                        <label class="inline-flex items-center gap-2 px-3 py-2 text-xs text-gray-500 transition-colors border border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            Adjuntar archivo
                            @if($totalArch > 0)
                                <span class="text-gray-400">({{ $totalArch }}/5)</span>
                            @endif
                            <input type="file"
                                wire:model="archivo_temp"
                                wire:change="$set('archivo_temp_index', {{ $i }})"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                                class="hidden">
                        </label>

                        <div wire:loading wire:target="archivo_temp"
                            class="inline-flex items-center gap-1 text-xs text-purple-500">
                            <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Subiendo...
                        </div>
                    </div>
                    @else
                    <p class="text-xs text-amber-500">⚠️ Límite de 5 archivos alcanzado</p>
                    @endif

                    @error("archivos_nuevos.$i")
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>
            @endforeach
        </div>

        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
            <button type="button" wire:click="addItem"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold transition-colors" style="color: #4A1660">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Agregar partida
            </button>
        </div>
    </div>

    {{-- ══ TOTALES ══════════════════════════════════════════════════════ --}}
    <div class="flex justify-end">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 w-full max-w-xs space-y-2.5">
            <div class="flex justify-between text-sm text-gray-500">
                <span>Subtotal</span>
                <span class="font-semibold text-gray-700">${{ number_format($subtotal, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm text-gray-500">
                <span>Impuestos</span>
                <span class="font-semibold text-gray-700">${{ number_format($total_impuestos, 2) }}</span>
            </div>
            <div class="flex justify-between pt-2.5 border-t border-gray-100">
                <span class="font-bold text-gray-800">Total</span>
                <span class="text-xl font-black" style="color: #4A1660">${{ number_format($total, 2) }}</span>
            </div>
            @if($es_pago_factura && $total_retenciones > 0)
            <div class="flex justify-between text-sm text-rose-600 pt-1 border-t border-rose-100">
                <span>Retenciones</span>
                <span class="font-semibold">- ${{ number_format($total_retenciones, 2) }}</span>
            </div>
            <div class="flex justify-between font-bold text-gray-900">
                <span>Total neto a pagar</span>
                <span class="text-lg" style="color: #4A1660">${{ number_format($total_neto, 2) }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- ══ ACCIONES ══════════════════════════════════════════════════════ --}}
    <div class="flex items-center justify-end gap-3 pb-6">
        <a href="{{ route('requisiciones.index') }}"
           class="px-4 py-2.5 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
            Cancelar
        </a>

        @if($estado_actual === 'borrador')
        <button type="button" wire:click.prevent="saveDraft"
                wire:loading.attr="disabled" wire:target="saveDraft"
                class="px-4 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition disabled:opacity-50">
            <span wire:loading.remove wire:target="saveDraft">Guardar borrador</span>
            <span wire:loading wire:target="saveDraft">Guardando…</span>
        </button>
        @endif

        @if($estado_actual === 'borrador')
        <button type="button" wire:click.prevent="sendToApproval"
                wire:loading.attr="disabled" wire:target="sendToApproval"
                class="px-6 py-2.5 text-sm font-bold text-white rounded-lg shadow-md hover:shadow-lg transition-all disabled:opacity-50"
                style="background: linear-gradient(135deg, #4A1660, #7c3aed);">
            <span wire:loading.remove wire:target="sendToApproval">Enviar a Compras</span>
            <span wire:loading wire:target="sendToApproval" class="flex items-center gap-2">
                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Enviando…
            </span>
        </button>
        @endif

        @if($estado_actual === 'rechazada_compras')
        <button type="button" wire:click.prevent="reenviarACompras"
                wire:loading.attr="disabled" wire:target="reenviarACompras"
                class="px-6 py-2.5 text-sm font-bold text-white bg-orange-600 rounded-lg hover:bg-orange-700 shadow-md hover:shadow-lg transition-all disabled:opacity-50">
            <span wire:loading.remove wire:target="reenviarACompras">↩️ Reenviar a Compras</span>
            <span wire:loading wire:target="reenviarACompras">Reenviando…</span>
        </button>
        @endif

        @if(in_array($estado_actual, ['en_revision_compras', 'aprobada_compras', 'en_aprobacion']))
        <button type="button" wire:click.prevent="saveDraft"
                wire:loading.attr="disabled" wire:target="saveDraft"
                class="px-6 py-2.5 text-sm font-bold text-white rounded-lg shadow-md hover:shadow-lg transition-all disabled:opacity-50"
                style="background: linear-gradient(135deg, #4A1660, #7c3aed);">
            <span wire:loading.remove wire:target="saveDraft">💾 Guardar cambios</span>
            <span wire:loading wire:target="saveDraft">Guardando…</span>
        </button>
        @endif
    </div>

</div>
</div>