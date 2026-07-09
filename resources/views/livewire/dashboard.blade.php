<div>
    {{-- ══ SELECTOR DE PERÍODO ════════════════════════════════════════════ --}}
    <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="flex flex-wrap items-center gap-3 px-5 py-3">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider shrink-0">Período:</span>

            <div class="flex flex-wrap gap-1.5">
                @foreach([
                    'mes_actual'   => 'Este mes',
                    'mes_anterior' => 'Mes anterior',
                    'ultimos_3'    => 'Últimos 3 meses',
                    'ultimos_6'    => 'Últimos 6 meses',
                    'anio_actual'  => 'Este año',
                    'personalizado'=> 'Personalizado',
                ] as $val => $label)
                <button wire:click="$set('periodo', '{{ $val }}')"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all border
                               {{ $periodo === $val
                                   ? 'text-white border-transparent shadow-sm'
                                   : 'text-gray-500 border-gray-200 hover:border-purple-200 hover:text-purple-700 hover:bg-purple-50' }}"
                        @if($periodo === $val) style="background: linear-gradient(135deg, #4A1660, #7c3aed);" @endif>
                    {{ $label }}
                </button>
                @endforeach
            </div>

            {{-- Rango personalizado --}}
            @if($periodo === 'personalizado')
            <div class="flex items-center gap-2 ml-auto">
                <input type="date" wire:model="fechaDesde"
                       class="text-xs border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500 py-1.5">
                <span class="text-xs text-gray-400">—</span>
                <input type="date" wire:model="fechaHasta"
                       class="text-xs border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500 py-1.5">
                <button wire:click="aplicarPersonalizado"
                        class="px-3 py-1.5 text-xs font-bold text-white rounded-lg transition-all"
                        style="background: linear-gradient(135deg, #4A1660, #7c3aed);">
                    Aplicar
                </button>
            </div>
            @else
            <span class="ml-auto text-xs text-gray-400">
                {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
            </span>
            @endif
        </div>
    </div>

    <div class="mt-5 space-y-5">

        {{-- ══ ACCIÓN REQUERIDA ═══════════════════════════════════════════ --}}
        @if($accionRequerida->count() > 0)
        <div class="overflow-hidden bg-white border-2 shadow-sm border-rose-200 rounded-xl">
            <div class="flex items-center gap-2 px-5 py-3 border-b border-rose-100 bg-rose-50">
                <span class="flex items-center justify-center w-5 h-5 text-xs font-bold text-white rounded-full bg-rose-500 shrink-0">
                    {{ $accionRequerida->count() }}
                </span>
                <h3 class="text-xs font-bold tracking-wider uppercase text-rose-700">Requiere tu acción</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($accionRequerida as $item)
                @php
                    $r = $item['requi'];
                    $config = match($item['accion']) {
                        'corregir' => ['icon' => '↩️', 'label' => 'Corregir', 'texto' => 'Rechazada por Compras — necesita correcciones', 'badge' => 'bg-orange-100 text-orange-700', 'btn' => 'bg-orange-500 hover:bg-orange-600', 'route' => route('requisiciones.edit', $r)],
                        'aprobar'  => ['icon' => '✍️', 'label' => 'Aprobar',  'texto' => 'Esperando tu firma — ' . ($r->solicitante?->name ?? ''), 'badge' => 'bg-amber-100 text-amber-700', 'btn' => 'bg-amber-500 hover:bg-amber-600', 'route' => route('requisiciones.show', $r)],
                        'recibir'  => ['icon' => '📦', 'label' => 'Recibir',  'texto' => 'Todos los artículos fueron entregados', 'badge' => 'bg-emerald-100 text-emerald-700', 'btn' => 'bg-emerald-500 hover:bg-emerald-600', 'route' => route('requisiciones.recibir', $r)],
                        default    => ['icon' => '•', 'label' => '', 'texto' => '', 'badge' => '', 'btn' => '', 'route' => '#'],
                    };
                @endphp
                <div class="flex items-center justify-between px-5 py-3 transition-colors hover:bg-gray-50">
                    <div class="flex items-center min-w-0 gap-3">
                        <span class="text-xl shrink-0">{{ $config['icon'] }}</span>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('requisiciones.show', $r) }}" class="text-sm font-bold text-indigo-600 hover:underline">{{ $r->folio }}</a>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold {{ $config['badge'] }}">{{ $config['label'] }}</span>
                            </div>
                            <p class="max-w-sm text-xs text-gray-400 truncate">{{ $config['texto'] }}</p>
                        </div>
                    </div>
                    <a href="{{ $config['route'] }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-white rounded-lg transition shrink-0 {{ $config['btn'] }}">
                        {{ $config['label'] }} →
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ══ KPIs ════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">Gasto del período</p>
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg" style="background: linear-gradient(135deg, #f3e8ff, #ede9fe);">
                            <svg class="w-4 h-4" style="color: #7c3aed" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-gray-900">${{ number_format($totalPeriodo, 0) }}</p>
                    @if($variacion !== null)
                        <p class="mt-1 text-xs {{ $variacion >= 0 ? 'text-rose-500' : 'text-emerald-500' }}">
                            {{ $variacion >= 0 ? '↑' : '↓' }} {{ number_format(abs($variacion), 1) }}% vs período anterior
                        </p>
                    @else
                        <p class="mt-1 text-xs text-gray-400">{{ $reqsPeriodo }} requisición(es)</p>
                    @endif
                </div>
                <div class="h-1" style="background: linear-gradient(90deg, #4A1660, #7c3aed)"></div>
            </div>

            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">Gasto {{ $hoy->year }}</p>
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-gray-900">${{ number_format($totalAnio, 0) }}</p>
                    <p class="mt-1 text-xs text-gray-400">Acumulado del año</p>
                </div>
                <div class="h-1 bg-blue-400"></div>
            </div>

            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">En aprobación</p>
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-gray-900">{{ $pendientesAprobacion }}</p>
                    @if($sinMovimiento->count() > 0)
                        <p class="mt-1 text-xs text-rose-500">⚠️ {{ $sinMovimiento->count() }} sin movimiento +5 días</p>
                    @else
                        <p class="mt-1 text-xs text-gray-400">Esperando firma(s)</p>
                    @endif
                </div>
                <div class="h-1 bg-amber-400"></div>
            </div>

            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase">Por recibir</p>
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-gray-900">{{ $porRecibir->count() }}</p>
                    <p class="mt-1 text-xs text-gray-400">Artículos entregados, pendientes</p>
                </div>
                <div class="h-1 bg-emerald-400"></div>
            </div>
        </div>

        {{-- ══ GRÁFICAS ════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl lg:col-span-2">
                <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
                     style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
                    <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                    </svg>
                    <h3 class="text-xs font-bold tracking-widest text-white uppercase">Gasto aprobado — últimos 6 meses</h3>
                </div>
                <div class="p-5">
                    <canvas id="chartLinea" height="120"></canvas>
                </div>
            </div>

            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
                     style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
                    <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                    </svg>
                    <h3 class="text-xs font-bold tracking-widest text-white uppercase">Estados del período</h3>
                </div>
                <div class="p-5">
                    @if($estadosMes->isEmpty())
                        <p class="py-8 text-sm text-center text-gray-400">Sin requisiciones en este período</p>
                    @else
                    <canvas id="chartDonut" height="180"></canvas>
                    <div class="mt-3 space-y-1.5">
                        @foreach($estadosMes as $estado => $count)
                        @php $info = $estadoLabels[$estado] ?? [$estado, '#6b7280']; @endphp
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $info[1] }}"></span>
                                <span class="text-gray-600">{{ $info[0] }}</span>
                            </div>
                            <span class="font-bold text-gray-800">{{ $count }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ══ DEPARTAMENTOS + SIN MOVIMIENTO ═════════════════════════════ --}}
        @if($esAdmin || $esJefe)
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            @if($gastoPorDep->count() > 0)
            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
                     style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
                    <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <h3 class="text-xs font-bold tracking-widest text-white uppercase">Gasto por departamento</h3>
                </div>
                <div class="p-5">
                    @php $maxDep = $gastoPorDep->max('total'); @endphp
                    <div class="space-y-3">
                        @foreach($gastoPorDep as $dep)
                        <div>
                            <div class="flex items-center justify-between mb-1 text-xs">
                                <span class="font-medium text-gray-700">{{ $dep->nombre }}</span>
                                <span class="font-bold text-gray-900">${{ number_format($dep->total, 0) }}</span>
                            </div>
                            <div class="h-2 overflow-hidden bg-gray-100 rounded-full">
                                <div class="h-2 transition-all duration-500 rounded-full"
                                     style="width: {{ $maxDep > 0 ? ($dep->total / $maxDep * 100) : 0 }}%; background: linear-gradient(90deg, #4A1660, #7c3aed);"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            @if($sinMovimiento->count() > 0)
            <div class="overflow-hidden bg-white border shadow-sm border-rose-200 rounded-xl">
                <div class="flex items-center gap-2 px-5 py-3 border-b border-rose-100 bg-rose-50">
                    <svg class="w-4 h-4 text-rose-500 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-xs font-bold tracking-wider uppercase text-rose-700">Sin movimiento — más de 5 días</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach($sinMovimiento as $rs)
                    <div class="flex items-center justify-between px-5 py-3 transition-colors hover:bg-rose-50/30">
                        <div class="min-w-0">
                            <a href="{{ route('requisiciones.show', $rs) }}" class="text-sm font-bold text-indigo-600 hover:underline">{{ $rs->folio }}</a>
                            <p class="text-xs text-gray-400">
                                {{ $rs->solicitante?->name ?? '—' }} ·
                                <span class="font-semibold text-rose-500">{{ $rs->updated_at->diffInDays(now()) }} días sin actividad</span>
                            </p>
                        </div>
                        <span class="text-xs font-bold text-gray-700 shrink-0">${{ number_format($rs->total, 0) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- ══ TOP PROVEEDORES ════════════════════════════════════════════ --}}
        @if(($esAdmin || $esJefe) && $topProveedores->count() > 0)
        <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
            <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
                 style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
                <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <h3 class="text-xs font-bold tracking-widest text-white uppercase">Top 5 proveedores — período seleccionado</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">#</th>
                            <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Proveedor</th>
                            <th class="px-5 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">Requis</th>
                            <th class="px-5 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total</th>
                            <th class="px-5 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @php $totalProv = $topProveedores->sum('total'); @endphp
                        @foreach($topProveedores as $i => $prov)
                        <tr class="transition-colors hover:bg-purple-50/20">
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white rounded-full"
                                      style="background: {{ ['#4A1660','#6d28d9','#7c3aed','#a78bfa','#c4b5fd'][$i] ?? '#e5e7eb' }}">
                                    {{ $i + 1 }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-sm font-semibold text-gray-800">{{ $prov->proveedor }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full bg-purple-100 text-purple-700">{{ $prov->num_requis }}</span>
                            </td>
                            <td class="px-5 py-3 text-sm font-bold text-right text-gray-900">${{ number_format($prov->total, 2) }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-20 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-1.5 rounded-full" style="width: {{ $totalProv > 0 ? ($prov->total / $totalProv * 100) : 0 }}%; background: linear-gradient(90deg, #4A1660, #7c3aed);"></div>
                                    </div>
                                    <span class="w-10 text-xs font-semibold text-right text-gray-600">{{ $totalProv > 0 ? number_format($prov->total / $totalProv * 100, 1) : 0 }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-gray-200 bg-gray-50">
                            <td colspan="3" class="px-5 py-2 text-xs font-bold text-gray-500">Total top 5</td>
                            <td class="px-5 py-2 text-sm font-black text-right" style="color: #4A1660">${{ number_format($topProveedores->sum('total'), 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif

        {{-- ══ TOP SOLICITANTES ═══════════════════════════════════════════ --}}
        @if(($esAdmin || $esJefe) && $topSolicitantes->count() > 0)
        <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
            <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
                 style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
                <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <h3 class="text-xs font-bold tracking-widest text-white uppercase">
                    {{ $esJefe ? 'Gasto por colaborador' : 'Gasto por solicitante' }} — período seleccionado
                </h3>
            </div>
            <div class="p-5">
                @php $maxSol = $topSolicitantes->max('total'); $totalSol = $topSolicitantes->sum('total'); @endphp
                <div class="space-y-3">
                    @foreach($topSolicitantes as $i => $sol)
                    @php
                        $colores = ['#4A1660','#6d28d9','#7c3aed','#8b5cf6','#a78bfa','#c4b5fd','#ddd6fe','#ede9fe','#f3e8ff','#faf5ff'];
                        $color = $colores[$i] ?? '#e5e7eb';
                        $pct = $maxSol > 0 ? ($sol->total / $maxSol * 100) : 0;
                        $pctTotal = $totalSol > 0 ? ($sol->total / $totalSol * 100) : 0;
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-8 h-8 text-xs font-bold text-white rounded-full shrink-0"
                             style="background: {{ $color }};">
                            {{ strtoupper(substr($sol->nombre, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-semibold text-gray-700 truncate max-w-[200px]">{{ $sol->nombre }}</span>
                                <div class="flex items-center gap-2 ml-2 shrink-0">
                                    <span class="text-[10px] text-gray-400">{{ $sol->num_requis }} requi(s)</span>
                                    <span class="text-xs font-bold text-gray-900">${{ number_format($sol->total, 0) }}</span>
                                    <span class="text-[10px] font-semibold text-purple-500 w-9 text-right">{{ number_format($pctTotal, 1) }}%</span>
                                </div>
                            </div>
                            <div class="h-1.5 overflow-hidden bg-gray-100 rounded-full">
                                <div class="h-1.5 rounded-full transition-all duration-700" style="width: {{ $pct }}%; background: {{ $color }};"></div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="flex items-center justify-between pt-4 mt-4 border-t border-gray-100">
                    <span class="text-xs font-semibold text-gray-500">Total del equipo en el período</span>
                    <span class="text-sm font-black" style="color: #4A1660">${{ number_format($totalSol, 2) }}</span>
                </div>
            </div>
        </div>
        @endif

        {{-- ══ GRID INFERIOR ═════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            {{-- Últimas requisiciones --}}
            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100"
                     style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <h3 class="text-xs font-bold tracking-widest text-white uppercase">Últimas requisiciones</h3>
                    </div>
                    <a href="{{ route('requisiciones.index') }}" class="text-[10px] text-white/60 hover:text-white transition">Ver todas →</a>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($ultimas as $r)
                    @php
                        $colorEstado = match($r->estado) {
                            'borrador'            => 'bg-gray-100 text-gray-600',
                            'en_revision_compras' => 'bg-violet-100 text-violet-700',
                            'rechazada_compras'   => 'bg-orange-100 text-orange-700',
                            'en_aprobacion'       => 'bg-amber-100 text-amber-700',
                            'rechazada'           => 'bg-rose-100 text-rose-700',
                            'aprobada_final'      => 'bg-emerald-100 text-emerald-700',
                            'pendiente_cierre'    => 'bg-cyan-100 text-cyan-700',
                            'recibida'            => 'bg-indigo-100 text-indigo-700',
                            default               => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <div class="flex items-center justify-between px-5 py-3 transition-colors hover:bg-gray-50">
                        <div class="flex items-center min-w-0 gap-3">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg shrink-0" style="background: linear-gradient(135deg, #f3e8ff, #ede9fe);">
                                <svg class="w-3.5 h-3.5" style="color: #7c3aed" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('requisiciones.show', $r) }}" class="text-sm font-bold text-indigo-600 hover:underline">{{ $r->folio }}</a>
                                <p class="text-xs text-gray-400 truncate max-w-[180px]">{{ $r->items->first()?->descripcion ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1 shrink-0">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold {{ $colorEstado }}">{{ $r->estado_label }}</span>
                            <span class="text-xs font-bold text-gray-700">${{ number_format($r->total, 0) }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="px-5 py-10 text-sm text-center text-gray-400">No hay requisiciones aún.</div>
                    @endforelse
                </div>
                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
                    <a href="{{ route('requisiciones.create') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold" style="color: #4A1660">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nueva requisición
                    </a>
                </div>
            </div>

            {{-- Pendientes + accesos rápidos --}}
            <div class="space-y-4">
                @if($porRecibir->count() > 0)
                <div class="overflow-hidden bg-white border shadow-sm border-emerald-200 rounded-xl">
                    <div class="flex items-center gap-2 px-5 py-3 border-b border-emerald-100 bg-emerald-50">
                        <svg class="w-4 h-4 text-emerald-600 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <h3 class="text-xs font-bold tracking-widest uppercase text-emerald-700">Pendientes de recibir</h3>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @foreach($porRecibir as $rr)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div>
                                <p class="text-sm font-bold text-gray-800">{{ $rr->folio }}</p>
                                <p class="text-xs text-gray-400">{{ $rr->items->count() }} artículo(s) entregados</p>
                            </div>
                            <a href="{{ route('requisiciones.recibir', $rr) }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition">
                                📦 Recibir
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
                    <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
                         style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
                        <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <h3 class="text-xs font-bold tracking-widest text-white uppercase">Accesos rápidos</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-3 p-4">
                        @foreach([
                            ['route' => 'requisiciones.create', 'icon' => 'M12 4v16m8-8H4', 'label' => 'Nueva requi', 'color' => '#7c3aed', 'bg' => 'linear-gradient(135deg, #f3e8ff, #ede9fe)', 'hover' => 'hover:border-purple-200 hover:bg-purple-50 group-hover:text-purple-700'],
                            ['route' => 'requisiciones.index', 'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16', 'label' => 'Mis requis', 'color' => '#3b82f6', 'bg' => '#eff6ff', 'hover' => 'hover:border-blue-200 hover:bg-blue-50 group-hover:text-blue-700'],
                            ['route' => 'profile.edit', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'label' => 'Mi perfil', 'color' => '#6b7280', 'bg' => '#f3f4f6', 'hover' => 'hover:border-gray-200 hover:bg-gray-50'],
                            ['route' => 'ayuda', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'label' => 'Ayuda', 'color' => '#6366f1', 'bg' => '#eef2ff', 'hover' => 'hover:border-indigo-200 hover:bg-indigo-50 group-hover:text-indigo-700'],
                        ] as $acc)
                        <a href="{{ route($acc['route']) }}"
                           class="flex flex-col items-center gap-2 p-4 text-center transition-all border border-gray-100 rounded-xl group {{ $acc['hover'] }}">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl" style="background: {{ $acc['bg'] }};">
                                <svg class="w-5 h-5" style="color: {{ $acc['color'] }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $acc['icon'] }}"/>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-gray-700">{{ $acc['label'] }}</span>
                        </a>
                        @endforeach

                        @role('administrador|compras')
                        <a href="{{ route('empleados.index') }}" class="flex flex-col items-center gap-2 p-4 text-center transition-all border border-gray-100 rounded-xl hover:border-violet-200 hover:bg-violet-50 group">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-violet-50">
                                <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-gray-700 group-hover:text-violet-700">Usuarios</span>
                        </a>
                        <a href="{{ route('requisiciones.exportar') }}" class="flex flex-col items-center gap-2 p-4 text-center transition-all border border-gray-100 rounded-xl hover:border-emerald-200 hover:bg-emerald-50 group">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-50">
                                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-gray-700 group-hover:text-emerald-700">Exportar</span>
                        </a>
                        @endrole
                    </div>
                </div>
            </div>
        </div>

    </div>

    @script
    <script>
        let chartLinea = null;
        let chartDonut = null;

        function iniciarGraficas() {
            // Línea
            const ctxLinea = document.getElementById('chartLinea');
            if (ctxLinea) {
                if (chartLinea) chartLinea.destroy();
                chartLinea = new Chart(ctxLinea, {
                    type: 'line',
                    data: {
                        labels: @json($mesesLabels),
                        datasets: [{
                            label: 'Gasto aprobado',
                            data: @json($mesesData),
                            borderColor: '#7c3aed',
                            backgroundColor: 'rgba(124, 58, 237, 0.08)',
                            borderWidth: 2.5,
                            pointBackgroundColor: '#4A1660',
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.4,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: ctx => ' $' + ctx.parsed.y.toLocaleString('es-MX', { minimumFractionDigits: 2 }) } }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 10 }, callback: val => '$' + val.toLocaleString('es-MX') } },
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                        }
                    }
                });
            }

            // Donut
            const ctxDonut = document.getElementById('chartDonut');
            if (ctxDonut && @json(count($donutData)) > 0) {
                if (chartDonut) chartDonut.destroy();
                chartDonut = new Chart(ctxDonut, {
                    type: 'doughnut',
                    data: {
                        labels: @json($donutLabels),
                        datasets: [{ data: @json($donutData), backgroundColor: @json($donutColors), borderWidth: 2, borderColor: '#ffffff', hoverOffset: 4 }]
                    },
                    options: {
                        responsive: true,
                        cutout: '65%',
                        plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } } }
                    }
                });
            }
        }

        // Cargar Chart.js si no está y luego iniciar
        if (typeof Chart === 'undefined') {
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
            s.onload = iniciarGraficas;
            document.head.appendChild(s);
        } else {
            iniciarGraficas();
        }

        // Re-iniciar gráficas cuando Livewire actualiza el componente
        document.addEventListener('livewire:updated', () => {
            setTimeout(iniciarGraficas, 100);
        });
    </script>
    @endscript
</div>