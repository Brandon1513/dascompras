<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Usuarios</h2>
                <p class="text-xs text-gray-400 mt-0.5">Gestión de usuarios y permisos del sistema</p>
            </div>
            <a href="{{ route('empleados.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white rounded-lg shadow-md hover:shadow-lg transition-all"
               style="background: linear-gradient(135deg, #4A1660, #7c3aed);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Agregar usuario
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen py-6" style="background: linear-gradient(160deg, #f8f5ff 0%, #f1f5f9 50%, #f8f5ff 100%);">
        <div class="px-4 mx-auto space-y-4 max-w-7xl sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="flex items-center gap-2 p-4 text-sm font-medium border shadow-sm text-emerald-800 border-emerald-200 rounded-xl bg-emerald-50">
                    <svg class="w-4 h-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="flex items-center gap-2 p-4 text-sm font-medium text-red-800 border border-red-200 shadow-sm rounded-xl bg-red-50">
                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Buscador --}}
            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100 bg-gray-50">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z"/>
                    </svg>
                    <span class="text-xs font-bold tracking-wider text-gray-500 uppercase">Buscar</span>
                </div>
                <div class="p-4">
                    <form method="GET" action="{{ route('empleados.index') }}" class="flex gap-3">
                        <input type="text" name="search" placeholder="Buscar por nombre o correo…"
                               value="{{ request('search') }}"
                               class="flex-1 text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                        <button class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white rounded-lg transition"
                                style="background: linear-gradient(135deg, #4A1660, #7c3aed);">
                            Buscar
                        </button>
                        @if(request('search'))
                        <a href="{{ route('empleados.index') }}"
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 transition bg-gray-100 rounded-lg hover:bg-gray-200">
                            Limpiar
                        </a>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Tabla --}}
            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100" style="background: linear-gradient(90deg, #f9f5ff, #f8f8ff);">
                                <th class="px-5 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nombre</th>
                                <th class="px-5 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Correo</th>
                                <th class="px-5 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Roles</th>
                                <th class="px-5 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Departamento</th>
                                <th class="px-5 py-3.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Estado</th>
                                <th class="px-5 py-3.5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($users as $user)
                                <tr class="transition-colors hover:bg-purple-50/20">
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center justify-center w-8 h-8 text-xs font-bold text-white rounded-full shrink-0"
                                                 style="background: linear-gradient(135deg, #4A1660, #7c3aed);">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <span class="font-semibold text-gray-800">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-sm text-gray-500">{{ $user->email }}</td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($user->getRoleNames() as $role)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-violet-100 text-violet-700">
                                                    {{ $role }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-sm text-gray-500">
                                        {{ $user->departamento?->nombre ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if($user->activo)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Activo
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-500 border border-gray-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                                Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ route('empleados.edit', $user->id) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">
                                                ✏️ Editar
                                            </a>
                                            @if(auth()->id() !== $user->id)
                                            <form action="{{ route('empleados.toggle', $user->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('¿{{ $user->activo ? 'Inactivar' : 'Activar' }} a {{ $user->name }}?')">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg transition
                                                               {{ $user->activo
                                                                  ? 'text-gray-600 bg-gray-100 hover:bg-gray-200'
                                                                  : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100' }}">
                                                    {{ $user->activo ? '🔒 Inactivar' : '✅ Activar' }}
                                                </button>
                                            </form>
                                            @else
                                            <span class="inline-flex items-center px-3 py-1.5 text-xs text-gray-300 cursor-not-allowed" title="No puedes inactivar tu propia cuenta">
                                                🔒 Tu cuenta
                                            </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="flex items-center justify-center w-16 h-16 rounded-2xl" style="background: linear-gradient(135deg, #f3e8ff, #ede9fe);">
                                                <svg class="w-8 h-8 text-purple-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                                                </svg>
                                            </div>
                                            <p class="text-sm font-medium text-gray-400">No hay usuarios que mostrar</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($users->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
                    {{ $users->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>