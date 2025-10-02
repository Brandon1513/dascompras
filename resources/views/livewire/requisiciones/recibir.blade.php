<div class="max-w-5xl mx-auto space-y-6">
    {{-- Título --}}
    <div>
        <h1 class="text-2xl font-semibold">
            Registrar recepción — {{ $requisicion->folio }}
        </h1>
    </div>

    {{-- Resumen --}}
    <div class="p-4 bg-white border rounded">
        <dl class="grid grid-cols-1 gap-4 text-sm md:grid-cols-4">
            <div>
                <dt class="text-gray-500">Fecha</dt>
                <dd class="font-medium">{{ optional($requisicion->fecha_emision)->format('Y-m-d') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Solicitante</dt>
                <dd class="font-medium">{{ $requisicion->solicitante?->name }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Departamento</dt>
                <dd class="font-medium">{{ $requisicion->departamentoRef?->nombre ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Centro de costos</dt>
                <dd class="font-medium">{{ $requisicion->centroCostoRef?->nombre ?? '—' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Partidas solicitadas --}}
    <div class="p-4 bg-white border rounded">
        <h3 class="mb-3 text-sm font-medium text-gray-700">Partidas de la requisición</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-gray-600">
                    <tr>
                        <th class="py-2">Cant.</th>
                        <th class="py-2">Descripción</th>
                        <th class="py-2">Unidad</th>
                        <th class="py-2 text-right">P. unit</th>
                        <th class="py-2 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($requisicion->items as $it)
                        <tr>
                            <td class="py-2">
                                {{ rtrim(rtrim(number_format($it->cantidad,3,'.',''), '0'), '.') }}
                            </td>
                            <td class="py-2">
                                {{ $it->descripcion }}
                                @if($it->link_compra)
                                    <a href="{{ $it->link_compra }}" class="ml-2 text-indigo-600" target="_blank" rel="noopener">link</a>
                                @endif
                            </td>
                            <td class="py-2">{{ $it->unidad }}</td>
                            <td class="py-2 text-right">${{ number_format($it->precio_unitario,2) }}</td>
                            <td class="py-2 text-right">${{ number_format($it->subtotal,2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500">Sin partidas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Totales --}}
        <div class="mt-4 ml-auto text-right max-w-64">
            <div class="flex justify-between text-sm">
                <span>Subtotal</span>
                <strong>${{ number_format($requisicion->subtotal,2) }}</strong>
            </div>
            <div class="flex justify-between text-sm">
                <span>IVA (16%)</span>
                <strong>${{ number_format($requisicion->iva,2) }}</strong>
            </div>
            <div class="flex justify-between text-lg">
                <span>Total</span>
                <strong>${{ number_format($requisicion->total,2) }}</strong>
            </div>
        </div>
    </div>

    {{-- Formulario de recepción --}}
    <div class="p-4 bg-white border rounded">
        @if (session('status'))
            <div class="p-3 mb-4 text-green-800 bg-green-100 rounded">{{ session('status') }}</div>
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium">Fecha de recibido</label>
                <input type="date"
                       wire:model.defer="fecha_recibido"
                       class="w-full mt-1 border-gray-300 rounded">
                @error('fecha_recibido') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium">Área que recibe</label>
                <select wire:model.defer="area_recibe" class="w-full mt-1 border-gray-300 rounded">
                    <option value="{{ $area_recibe }}">{{ $area_recibe }}</option>
                    @foreach($departamentos as $d)
                        <option value="{{ $d['nombre'] }}">{{ $d['nombre'] }}</option>
                    @endforeach
                </select>
                @error('area_recibe') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-6">
            <button wire:click="save" wire:loading.attr="disabled"
                        class="px-4 py-2 text-white bg-indigo-600 rounded hover:bg-indigo-700">
                    <span wire:loading.remove wire:target="save">Guardar recepción</span>
                    <span wire:loading wire:target="save">Guardando…</span>
                </button>
        </div>
    </div>
</div>
