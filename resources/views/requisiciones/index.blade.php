{{-- resources/views/requisiciones/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight">Requisiciones</h2>
            <a href="{{ route('requisiciones.create') }}" class="px-4 py-2 text-white bg-indigo-600 rounded hover:bg-indigo-700">
                Nueva requisición
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow sm:rounded-lg">

                {{-- Filtros --}}
                <form method="GET" class="grid items-end gap-3 mb-4 md:grid-cols-4">
                    <div>
                        <label class="block text-sm font-medium">Estado</label>
                        <select name="estado" class="w-full mt-1 border-gray-300 rounded">
                            <option value="">Todos</option>
                            @foreach (['borrador','enviada','en_aprobacion','rechazada','aprobada_final','recibida','cancelada'] as $opt)
                                <option value="{{ $opt }}" @selected(($estado ?? '') === $opt)>{{ ucfirst(str_replace('_',' ', $opt)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Solicitante</label>
                        <select name="solicitante" class="w-full mt-1 border-gray-300 rounded">
                            <option value="">Todos</option>
                            @foreach ($solicitantes as $u)
                                <option value="{{ $u->id }}" @selected(($solicitante ?? '') == $u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button class="px-4 py-2 mt-6 bg-gray-200 rounded hover:bg-gray-300">Aplicar</button>
                        <a href="{{ route('requisiciones.index') }}" class="px-4 py-2 mt-6 bg-white border rounded hover:bg-gray-50">Limpiar</a>
                    </div>
                </form>

                @if (session('status'))
                    <div class="p-3 mb-4 text-green-800 bg-green-100 rounded">{{ session('status') }}</div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="text-xs tracking-wider text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left">Folio</th>
                                <th class="px-3 py-2 text-left">Fecha</th>
                                <th class="px-3 py-2 text-left">Solicitante</th>
                                <th class="px-3 py-2 text-left">Departamento</th>
                                <th class="px-3 py-2 text-left">Estado</th>
                                <th class="px-3 py-2 text-right">Total</th>
                                <th class="px-3 py-2 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($requisiciones as $r)
                                <tr>
                                    <td class="px-3 py-2">{{ $r->folio }}</td>
                                    <td class="px-3 py-2">{{ \Illuminate\Support\Carbon::parse($r->fecha_emision)->format('Y-m-d') }}</td>
                                    <td class="px-3 py-2">{{ $r->solicitante?->name }}</td>
                                    <td class="px-3 py-2">{{ $r->departamentoRef?->nombre ?? '—' }}</td>
                                    <td class="px-3 py-2">
                                        <span class="px-2 py-0.5 rounded text-xs border">
                                            {{ str_replace('_',' ', $r->estado) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-right">${{ number_format($r->total,2) }}</td>
                                    <td class="px-3 py-2 text-right">
                                        @if ($r->estado === 'borrador' && $r->solicitante_id === auth()->id())
                                            <a href="{{ route('requisiciones.edit', $r) }}" class="text-indigo-600 hover:text-indigo-800">Continuar edición</a>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $requisiciones->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
