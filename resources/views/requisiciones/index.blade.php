<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Requisiciones</h2>
                <p class="text-xs text-gray-400 mt-0.5">Gestión y seguimiento de solicitudes de compra</p>
            </div>
            <div class="flex items-center gap-2">
                @role('administrador|compras')
                <a href="{{ route('requisiciones.exportar', request()->query()) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exportar Excel
                </a>
                @endrole
                <a href="{{ route('requisiciones.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white rounded-lg shadow-md hover:shadow-lg transition-all"
                   style="background: linear-gradient(135deg, #4A1660, #7c3aed);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nueva requisición
                </a>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen py-6" style="background: linear-gradient(160deg, #f8f5ff 0%, #f1f5f9 50%, #f8f5ff 100%);">
        <div class="px-4 mx-auto space-y-4 max-w-7xl sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="flex items-center gap-2 p-4 text-sm font-medium border shadow-sm text-emerald-800 border-emerald-200 rounded-xl bg-emerald-50">
                    <svg class="w-4 h-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('status') }}
                </div>
            @endif

            {{-- Filtros --}}
            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100 bg-gray-50">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span class="text-xs font-bold tracking-wider text-gray-500 uppercase">Filtros</span>
                </div>
                <div class="p-4">
                    <form method="GET" class="grid items-end grid-cols-2 gap-3 md:grid-cols-4 lg:grid-cols-6">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Estado</label>
                            <select name="estado" class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                <option value="">Todos</option>
                                @foreach([
                                    'borrador'            => 'Borrador',
                                    'en_revision_compras' => 'En revisión compras',
                                    'rechazada_compras'   => 'Rechazada por compras',
                                    'aprobada_compras'    => 'Aprobada por compras',
                                    'en_aprobacion'       => 'En aprobación',
                                    'rechazada'           => 'Rechazada',
                                    'aprobada_final'      => 'Aprobada',
                                    'pendiente_cierre'    => 'Pendiente de cierre',
                                    'recibida'            => 'Recibida',
                                    'cancelada'           => 'Cancelada',
                                ] as $val => $label)
                                    <option value="{{ $val }}" @selected(($estado ?? '') === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        @role('administrador|compras|gerente_operaciones|gerencia_adm|jefe')
                        <div class="col-span-2 md:col-span-1">
                            <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Solicitante</label>
                            <select name="solicitante" class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                <option value="">Todos</option>
                                @foreach ($solicitantes as $u)
                                    <option value="{{ $u->id }}" @selected(($solicitante ?? '') == $u->id)>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endrole

                        @role('administrador|compras')
                        <div class="col-span-2 md:col-span-1">
                            <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Método pago</label>
                            <select name="metodo_pago" class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                <option value="">Todos</option>
                                <option value="tarjeta" @selected(($metodo_pago ?? '') === 'tarjeta')>💳 Tarjeta</option>
                                <option value="transferencia" @selected(($metodo_pago ?? '') === 'transferencia')>🏦 Transferencia</option>
                            </select>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tipo</label>
                            <select name="pago_factura" class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                <option value="">Todos</option>
                                <option value="0" @selected(($pago_factura ?? '') === '0')>Requisición</option>
                                <option value="1" @selected(($pago_factura ?? '') === '1')>Pago de factura</option>
                            </select>
                        </div>
                        @endrole

                        <div class="flex items-end col-span-2 gap-2 md:col-span-1">
                            <button class="flex-1 px-3 py-2 text-sm font-semibold text-white transition rounded-lg"
                                    style="background: linear-gradient(135deg, #4A1660, #7c3aed);">Aplicar</button>
                            <a href="{{ route('requisiciones.index') }}"
                               class="flex-1 px-3 py-2 text-sm font-medium text-center text-gray-600 transition bg-gray-100 rounded-lg hover:bg-gray-200">Limpiar</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tabla --}}
            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100" style="background: linear-gradient(90deg, #f9f5ff, #f8f8ff);">
                                <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Folio</th>
                                <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Fecha</th>
                                <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Solicitante</th>
                                <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Artículos</th>
                                <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Estado</th>
                                @role('administrador|compras')
                                <th class="px-4 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pago</th>
                                @endrole
                                <th class="px-4 py-3.5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total</th>
                                <th class="px-4 py-3.5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($requisiciones as $r)
                                @php
                                    $color       = $r->estadoColor;
                                    $canPdf      = in_array($r->estado, ['aprobada_final', 'pendiente_cierre', 'recibida'], true);
                                    $borderColor = match($r->estado) {
                                        'en_revision_compras' => '#7c3aed',
                                        'rechazada_compras'   => '#f97316',
                                        'en_aprobacion'       => '#f59e0b',
                                        'aprobada_final'      => '#10b981',
                                        'pendiente_cierre'    => '#06b6d4',
                                        'recibida'            => '#6366f1',
                                        'rechazada'           => '#ef4444',
                                        default               => 'transparent',
                                    };
                                    // Artículos para mostrar
                                    $items        = $r->items;
                                    $primerItem   = $items->first()?->descripcion ?? '—';
                                    $restoItems   = $items->skip(1)->pluck('descripcion');
                                    $totalItems   = $items->count();
                                @endphp
                                <tr class="transition-colors hover:bg-purple-50/20 group"
                                    style="border-left: 3px solid {{ $borderColor }};">

                                    {{-- Folio --}}
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('requisiciones.show', $r) }}"
                                               class="text-sm font-bold text-indigo-600 hover:text-indigo-800 hover:underline">
                                                {{ $r->folio }}
                                            </a>
                                            @if($r->es_pago_factura)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-violet-100 text-violet-700">FAC</span>
                                            @endif
                                            @if($r->urgencia === 'urgente')
                                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-700" title="Afecta proceso de producción">⚠️ Producción</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-4 py-3.5 text-xs text-gray-500">
                                        <div>{{ \Carbon\Carbon::parse($r->fecha_emision)->format('d/m/Y') }}</div>
                                        <div class="text-[10px] text-gray-400">{{ $r->created_at->format('h:i A') }}</div>
                                    </td>

                                    <td class="px-4 py-3.5 text-sm font-medium text-gray-700">{{ $r->solicitante?->name ?? '—' }}</td>

                                    {{-- Artículos con ver más --}}
                                    <td class="px-4 py-3.5 max-w-[220px]">
                                        <div x-data="{ open: false }">
                                            <p class="text-xs font-medium leading-snug text-gray-700">{{ $primerItem }}</p>
                                            @if($totalItems > 1)
                                                <div x-show="open" x-collapse class="mt-1 space-y-0.5">
                                                    @foreach($restoItems as $desc)
                                                        <p class="text-xs leading-snug text-gray-500">{{ $desc }}</p>
                                                    @endforeach
                                                </div>
                                                <button type="button"
                                                        @click="open = !open"
                                                        class="mt-1 inline-flex items-center gap-0.5 text-[10px] font-semibold text-indigo-500 hover:text-indigo-700 transition-colors">
                                                    <span x-text="open ? 'Ver menos ▲' : '+ {{ $totalItems - 1 }} más ▼'"></span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Estado --}}
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold border
                                                     {{ $color['bg'] }} {{ $color['text'] }} {{ $color['border'] }}">
                                            {{ $r->estado_label }}
                                        </span>
                                    </td>

                                   @role('administrador|compras')
<td class="px-4 py-3.5">
    @php
        // Método general de la requisición
        $metodoBadges = [];
 
        if ($r->metodo_pago) {
            $metodoBadges[$r->metodo_pago] = true;
        }
 
        // Métodos únicos de las partidas (solo los que difieren o no hay general)
        foreach ($r->items as $it) {
            if ($it->metodo_pago) {
                $metodoBadges[$it->metodo_pago] = true;
            }
        }
 
        $iconos = [
            'transferencia' => '🏦',
            'tarjeta'       => '💳',
            'efectivo'      => '💵',
        ];
        $colores = [
            'transferencia' => 'bg-blue-50 text-blue-700',
            'tarjeta'       => 'bg-purple-50 text-purple-700',
            'efectivo'      => 'bg-green-50 text-green-700',
        ];
    @endphp
 
    @if(count($metodoBadges) > 0)
        <div class="flex flex-col gap-1">
            @foreach(array_keys($metodoBadges) as $metodo)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold
                             {{ $colores[$metodo] ?? 'bg-gray-50 text-gray-600' }}">
                    {{ $iconos[$metodo] ?? '' }}
                    {{ ucfirst($metodo) }}
                </span>
            @endforeach
        </div>
    @else
        <span class="text-xs text-gray-300">—</span>
    @endif
</td>
@endrole


                                    <td class="px-4 py-3.5 text-right">
                                        @php
                                            $tieneRetenciones = $r->es_pago_factura && $r->items->sum('monto_retenciones') > 0;
                                            $totalNeto = $r->items->sum(fn($it) => (float)($it->total_neto ?? $it->total_item ?? 0));
                                        @endphp
                                        @if($tieneRetenciones)
                                            <div class="font-bold text-gray-800">${{ number_format($totalNeto, 2) }}</div>
                                            <div class="text-[10px] text-gray-400 text-right">neto</div>
                                        @else
                                            <span class="font-bold text-gray-800">${{ number_format($r->total, 2) }}</span>
                                        @endif
                                    </td>


                                    {{-- Acciones --}}
                                    <td class="px-4 py-3.5 text-right">
                                        <div class="inline-flex items-center gap-1.5 text-xs">
                                            @role('administrador|compras')
                                            @if($r->estado === 'en_revision_compras')
                                                <a href="{{ route('requisiciones.revisar', $r) }}"
                                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md font-semibold text-violet-700 bg-violet-100 hover:bg-violet-200 transition">
                                                    🔍 Revisar
                                                </a>
                                            @endif
                                            @if($r->estado === 'pendiente_cierre')
                                                <a href="{{ route('requisiciones.cerrar', $r) }}"
                                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md font-semibold text-cyan-700 bg-cyan-100 hover:bg-cyan-200 transition">
                                                    ✅ Cerrar
                                                </a>
                                            @endif
                                            @endrole

                                            @can('update', $r)
                                                <a href="{{ route('requisiciones.edit', $r) }}"
                                                   class="px-2.5 py-1 rounded-md font-semibold transition
                                                          {{ $r->estado === 'rechazada_compras'
                                                             ? 'text-orange-700 bg-orange-100 hover:bg-orange-200'
                                                             : 'text-indigo-700 bg-indigo-50 hover:bg-indigo-100' }}">
                                                    {{ $r->estado === 'rechazada_compras' ? '↩️ Corregir' : '✏️ Editar' }}
                                                </a>
                                            @endcan

                                            @can('approve', $r)
                                                <a href="{{ route('requisiciones.show', $r) }}"
                                                   class="px-2.5 py-1 rounded-md font-semibold text-amber-700 bg-amber-100 hover:bg-amber-200 transition">
                                                    ✍️ Aprobar
                                                </a>
                                            @endcan

                                            @can('receive', $r)
                                                <a href="{{ route('requisiciones.recibir', $r) }}"
                                                   class="px-2.5 py-1 rounded-md font-semibold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 transition">
                                                    📦 Recibir
                                                </a>
                                            @endcan

                                            <a href="{{ route('requisiciones.show', $r) }}"
                                               class="px-2.5 py-1 rounded-md font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition">
                                                Ver
                                            </a>

                                            @if($canPdf)
                                                <a href="{{ route('requisiciones.pdf', $r) }}" target="_blank"
                                                   class="px-2.5 py-1 rounded-md font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 transition">
                                                    PDF
                                                </a>
                                            @endif

                                            @if(in_array($r->estado, ['rechazada', 'recibida', 'rechazada_compras']) && $r->solicitante_id === auth()->id())
                                                <a href="{{ route('requisiciones.duplicar', $r) }}"
                                                onclick="return confirm('¿Crear una copia de {{ $r->folio }}? Se abrirá como borrador para que la edites y envíes.')"
                                                class="px-2.5 py-1 rounded-md font-semibold text-teal-700 bg-teal-50 hover:bg-teal-100 transition">
                                                    📋 Copiar
                                                </a>
                                            @endif

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="flex items-center justify-center w-16 h-16 rounded-2xl" style="background: linear-gradient(135deg, #f3e8ff, #ede9fe);">
                                                <svg class="w-8 h-8 text-purple-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                </svg>
                                            </div>
                                            <p class="text-sm font-medium text-gray-400">No hay requisiciones que mostrar</p>
                                            <p class="text-xs text-gray-300">Intenta cambiar los filtros o crea una nueva</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($requisiciones->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                    {{ $requisiciones->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>