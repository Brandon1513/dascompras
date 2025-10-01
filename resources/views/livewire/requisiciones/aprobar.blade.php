<div class="max-w-6xl mx-auto space-y-6">
    {{-- BANNER: quién tiene la aprobación pendiente --}}
    @if($apPendiente)
        <div class="p-3 text-sm rounded bg-amber-50 text-amber-900 border border-amber-200">
            <span class="font-semibold">Pendiente</span>:
            {{ $apPendiente->nivel?->nombre ?? '—' }}
            <span class="text-amber-700"> · </span>
            {{ $apPendiente->aprobador?->name ?? 'Por rol: ' . ($apPendiente->nivel?->rol_aprobador ?? '—') }}
        </div>
    @endif

    {{-- RESUMEN --}}
    <div class="p-4 bg-white border rounded">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">Requisición {{ $requisicion->folio }}</h2>
            <span class="px-2 py-0.5 text-xs border rounded">
                {{ str_replace('_',' ', $requisicion->estado) }}
            </span>
        </div>

        <dl class="grid grid-cols-1 mt-4 text-sm md:grid-cols-4 gap-x-6 gap-y-2">
            <div>
                <dt class="text-gray-500">Fecha de elaboración</dt>
                <dd>{{ optional($requisicion->fecha_emision)->format('Y-m-d') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Solicitante</dt>
                <dd>{{ $requisicion->solicitante?->name }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Departamento</dt>
                <dd>{{ $requisicion->departamentoRef?->nombre ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Centro de costos</dt>
                <dd>{{ $requisicion->centroCostoRef?->nombre ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Urgencia</dt>
                <dd class="capitalize">{{ $requisicion->urgencia }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Fecha requerida</dt>
                <dd>{{ optional($requisicion->fecha_requerida)->format('Y-m-d') ?: '—' }}</dd>
            </div>
        </dl>
    </div>

    {{-- JUSTIFICACIÓN --}}
    <div class="p-4 bg-white border rounded">
        <h3 class="mb-2 text-sm font-medium text-gray-700">Justificación de la compra</h3>
        <div class="p-3 text-sm bg-gray-50 rounded border">
            {{ $requisicion->justificacion ?: '—' }}
        </div>
    </div>

    {{-- PARTIDAS --}}
    <div class="p-4 bg-white border rounded overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-gray-600">
                <tr class="text-left">
                    <th class="px-3 py-2">Cant.</th>
                    <th class="px-3 py-2">Descripción</th>
                    <th class="px-3 py-2">Unidad</th>
                    <th class="px-3 py-2 text-right">P. Unitario</th>
                    <th class="px-3 py-2 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($requisicion->items as $it)
                    <tr>
                        <td class="px-3 py-2">
                            {{ rtrim(rtrim(number_format($it->cantidad,3,'.',''), '0'), '.') }}
                        </td>
                        <td class="px-3 py-2">
                            {{ $it->descripcion }}
                            @if($it->link_compra)
                                <a href="{{ $it->link_compra }}" class="ml-2 text-indigo-600 hover:underline" target="_blank" rel="noopener">link</a>
                            @endif
                        </td>
                        <td class="px-3 py-2">{{ $it->unidad ?: '—' }}</td>
                        <td class="px-3 py-2 text-right">${{ number_format($it->precio_unitario, 2) }}</td>
                        <td class="px-3 py-2 text-right">${{ number_format($it->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totales --}}
        <div class="grid justify-end mt-4">
            <div class="p-4 text-sm bg-gray-50 border rounded w-full max-w-xs">
                <div class="flex justify-between"><span>Subtotal</span><strong>${{ number_format($requisicion->subtotal,2) }}</strong></div>
                <div class="flex justify-between"><span>IVA (16%)</span><strong>${{ number_format($requisicion->iva,2) }}</strong></div>
                <div class="flex justify-between text-base"><span>Total</span><strong>${{ number_format($requisicion->total,2) }}</strong></div>
            </div>
        </div>
    </div>

    {{-- CADENA DE APROBACIONES / HISTORIAL --}}
    <div class="p-4 bg-white border rounded">
        <h3 class="mb-3 text-sm font-medium text-gray-700">Cadena de aprobaciones</h3>
        <ul class="space-y-2 text-sm">
            @foreach($requisicion->aprobaciones->sortBy('created_at') as $ap)
                <li class="flex items-center justify-between">
                    <div>
                        <span class="font-medium">{{ $ap->nivel?->nombre ?? '—' }}</span>
                        <span class="text-gray-500"> · </span>
                        <span>{{ $ap->aprobador?->name ?? 'Por rol: ' . ($ap->nivel?->rol_aprobador ?? '—') }}</span>
                        @if($ap->firmado_en)
                            <span class="text-gray-500"> · </span>
                            <span>{{ $ap->firmado_en->format('Y-m-d H:i') }}</span>
                        @endif
                    </div>
                    <span @class([
                        'px-2 py-0.5 rounded border',
                        'bg-amber-50 text-amber-800 border-amber-200' => $ap->estado==='pendiente',
                        'bg-emerald-50 text-emerald-800 border-emerald-200' => $ap->estado==='aprobada',
                        'bg-rose-50 text-rose-800 border-rose-200' => $ap->estado==='rechazada',
                    ])>
                        {{ $ap->estado }}
                    </span>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- FORMULARIO APROBAR/RECHAZAR --}}
    @if($apPendiente)
        <div class="p-4 bg-white border rounded">
            @if (session('status'))
                <div class="p-3 mb-3 text-green-800 bg-green-100 border border-green-200 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <label class="block mb-2 text-sm font-medium text-gray-700">Comentarios (opcional)</label>
            <textarea
                class="w-full border rounded focus:ring-0 focus:border-gray-400"
                rows="4"
                wire:model.defer="comentarios"
                placeholder="Escribe comentarios para dejar rastro de la decisión…"
            ></textarea>

            <div class="flex items-center gap-3 mt-4">
                <button
                    type="button"
                    wire:click="approve"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-white bg-emerald-600 rounded hover:bg-emerald-700"
                >
                    <span wire:loading.remove wire:target="approve">Aprobar</span>
                    <span wire:loading wire:target="approve">Guardando…</span>
                </button>

                <button
                    type="button"
                    wire:click="reject"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-white bg-rose-600 rounded hover:bg-rose-700"
                >
                    <span wire:loading.remove wire:target="reject">Rechazar</span>
                    <span wire:loading wire:target="reject">Guardando…</span>
                </button>
            </div>
        </div>
    @endif
</div>
