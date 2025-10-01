{{-- NO envuelvas con <x-app-layout> en un Livewire page --}}
<div class="py-6">
    <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
        <div class="p-6 bg-white shadow sm:rounded-lg space-y-6">

            <div class="mb-4">
                <h2 class="text-xl font-semibold">
                    Registrar recepción – {{ $requisicion->folio }}
                </h2>
            </div>

            {{-- Resumen --}}
            <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-4">
                <div>
                    <div class="text-gray-500">Fecha</div>
                    <div>{{ optional($requisicion->fecha_emision)->format('Y-m-d') }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Solicitante</div>
                    <div>{{ $requisicion->solicitante?->name }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Departamento</div>
                    <div>{{ $requisicion->departamentoRef?->nombre ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Total</div>
                    <div>${{ number_format($requisicion->total,2) }}</div>
                </div>
            </div>

            {{-- Formulario --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium">Fecha de recibido</label>
                    <input type="date" class="w-full mt-1 border-gray-300 rounded" wire:model="fecha_recibido">
                    @error('fecha_recibido') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium">Área que recibe</label>
                    <select class="w-full mt-1 border-gray-300 rounded" wire:model="area_recibe">
                        <option value="">-- Selecciona --</option>
                        @foreach($departamentos as $dep)
                            <option value="{{ $dep['nombre'] }}">{{ $dep['nombre'] }}</option>
                        @endforeach
                    </select>
                    @error('area_recibe') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end">
                <button wire:click="save" wire:loading.attr="disabled"
                        class="px-4 py-2 text-white bg-indigo-600 rounded hover:bg-indigo-700">
                    <span wire:loading.remove wire:target="save">Guardar recepción</span>
                    <span wire:loading wire:target="save">Guardando…</span>
                </button>
            </div>

        </div>
    </div>
</div>
