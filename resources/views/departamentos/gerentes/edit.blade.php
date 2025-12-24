<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Asignar gerente — {{ $departamento->nombre }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('departamentos.gerentes.update', $departamento) }}">
                        @csrf
                        @method('PUT')

                        <label class="block text-sm font-medium text-gray-700">
                            Selecciona gerente
                        </label>

                        <select name="gerente_id" class="w-full mt-1 border-gray-300 rounded">
                            <option value="">— Sin asignar —</option>
                            @foreach($usuarios as $u)
                                <option value="{{ $u->id }}"
                                    @selected(old('gerente_id', $departamento->gerente_id) == $u->id)>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('gerente_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <div class="flex items-center justify-between mt-6">
                            <a href="{{ route('departamentos.gerentes.index') }}" class="text-sm text-gray-600 hover:underline">
                                Volver
                            </a>

                            <button class="px-4 py-2 text-white bg-indigo-600 rounded hover:bg-indigo-700">
                                Guardar
                            </button>
                        </div>

                        <p class="mt-4 text-xs text-gray-500">
                            Tip: El gerente asignado aquí es quien firma el paso “Gerente de área” del flujo.
                            Puede ser también “Jefe”, no hay conflicto.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
