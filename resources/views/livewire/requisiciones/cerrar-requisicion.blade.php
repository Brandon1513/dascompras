<div class="max-w-4xl mx-auto space-y-5">

    {{-- ══ HEADER ══════════════════════════════════════════════════════ --}}
    <div class="flex items-start gap-3 p-4 border rounded-xl border-cyan-200 bg-cyan-50">
        <div class="flex items-center justify-center rounded-lg w-9 h-9 bg-cyan-100 shrink-0">
            <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-cyan-800">Cierre de requisición — Coordinación de Compras</p>
            <p class="text-xs text-cyan-600 mt-0.5">
                @if($requisicion->es_pago_factura)
                    Esta es un <strong>pago de factura</strong>. La factura ya fue adjuntada por el solicitante. Verifica el UUID y cierra el proceso.
                @else
                    La compra fue recibida por el solicitante. Registra si se cuenta con la factura del proveedor.
                @endif
            </p>
        </div>
    </div>

    {{-- ══ RESUMEN ══════════════════════════════════════════════════════ --}}
    @php
        $hayRetenciones = $requisicion->es_pago_factura &&
            $requisicion->items->sum(fn($it) => (float)($it->monto_retenciones ?? 0)) > 0;
        $totalRet  = $hayRetenciones ? $requisicion->items->sum(fn($it) => (float)($it->monto_retenciones ?? 0)) : 0;
        $totalNeto = $hayRetenciones ? $requisicion->items->sum(fn($it) => (float)($it->total_neto ?? $it->total_item ?? 0)) : (float)$requisicion->total;
    @endphp
    <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-base font-semibold text-gray-800">{{ $requisicion->folio }}</h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Solicitante: <strong>{{ $requisicion->solicitante?->name }}</strong>
                    · {{ optional($requisicion->fecha_emision)->format('d/m/Y') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if($requisicion->es_pago_factura)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-violet-100 text-violet-700 border border-violet-200">
                        Pago de factura
                    </span>
                @endif
                @if($requisicion->urgencia === 'urgente')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700 border border-red-200">⚠️ Afecta producción</span>
                @endif
            </div>
        </div>

        <dl class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
            <div>
                <dt class="text-xs text-gray-500 mb-0.5">Departamento</dt>
                <dd class="font-medium text-gray-800">{{ $requisicion->departamentoRef?->nombre ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500 mb-0.5">Subtotal</dt>
                <dd class="font-medium text-gray-800">${{ number_format($requisicion->subtotal, 2) }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500 mb-0.5">Impuestos</dt>
                <dd class="font-medium text-gray-800">${{ number_format($requisicion->iva, 2) }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500 mb-0.5">Total</dt>
                <dd class="text-base font-bold text-gray-900">${{ number_format($requisicion->total, 2) }}</dd>
            </div>
        </dl>

        {{-- Retenciones y total neto (solo si aplica) --}}
        @if($hayRetenciones)
        <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4 mt-3 pt-3 border-t border-rose-100">
            <div class="md:col-start-3">
                <dt class="text-xs text-rose-500 mb-0.5">Retenciones</dt>
                <dd class="font-semibold text-rose-600">- ${{ number_format($totalRet, 2) }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500 mb-0.5">Total neto a pagar</dt>
                <dd class="text-base font-bold" style="color: #4A1660">${{ number_format($totalNeto, 2) }}</dd>
            </div>
        </div>
        @endif

        @if($requisicion->metodo_pago)
        <div class="flex items-center gap-2 pt-3 mt-3 border-t border-gray-100">
            <dt class="text-xs text-gray-500">Método de pago:</dt>
            <dd>
                <span @class([
                    'inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium border',
                    'bg-blue-50 text-blue-700 border-blue-200'       => $requisicion->metodo_pago === 'transferencia',
                    'bg-purple-50 text-purple-700 border-purple-200' => $requisicion->metodo_pago === 'tarjeta',
                    'bg-green-50 text-green-700 border-green-200'    => $requisicion->metodo_pago === 'efectivo',
                ])>
                    {{ match($requisicion->metodo_pago) { 'tarjeta' => '💳', 'transferencia' => '🏦', 'efectivo' => '💵', default => '' } }}
                    {{ ucfirst($requisicion->metodo_pago) }}
                </span>
            </dd>
        </div>
        @endif
    </div>

    {{-- ══ PANEL PRINCIPAL ══════════════════════════════════════════════ --}}
    <div class="p-5 space-y-5 bg-white border border-gray-200 shadow-sm rounded-xl">

        @if($requisicion->es_pago_factura)

            {{-- Factura del solicitante --}}
            <div>
                <h3 class="mb-3 text-sm font-semibold text-gray-700">Factura adjunta por el solicitante</h3>
                <div class="flex items-center justify-between p-4 border border-indigo-200 bg-indigo-50 rounded-xl">
                    <div class="flex items-center min-w-0 gap-3">
                        <span class="text-2xl">📄</span>
                        <div class="min-w-0">
                            <a href="{{ Storage::disk('public')->url($factura_compras_path) }}"
                               target="_blank"
                               class="block max-w-xs text-sm font-medium text-indigo-700 truncate hover:underline">
                                {{ $factura_compras_nombre ?? 'Ver factura' }}
                            </a>
                            <p class="text-xs text-indigo-400 mt-0.5">Subida por el solicitante al crear la requisición</p>
                        </div>
                    </div>
                    <a href="{{ Storage::disk('public')->url($factura_compras_path) }}"
                       target="_blank"
                       class="ml-4 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-indigo-600 bg-white border border-indigo-200 rounded-lg hover:bg-indigo-50 transition shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Abrir
                    </a>
                </div>
            </div>

            {{-- UUID --}}
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">
                    UUID / Folio Fiscal
                    @if($uuidAutoDetectado)
                        <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-700">
                            ✅ Auto detectado
                        </span>
                    @else
                        <span class="ml-1 text-xs font-normal text-gray-400">(opcional — ingresa manualmente si no se detectó)</span>
                    @endif
                </label>

                @if($mensajeExtraccion)
                <div @class([
                    'flex items-center gap-2 p-3 mb-2 rounded-lg text-xs font-medium',
                    'bg-emerald-50 border border-emerald-200 text-emerald-700' => $uuidAutoDetectado,
                    'bg-blue-50 border border-blue-200 text-blue-700'          => !$uuidAutoDetectado,
                ])>
                    {{ $mensajeExtraccion }}
                </div>
                @endif

                <input type="text"
                       wire:model="uuid_factura"
                       placeholder="XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX"
                       maxlength="36"
                       class="w-full rounded-lg border-gray-300 text-sm font-mono shadow-sm focus:ring-emerald-500 focus:border-emerald-500
                              @error('uuid_factura') border-red-400 @enderror">
                <p class="mt-1 text-xs text-gray-400">Puedes copiarlo del correo del SAT o del PDF de la factura.</p>
                @error('uuid_factura')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Notas --}}
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">
                    Notas de cierre <span class="font-normal text-gray-400">(opcional)</span>
                </label>
                <textarea wire:model.lazy="notas_cierre" rows="2"
                          placeholder="Condiciones de pago, observaciones del cierre..."
                          class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500"></textarea>
            </div>

            <div class="pt-2">
                <button type="button" wire:click="cerrarConFactura"
                        wire:loading.attr="disabled" wire:target="cerrarConFactura"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="cerrarConFactura">
                        <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Cerrar requisición
                    </span>
                    <span wire:loading wire:target="cerrarConFactura" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Procesando…
                    </span>
                </button>
            </div>

        @else

            <h3 class="text-sm font-semibold text-gray-700">¿Se tiene factura del proveedor?</h3>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <button type="button"
                        wire:click="$set('tiene_factura', true)"
                        @class([
                            'flex items-center gap-3 p-4 rounded-xl border-2 transition-all text-left',
                            'border-emerald-500 bg-emerald-50'  => $tiene_factura === true,
                            'border-gray-200 bg-white hover:border-emerald-300 hover:bg-emerald-50' => $tiene_factura !== true,
                        ])>
                    <div @class([
                        'flex items-center justify-center w-10 h-10 rounded-full shrink-0',
                        'bg-emerald-500 text-white' => $tiene_factura === true,
                        'bg-gray-100 text-gray-400' => $tiene_factura !== true,
                    ])>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p @class(['text-sm font-semibold', 'text-emerald-800' => $tiene_factura === true, 'text-gray-700' => $tiene_factura !== true])>Sí, tengo la factura</p>
                        <p class="text-xs text-gray-400 mt-0.5">Adjuntar PDF o imagen de la factura</p>
                    </div>
                </button>

                <button type="button"
                        wire:click="$set('tiene_factura', false)"
                        @class([
                            'flex items-center gap-3 p-4 rounded-xl border-2 transition-all text-left',
                            'border-orange-400 bg-orange-50'  => $tiene_factura === false,
                            'border-gray-200 bg-white hover:border-orange-300 hover:bg-orange-50' => $tiene_factura !== false,
                        ])>
                    <div @class([
                        'flex items-center justify-center w-10 h-10 rounded-full shrink-0',
                        'bg-orange-400 text-white' => $tiene_factura === false,
                        'bg-gray-100 text-gray-400' => $tiene_factura !== false,
                    ])>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div>
                        <p @class(['text-sm font-semibold', 'text-orange-800' => $tiene_factura === false, 'text-gray-700' => $tiene_factura !== false])>No, sin factura por ahora</p>
                        <p class="text-xs text-gray-400 mt-0.5">Se registrará para el reporte</p>
                    </div>
                </button>
            </div>

            @if($tiene_factura === true)
            <div class="pt-2 space-y-4 border-t border-gray-100">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        Factura del proveedor <span class="text-red-500">*</span>
                        <span class="ml-1 text-xs font-normal text-gray-400">(PDF, JPG o PNG · máx. 10 MB)</span>
                    </label>

                    @if($factura_compras_path)
                        <div class="flex items-center justify-between p-3 border rounded-lg bg-emerald-50 border-emerald-200">
                            <div class="flex items-center min-w-0 gap-2">
                                <span class="text-xl">📄</span>
                                <div class="min-w-0">
                                    <a href="{{ Storage::disk('public')->url($factura_compras_path) }}"
                                       target="_blank"
                                       class="block max-w-xs text-sm font-medium text-indigo-600 truncate hover:underline">
                                        {{ $factura_compras_nombre ?? 'Factura' }}
                                    </a>
                                    <p class="text-xs text-gray-400">Factura adjunta</p>
                                </div>
                            </div>
                            <button type="button" wire:click="removeFacturaCompras"
                                    wire:confirm="¿Eliminar la factura? Tendrás que subir una nueva."
                                    class="ml-3 text-xs font-medium text-red-600 hover:text-red-800 shrink-0">
                                Eliminar
                            </button>
                        </div>
                    @else
                        <label class="flex flex-col items-center justify-center gap-2 p-6 transition-all border-2 border-gray-300 border-dashed cursor-pointer bg-gray-50 rounded-xl hover:border-emerald-400 hover:bg-emerald-50">
                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            <div class="text-center">
                                <span class="text-sm font-medium text-gray-600">Haz clic para subir la factura</span>
                                <p class="text-xs text-gray-400 mt-0.5">PDF, JPG o PNG · máx. 10 MB</p>
                            </div>
                            <input type="file" wire:model="factura_compras_nueva"
                                   accept=".pdf,.jpg,.jpeg,.png" class="hidden">
                        </label>

                        <div wire:loading wire:target="factura_compras_nueva"
                             class="flex items-center gap-2 mt-2 text-xs text-indigo-600">
                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Subiendo y procesando PDF...
                        </div>

                        @if($factura_compras_nueva)
                        <div class="flex items-center gap-2 p-2 mt-2 text-xs text-gray-600 bg-white border border-gray-200 rounded-lg">
                            <span>📄</span>
                            <span class="truncate">{{ $factura_compras_nueva->getClientOriginalName() }}</span>
                            <span class="text-gray-400 shrink-0">({{ round($factura_compras_nueva->getSize() / 1024, 1) }} KB)</span>
                        </div>
                        @endif
                    @endif

                    @error('factura_compras_nueva')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if($mensajeExtraccion)
                <div @class([
                    'flex items-center gap-2 p-3 rounded-lg text-xs font-medium',
                    'bg-emerald-50 border border-emerald-200 text-emerald-700' => $uuidAutoDetectado,
                    'bg-blue-50 border border-blue-200 text-blue-700'          => !$uuidAutoDetectado,
                ])>{{ $mensajeExtraccion }}</div>
                @endif

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        UUID / Folio Fiscal
                        @if($uuidAutoDetectado)
                            <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-700">Auto detectado</span>
                        @else
                            <span class="ml-1 text-xs font-normal text-gray-400">(opcional)</span>
                        @endif
                    </label>
                    <input type="text"
                           wire:model="uuid_factura"
                           placeholder="XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX"
                           maxlength="36"
                           class="w-full rounded-lg border-gray-300 text-sm font-mono shadow-sm focus:ring-emerald-500 focus:border-emerald-500
                                  @error('uuid_factura') border-red-400 @enderror">
                    @error('uuid_factura')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Notas de cierre <span class="font-normal text-gray-400">(opcional)</span>
                    </label>
                    <textarea wire:model.lazy="notas_cierre" rows="2"
                              placeholder="Condiciones de pago, observaciones del cierre..."
                              class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                </div>

                <button type="button" wire:click="cerrarConFactura"
                        wire:loading.attr="disabled" wire:target="cerrarConFactura"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="cerrarConFactura">
                        <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Cerrar requisición con factura
                    </span>
                    <span wire:loading wire:target="cerrarConFactura" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Procesando…
                    </span>
                </button>
            </div>
            @endif

            @if($tiene_factura === false)
            <div class="pt-2 space-y-4 border-t border-gray-100">
                <div class="p-4 text-sm text-orange-800 border border-orange-200 rounded-lg bg-orange-50">
                    <p class="font-medium">Sin factura</p>
                    <p class="mt-1 text-xs text-orange-600">La requisición se cerrará y quedará registrada como <strong>"Sin factura"</strong> en los reportes.</p>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Motivo o notas <span class="font-normal text-gray-400">(opcional)</span>
                    </label>
                    <textarea wire:model.lazy="notas_cierre" rows="2"
                              placeholder="Ej: Proveedor no emite factura, compra de caja chica..."
                              class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500"></textarea>
                </div>
                <button type="button" wire:click="cerrarSinFactura"
                        wire:loading.attr="disabled" wire:target="cerrarSinFactura"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-orange-500 rounded-lg hover:bg-orange-600 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="cerrarSinFactura">
                        <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Cerrar sin factura
                    </span>
                    <span wire:loading wire:target="cerrarSinFactura">Cerrando…</span>
                </button>
            </div>
            @endif

        @endif

    </div>

</div>