<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Catálogo — Unidades de Medida
            </h2>
            <a href="{{ route('catalogos.unidades.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva unidad
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl px-4 mx-auto space-y-4 sm:px-6 lg:px-8">

            {{-- Flash --}}
            @if(session('success'))
                <div class="flex items-center gap-2 p-4 text-sm text-green-800 border border-green-200 rounded-lg bg-green-50">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Tabla --}}
            <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
                <table class="min-w-full text-sm divide-y divide-gray-200">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 font-medium tracking-wider text-left">Nombre</th>
                            <th class="px-5 py-3 font-medium tracking-wider text-left">Abreviatura</th>
                            <th class="px-5 py-3 font-medium tracking-wider text-center">Estado</th>
                            <th class="px-5 py-3 font-medium tracking-wider text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($unidades as $unidad)
                            <tr class="transition hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $unidad->nombre }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-block px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-mono font-semibold text-xs">
                                        {{ $unidad->abreviatura }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if($unidad->activo)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-medium">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 text-xs font-medium">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactiva
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('catalogos.unidades.edit', $unidad) }}"
                                           class="font-medium text-indigo-600 hover:text-indigo-800">Editar</a>

                                        <form action="{{ route('catalogos.unidades.toggle', $unidad) }}"
                                              method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="{{ $unidad->activo ? 'text-amber-600 hover:text-amber-800' : 'text-green-600 hover:text-green-800' }} font-medium">
                                                {{ $unidad->activo ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-gray-400">
                                    No hay unidades registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($unidades->hasPages())
                <div>{{ $unidades->links() }}</div>
            @endif

        </div>
    </div>
</x-app-layout>