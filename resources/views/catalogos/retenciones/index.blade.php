<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Catálogo — Tipos de Retención
            </h2>
            <a href="{{ route('catalogos.retenciones.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva retención
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl px-4 mx-auto space-y-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="flex items-center gap-2 p-4 text-sm text-green-800 border border-green-200 rounded-lg bg-green-50">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
                <table class="min-w-full text-sm divide-y divide-gray-200">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 font-medium tracking-wider text-left">Nombre</th>
                            <th class="px-5 py-3 font-medium tracking-wider text-left">Clave</th>
                            <th class="px-5 py-3 font-medium tracking-wider text-right">Porcentaje</th>
                            <th class="px-5 py-3 font-medium tracking-wider text-center">Estado</th>
                            <th class="px-5 py-3 font-medium tracking-wider text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($retenciones as $retencion)
                            <tr class="transition hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $retencion->nombre }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-block px-2 py-0.5 rounded bg-rose-50 text-rose-700 font-mono font-semibold text-xs">
                                        {{ $retencion->clave }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 font-semibold text-right text-gray-800">
                                    {{ number_format($retencion->porcentaje, 3) }}%
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if($retencion->activo)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-medium">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 text-xs font-medium">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('catalogos.retenciones.edit', $retencion) }}"
                                           class="font-medium text-indigo-600 hover:text-indigo-800">Editar</a>

                                        <form action="{{ route('catalogos.retenciones.toggle', $retencion) }}"
                                              method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="{{ $retencion->activo ? 'text-amber-600 hover:text-amber-800' : 'text-green-600 hover:text-green-800' }} font-medium">
                                                {{ $retencion->activo ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-gray-400">
                                    No hay tipos de retención registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($retenciones->hasPages())
                <div>{{ $retenciones->links() }}</div>
            @endif

        </div>
    </div>
</x-app-layout>