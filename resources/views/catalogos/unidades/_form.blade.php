{{-- resources/views/catalogos/unidades/_form.blade.php --}}
{{-- Uso: @include('catalogos.unidades._form', ['unidad' => $unidad ?? null, 'action' => $action, 'method' => $method]) --}}

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
            value="{{ old('nombre', $unidad->nombre ?? '') }}"
            placeholder="Ej: Pieza, Kilogramo, Litro"
            class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500
                   @error('nombre') border-red-400 @enderror"
        >
        @error('nombre')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Abreviatura --}}
    <div>
        <label for="abreviatura" class="block mb-1 text-sm font-medium text-gray-700">
            Abreviatura <span class="text-red-500">*</span>
        </label>
        <input
            type="text"
            id="abreviatura"
            name="abreviatura"
            value="{{ old('abreviatura', $unidad->abreviatura ?? '') }}"
            placeholder="Ej: PZA, KG, LT"
            maxlength="20"
            class="w-40 rounded-lg border-gray-300 shadow-sm text-sm font-mono uppercase
                   focus:ring-indigo-500 focus:border-indigo-500
                   @error('abreviatura') border-red-400 @enderror"
            oninput="this.value = this.value.toUpperCase()"
        >
        <p class="mt-1 text-xs text-gray-400">Se guardará en mayúsculas. Máx. 20 caracteres.</p>
        @error('abreviatura')
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
            {{ old('activo', $unidad->activo ?? true) ? 'checked' : '' }}
            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
        >
        <label for="activo" class="text-sm text-gray-700">Activa (disponible para seleccionar en requisiciones)</label>
    </div>

    {{-- Botones --}}
    <div class="flex items-center gap-3 pt-2">
        <button type="submit"
                class="px-5 py-2 text-sm font-medium text-white transition bg-indigo-600 rounded-lg hover:bg-indigo-700">
            Guardar
        </button>
        <a href="{{ route('catalogos.unidades.index') }}"
           class="px-5 py-2 text-sm font-medium text-gray-700 transition bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            Cancelar
        </a>
    </div>
</form>