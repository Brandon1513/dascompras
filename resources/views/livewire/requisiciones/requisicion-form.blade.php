<div class="space-y-6">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="md:col-span-1">
            <label class="block text-sm font-medium">Fecha de elaboración</label>
            <input type="date" class="w-full mt-1 border-gray-300 rounded" wire:model.lazy="fecha_emision">
            @error('fecha_emision') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-1">
            <label class="block text-sm font-medium">Urgencia</label>
            <select class="w-full mt-1 border-gray-300 rounded" wire:model="urgencia">
                <option value="normal">Normal</option>
                <option value="urgente">Urgente</option>
            </select>
            @error('urgencia') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium">Solicitante</label>
            <input type="text" class="w-full mt-1 bg-gray-100 border-gray-300 rounded" value="{{ $solicitante_nombre }}" readonly>
        </div>
    </div>

    <div>
    <label class="block text-sm font-medium">Departamento quien solicita</label>
    <select class="w-full mt-1 border-gray-300 rounded" wire:model="departamento_id">
        <option value="">-- Selecciona --</option>
        @foreach ($departamentos as $dep)
            <option value="{{ $dep['id'] }}">{{ $dep['nombre'] }}</option>
        @endforeach
    </select>
    @error('departamento_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-medium">Centro de costos (Departamento)</label>
    <select class="w-full mt-1 border-gray-300 rounded" wire:model="centro_costo_id">
        <option value="">-- Selecciona --</option>
        @foreach ($departamentos as $dep)
            <option value="{{ $dep['id'] }}">{{ $dep['nombre'] }}</option>
        @endforeach
    </select>
    @error('centro_costo_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
</div>


    <div>
        <label class="block text-sm font-medium">Justificación de la compra</label>
        <textarea rows="3" class="w-full mt-1 border-gray-300 rounded"
                  wire:model.lazy="justificacion"
                  placeholder="Describe la necesidad o motivo de la compra..."></textarea>
        @error('justificacion') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr class="text-xs tracking-wider text-gray-700 uppercase">
                    <th class="px-3 py-2 text-right">Cantidad</th>
                    <th class="px-3 py-2 text-left">Descripción</th>
                    <th class="px-3 py-2 text-left">Unidad</th>
                    
                    <th class="px-3 py-2 text-right">Precio unitario</th>
                    <th class="px-3 py-2 text-left">Link</th>
                    <th class="px-3 py-2 text-right">Subtotal</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($items as $i => $row)
                    <tr wire:key="row-{{ $i }}">
                        <td class="px-3 py-2 text-right">
                            <input type="number" step="0.001" min="0" class="w-24 text-right border-gray-300 rounded"
                                   wire:model.debounce.300ms="items.{{ $i }}.cantidad">
                            @error("items.$i.cantidad") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" class="w-full border-gray-300 rounded"
                                   wire:model.lazy="items.{{ $i }}.descripcion"
                                   placeholder="Descripción del producto o servicio">
                            @error("items.$i.descripcion") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" class="w-full border-gray-300 rounded"
                                   wire:model.lazy="items.{{ $i }}.unidad" placeholder="PZ, CJ, LTS...">
                            @error("items.$i.unidad") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </td>
                        
                        <td class="px-3 py-2 text-right">
                            <input type="number" step="0.01" min="0" class="text-right border-gray-300 rounded w-28"
                                   wire:model.debounce.300ms="items.{{ $i }}.precio_unitario">
                            @error("items.$i.precio_unitario") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </td>
                        <td class="px-3 py-2">
                            <input type="url" class="w-full border-gray-300 rounded"
                                   wire:model.lazy="items.{{ $i }}.link_compra"
                                   placeholder="https://...">
                            @error("items.$i.link_compra") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </td>
                        <td class="px-3 py-2 text-right">
                            ${{ number_format($row['subtotal'] ?? 0, 2) }}
                        </td>
                        <td class="px-3 py-2 text-right">
                            <button type="button" class="text-red-600 hover:text-red-800"
                                    wire:click="removeItem({{ $i }})">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-3">
            <button type="button" class="px-3 py-2 bg-gray-100 rounded hover:bg-gray-200"
                    wire:click="addItem">+ Agregar partida</button>
        </div>
    </div>

    <div class="grid justify-end gap-4 md:grid-cols-3">
        <div class="p-4 border rounded md:col-start-2 bg-gray-50">
            <div class="flex justify-between"><span>Subtotal</span><strong>${{ number_format($subtotal,2) }}</strong></div>
            <div class="flex justify-between"><span>IVA (16%)</span><strong>${{ number_format($iva,2) }}</strong></div>
            <div class="flex justify-between text-lg"><span>Total</span><strong>${{ number_format($total,2) }}</strong></div>
        </div>

        <div class="flex items-end gap-3">
           <button type="button" wire:click.prevent="saveDraft"
        wire:loading.attr="disabled" wire:target="saveDraft"
        class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
            Guardar borrador
            </button>

            <button type="button" wire:click.prevent="sendToApproval"
                    wire:loading.attr="disabled" wire:target="sendToApproval"
                    class="px-4 py-2 text-white bg-indigo-600 rounded hover:bg-indigo-700">
            <span wire:loading.remove wire:target="sendToApproval">Enviar a aprobación</span>
            <span wire:loading wire:target="sendToApproval">Enviando…</span>
            </button>
        </div>
    </div>
</div>
