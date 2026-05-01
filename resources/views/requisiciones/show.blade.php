<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('requisiciones.index') }}"
                   class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-500 hover:bg-gray-200 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-gray-900">{{ $requisicion->folio }}</h2>
                        @if($requisicion->es_pago_factura)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-violet-100 text-violet-700 border border-violet-200">
                                Pago de factura
                            </span>
                        @endif
                        @if($requisicion->urgencia === 'urgente')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-orange-100 text-orange-700">
                                ⚠️ Afecta producción
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Emitida el {{ optional($requisicion->fecha_emision)->format('d/m/Y') }}
                        · {{ $requisicion->solicitante?->name }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @php
                    $color = $requisicion->estadoColor;
                    $estadoIcons = [
                        'borrador'            => '✏️',
                        'en_revision_compras' => '🔍',
                        'rechazada_compras'   => '↩️',
                        'aprobada_compras'    => '✅',
                        'en_aprobacion'       => '⏳',
                        'rechazada'           => '❌',
                        'aprobada_final'      => '✅',
                        'pendiente_cierre'    => '📋',
                        'recibida'            => '📦',
                        'cancelada'           => '🚫',
                    ];
                    $estadoIcon = $estadoIcons[$requisicion->estado] ?? '•';
                @endphp
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border
                             {{ $color['bg'] }} {{ $color['text'] }} {{ $color['border'] }}">
                    {{ $estadoIcon }} {{ $requisicion->estado_label }}
                </span>

                @role('administrador|compras')
                @if($requisicion->estado === 'en_revision_compras')
                    <a href="{{ route('requisiciones.revisar', $requisicion) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white rounded-lg transition"
                       style="background: linear-gradient(135deg, #7c3aed, #4A1660);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Revisar
                    </a>
                @endif
                @endrole

                @if(in_array($requisicion->estado, ['aprobada_final','pendiente_cierre','recibida']))
                    <a href="{{ route('requisiciones.pdf', $requisicion) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-purple-700 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        PDF
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    {{-- Fondo con textura sutil --}}
    <div class="min-h-screen py-6" style="background: linear-gradient(160deg, #f8f5ff 0%, #f1f5f9 50%, #f8f5ff 100%);">
        <div class="max-w-5xl px-4 mx-auto space-y-4 sm:px-6 lg:px-8">

            {{-- ══ BANNERS DE ESTADO ════════════════════════════════════ --}}

            {{-- Leyenda responsabilidad --}}
            <div class="flex items-start gap-3 px-4 py-3 rounded-xl border border-amber-200/80 bg-amber-50/80 backdrop-blur-sm">
                <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <p class="text-xs font-medium text-amber-800">
                    El seguimiento al flujo de aprobación es responsabilidad del solicitante y superiores.
                </p>
            </div>

            {{-- Rechazada por compras --}}
            @if($requisicion->estado === 'rechazada_compras')
                <div class="rounded-xl border border-orange-200 bg-orange-50 overflow-hidden">
                    <div class="flex items-center gap-2 px-4 py-2 bg-orange-100 border-b border-orange-200">
                        <span class="text-sm">↩️</span>
                        <span class="text-xs font-bold text-orange-800 uppercase tracking-wider">Requiere correcciones — Compras</span>
                    </div>
                    <div class="px-4 py-3">
                        @if($requisicion->motivo_rechazo_compras)
                            <p class="text-sm text-orange-700 whitespace-pre-line">{{ $requisicion->motivo_rechazo_compras }}</p>
                        @endif
                        <p class="text-xs text-orange-500 mt-2">
                            Revisado por {{ $requisicion->revisadoPor?->name ?? '—' }}
                            @if($requisicion->revisado_en)· {{ $requisicion->revisado_en->format('d/m/Y H:i') }}@endif
                        </p>
                        @can('update', $requisicion)
                        <a href="{{ route('requisiciones.edit', $requisicion) }}"
                           class="inline-flex items-center gap-1.5 mt-3 px-4 py-2 text-xs font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 transition">
                            ✏️ Corregir y reenviar
                        </a>
                        @endcan
                    </div>
                </div>
            @endif

            {{-- En revisión --}}
            @if($requisicion->estado === 'en_revision_compras')
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl border border-violet-200 bg-violet-50">
                    <div class="flex items-center justify-center w-7 h-7 rounded-full bg-violet-100 shrink-0">
                        <svg class="w-4 h-4 text-violet-600 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs text-violet-800">
                        <span class="font-bold">En revisión de Compras.</span>
                        El área está verificando la información antes de continuar con el proceso de aprobación.
                    </p>
                </div>
            @endif

            {{-- Pendiente de cierre --}}
            @if($requisicion->estado === 'pendiente_cierre')
                <div class="flex items-center justify-between px-4 py-3 rounded-xl border border-cyan-200 bg-cyan-50">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-7 h-7 rounded-full bg-cyan-100 shrink-0">
                            <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-xs text-cyan-800">
                            <span class="font-bold">Recibida — Pendiente de cierre por Compras.</span>
                            El solicitante confirmó la recepción.
                        </p>
                    </div>
                    @role('administrador|compras')
                    <a href="{{ route('requisiciones.cerrar', $requisicion) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-cyan-600 rounded-lg hover:bg-cyan-700 transition shrink-0">
                        Cerrar requisición →
                    </a>
                    @endrole
                </div>
            @endif

            {{-- ══ DATOS GENERALES ════════════════════════════════════ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
                     style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
                    <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="text-xs font-bold text-white uppercase tracking-widest">Datos generales</h3>
                </div>
                <div class="p-5">
                    <dl class="grid grid-cols-2 gap-5 text-sm md:grid-cols-4">
                        <div>
                            <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Fecha</dt>
                            <dd class="font-semibold text-gray-800">{{ optional($requisicion->fecha_emision)->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Solicitante</dt>
                            <dd class="font-semibold text-gray-800">{{ $requisicion->solicitante?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Departamento</dt>
                            <dd class="font-semibold text-gray-800">{{ $requisicion->departamentoRef?->nombre ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Centro de costos</dt>
                            <dd class="font-semibold text-gray-800">{{ $requisicion->centroCostoRef?->nombre ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tipo</dt>
                            <dd>
                                @if($requisicion->es_pago_factura)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-violet-100 text-violet-700">Pago de factura</span>
                                @else
                                    <span class="text-gray-600 text-sm">Requisición de compra</span>
                                @endif
                            </dd>
                        </div>
                        @role('administrador|compras')
                        <div>
                            <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Método de pago</dt>
                            <dd>
                                @if($requisicion->metodo_pago)
                                    <span @class([
                                        'inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold border',
                                        'bg-blue-50 text-blue-700 border-blue-200'       => $requisicion->metodo_pago === 'transferencia',
                                        'bg-purple-50 text-purple-700 border-purple-200' => $requisicion->metodo_pago === 'tarjeta',
                                    ])>
                                        {{ $requisicion->metodo_pago === 'tarjeta' ? '💳' : '🏦' }}
                                        {{ ucfirst($requisicion->metodo_pago) }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">Sin asignar</span>
                                @endif
                            </dd>
                        </div>
                        @endrole
                    </dl>

                    @if($requisicion->justificacion)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Justificación</dt>
                        <dd class="text-sm text-gray-700 whitespace-pre-line leading-relaxed bg-gray-50 rounded-lg px-4 py-3 border border-gray-100">{{ $requisicion->justificacion }}</dd>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ══ LEYENDAS INFORMATIVAS ══════════════════════════════ --}}
            <div class="flex flex-wrap gap-x-8 gap-y-2 px-1">
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

            {{-- ══ FACTURA ADJUNTA ═════════════════════════════════════ --}}
            @if($requisicion->es_pago_factura)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100 bg-violet-50">
                    <span>📄</span>
                    <h3 class="text-xs font-bold text-violet-700 uppercase tracking-widest">Factura adjunta</h3>
                </div>
                <div class="p-5">
                    @if($requisicion->factura_path)
                        <a href="{{ Storage::disk('public')->url($requisicion->factura_path) }}" target="_blank"
                           class="inline-flex items-center gap-3 px-4 py-3 bg-violet-50 border border-violet-200 rounded-xl hover:bg-violet-100 transition group">
                            <span class="text-2xl">📄</span>
                            <div>
                                <p class="text-sm font-semibold text-violet-700 group-hover:underline">{{ $requisicion->factura_nombre ?? 'Ver factura' }}</p>
                                <p class="text-xs text-violet-400">Clic para abrir el archivo</p>
                            </div>
                            <svg class="w-4 h-4 text-violet-400 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    @else
                        <p class="text-sm text-red-500 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Factura no adjunta
                        </p>
                    @endif
                </div>
            </div>
            @endif

            {{-- ══ OBSERVACIONES DE COMPRAS ════════════════════════════ --}}
            @role('administrador|compras')
            @if($requisicion->observaciones_compras || $requisicion->revisadoPor)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 bg-violet-50">
                    <div class="flex items-center gap-2">
                        <span>💬</span>
                        <h3 class="text-xs font-bold text-violet-700 uppercase tracking-widest">Observaciones de Compras</h3>
                    </div>
                    @if($requisicion->puedeEditarCompras())
                    <a href="{{ route('requisiciones.revisar', $requisicion) }}" class="text-xs text-violet-600 hover:underline">Editar →</a>
                    @endif
                </div>
                <div class="p-5">
                    @if($requisicion->observaciones_compras)
                        <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $requisicion->observaciones_compras }}</p>
                    @else
                        <p class="text-sm italic text-gray-400">Sin observaciones registradas.</p>
                    @endif
                    @if($requisicion->revisadoPor)
                    <p class="mt-3 text-xs text-gray-400">
                        Revisado por <span class="font-semibold text-gray-600">{{ $requisicion->revisadoPor->name }}</span>
                        @if($requisicion->revisado_en)· {{ $requisicion->revisado_en->format('d/m/Y H:i') }}@endif
                    </p>
                    @endif
                </div>
            </div>
            @endif
            @endrole

            {{-- ══ PARTIDAS ════════════════════════════════════════════ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
                     style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
                    <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <h3 class="text-xs font-bold text-white uppercase tracking-widest">Partidas</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Cant.</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Descripción</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Unidad</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Proveedor</th>
                                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Archivos</th>
                                <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">P. Unit.</th>
                                <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Subtotal</th>
                                <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Impuesto</th>
                                <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($requisicion->items as $it)
                                <tr class="hover:bg-purple-50/30 transition-colors">
                                    <td class="px-4 py-3 text-gray-500 text-xs">
                                        {{ rtrim(rtrim(number_format($it->cantidad, 3, '.', ''), '0'), '.') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-medium text-gray-800">{{ $it->descripcion }}</span>
                                        @if($it->link_compra)
                                            <a href="{{ $it->link_compra }}" target="_blank" rel="noopener"
                                               class="ml-1.5 inline-flex items-center gap-0.5 text-xs text-indigo-500 hover:text-indigo-700">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                link
                                            </a>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $it->unidad_label }}</td>
                                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $it->proveedor_sugerido ?: '—' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col gap-1">
                                            @foreach($it->archivos as $arch)
                                                <a href="{{ Storage::url($arch->path) }}" target="_blank"
                                                   class="inline-flex items-center gap-1 text-xs text-indigo-500 hover:underline max-w-[160px]">
                                                    <span>{{ $arch->icono }}</span>
                                                    <span class="truncate">{{ $arch->nombre_original }}</span>
                                                </a>
                                            @endforeach
                                            @if($it->ficha_tecnica_path && $it->archivos->isEmpty())
                                                <a href="{{ Storage::disk('public')->url($it->ficha_tecnica_path) }}" target="_blank"
                                                   class="inline-flex items-center gap-1 text-xs text-indigo-500 hover:underline">
                                                    📄 {{ $it->ficha_tecnica_nombre ?? 'Ficha' }}
                                                </a>
                                            @endif
                                            @if($it->archivos->isEmpty() && !$it->ficha_tecnica_path)
                                                <span class="text-gray-300 text-xs">—</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-600 text-xs">${{ number_format($it->precio_unitario, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-600 text-xs">${{ number_format($it->subtotal, 2) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        @if($it->tipoImpuesto)
                                            <div class="text-xs text-gray-600">${{ number_format($it->monto_impuesto, 2) }}</div>
                                            <div class="text-[10px] text-gray-400">{{ $it->tipoImpuesto->nombre }}</div>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-800 text-sm">
                                        ${{ number_format($it->total_item ?: $it->subtotal, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400 text-sm">Sin partidas registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Totales --}}
                <div class="flex justify-end px-5 py-4 border-t border-gray-100 bg-gray-50">
                    <div class="w-full max-w-xs space-y-2">
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Subtotal</span>
                            <span class="font-medium text-gray-700">${{ number_format($requisicion->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Impuestos</span>
                            <span class="font-medium text-gray-700">${{ number_format($requisicion->iva, 2) }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-gray-200">
                            <span class="font-bold text-gray-800">Total</span>
                            <span class="text-lg font-bold" style="color: #4A1660">${{ number_format($requisicion->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══ CADENA DE APROBACIONES ══════════════════════════════ --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
                     style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
                    <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <h3 class="text-xs font-bold text-white uppercase tracking-widest">Cadena de aprobaciones</h3>
                </div>
                <div class="p-5">
                    @if($requisicion->aprobaciones->isEmpty())
                        <p class="text-sm text-gray-400 text-center py-4">Sin registros de aprobación aún.</p>
                    @else
                    <div class="relative">
                        {{-- Línea conectora --}}
                        @if($requisicion->aprobaciones->count() > 1)
                        <div class="absolute left-3.5 top-6 bottom-6 w-px bg-gray-200"></div>
                        @endif

                        <ul class="space-y-4">
                            @foreach($requisicion->aprobaciones->sortBy(fn($a) => $a->nivel?->orden ?? 999) as $ap)
                                <li class="flex items-start gap-4 relative">
                                    {{-- Indicador --}}
                                    <div @class([
                                        'flex items-center justify-center w-7 h-7 rounded-full shrink-0 z-10 border-2',
                                        'bg-amber-100 border-amber-300'   => $ap->estado === 'pendiente',
                                        'bg-emerald-100 border-emerald-400' => $ap->estado === 'aprobada',
                                        'bg-rose-100 border-rose-400'     => $ap->estado === 'rechazada',
                                    ])>
                                        @if($ap->estado === 'aprobada')
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        @elseif($ap->estado === 'rechazada')
                                            <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        @else
                                            <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                                        @endif
                                    </div>

                                    <div class="flex-1 flex items-start justify-between gap-4 min-w-0">
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-800">{{ $ap->nivel?->nombre ?? '—' }}</p>
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                {{ $ap->aprobador?->name ?? 'Por rol: ' . ($ap->nivel?->rol_aprobador ?? '—') }}
                                                @if($ap->firmado_en)
                                                    <span class="text-gray-400">· {{ $ap->firmado_en->format('d/m/Y H:i') }}</span>
                                                @endif
                                            </p>
                                            @if($ap->comentarios)
                                                <p class="mt-1 text-xs italic text-gray-400 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100">"{{ $ap->comentarios }}"</p>
                                            @endif
                                        </div>
                                        <span @class([
                                            'inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold shrink-0',
                                            'bg-amber-100 text-amber-700'   => $ap->estado === 'pendiente',
                                            'bg-emerald-100 text-emerald-700' => $ap->estado === 'aprobada',
                                            'bg-rose-100 text-rose-700'     => $ap->estado === 'rechazada',
                                        ])>{{ ucfirst($ap->estado) }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ══ COMPONENTE APROBAR ══════════════════════════════════ --}}
            @if($puedeFirmar && $requisicion->estado === 'en_aprobacion')
                <livewire:requisiciones.aprobar :requisicion="$requisicion" />
            @endif

            {{-- ══ ACCIÓN RECIBIR ══════════════════════════════════════ --}}
            @can('receive', $requisicion)
                @if($requisicion->estado === 'aprobada_final')
                    <div class="flex justify-end">
                        <a href="{{ route('requisiciones.recibir', $requisicion) }}"
                           class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white rounded-xl shadow-lg hover:shadow-xl transition-all"
                           style="background: linear-gradient(135deg, #059669, #047857);">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Registrar recepción
                        </a>
                    </div>
                @endif
            @endcan

        </div>
    </div>
</x-app-layout>