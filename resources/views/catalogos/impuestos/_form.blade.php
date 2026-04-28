{{-- resources/views/catalogos/impuestos/_form.blade.php --}}

<form action="{{ $action }}" method="POST" class="space-y-5">
    @csrf
    @method($method)

    {{-- Nombre --}}
    <div>
        <label for="nombre" class="block mb-1 text-sm font-medium text-gray-700">
            Nombre <span class="text-red-500">*</span>
        </label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            value="{{ old('nombre', $impuesto->nombre ?? '') }}"
            placeholder="Ej: IVA 16%, IEPS 8%, Exento"
            class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500
                   @error('nombre') border-red-400 @enderror"
        >
        @error('nombre')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Clave --}}
    <div>
        <label for="clave" class="block mb-1 text-sm font-medium text-gray-700">
            Clave interna <span class="text-red-500">*</span>
        </label>
        <input
            type="text"
            id="clave"
            name="clave"
            value="{{ old('clave', $impuesto->clave ?? '') }}"
            placeholder="Ej: IVA_16, IEPS_8, EXENTO"
            maxlength="30"
            class="w-48 rounded-lg border-gray-300 shadow-sm text-sm font-mono uppercase
                   focus:ring-indigo-500 focus:border-indigo-500
                   @error('clave') border-red-400 @enderror"
            oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9_]/g, '')"
        >
        <p class="mt-1 text-xs text-gray-400">Solo mayúsculas, números y guion bajo. Se usa internamente.</p>
        @error('clave')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Porcentaje --}}
    <div>
        <label for="porcentaje" class="block mb-1 text-sm font-medium text-gray-700">
            Porcentaje <span class="text-red-500">*</span>
        </label>
        <div class="flex items-center gap-2">
            <input
                type="number"
                id="porcentaje"
                name="porcentaje"
                value="{{ old('porcentaje', $impuesto->porcentaje ?? '') }}"
                placeholder="0.00"
                min="0"
                max="100"
                step="0.01"
                class="w-32 rounded-lg border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500
                       @error('porcentaje') border-red-400 @enderror"
            >
            <span class="font-medium text-gray-500">%</span>
        </div>
        <p class="mt-1 text-xs text-gray-400">Usa 0 para exento o tasa cero.</p>
        @error('porcentaje')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Activo --}}
    <div class="flex items-center gap-3">
        <input
            type="checkbox"
            id="activo"
            name="activo"
            value="1"
            {{ old('activo', $impuesto->activo ?? true) ? 'checked' : '' }}
            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
        >
        <label for="activo" class="text-sm text-gray-700">Activo (disponible para seleccionar en requisiciones)</label>
    </div>

    {{-- Botones --}}
    <div class="flex items-center gap-3 pt-2">
        <button type="submit"
                class="px-5 py-2 text-sm font-medium text-white transition bg-indigo-600 rounded-lg hover:bg-indigo-700">
            Guardar
        </button>
        <a href="{{ route('catalogos.impuestos.index') }}"
           class="px-5 py-2 text-sm font-medium text-gray-700 transition bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            Cancelar
        </a>
    </div>
</form>