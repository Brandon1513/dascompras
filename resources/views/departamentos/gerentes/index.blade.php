<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Gerentes por departamento
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(session('status'))
                        <div class="p-3 mb-4 text-green-800 bg-green-100 rounded">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-left text-gray-600">
                                <tr class="border-b">
                                    <th class="py-2">Departamento</th>
                                    <th class="py-2">Gerente asignado</th>
                                    <th class="py-2 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($departamentos as $d)
                                    <tr>
                                        <td class="py-2 font-medium">{{ $d->nombre }}</td>
                                        <td class="py-2">
                                            {{ $d->gerente?->name ?? '— Sin asignar —' }}
                                        </td>
                                        <td class="py-2 text-right">
                                            <a href="{{ route('departamentos.gerentes.edit', $d) }}"
                                               class="px-3 py-1 text-white bg-indigo-600 rounded hover:bg-indigo-700">
                                                Asignar / editar
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <p class="mt-4 text-xs text-gray-500">
                        * Esta asignación es la que usará el flujo para “Gerente de área” (departamentos.gerente_id).
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
