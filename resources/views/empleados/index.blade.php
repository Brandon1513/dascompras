<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Usuarios Registrados') }}
        </h2>
    </x-slot>

    <div class="py-12 " style="background-image: url('{{ asset('images/background-pattern.png') }}');">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <!-- Mensaje de éxito -->
                @if (session('success'))
                    <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Barra de búsqueda -->
                    <form method="GET" action="{{ route('empleados.index') }}" class="mb-4">
                        <input type="text" name="search" placeholder="Buscar empleado..." value="{{ request('search') }}" 
                               class="block w-full px-4 py-2 text-sm border-gray-300 rounded-md shadow-sm">
                    </form>

                    <!-- Botón para agregar un nuevo usuario -->
                    <a href="{{ route('empleados.create') }}" class="inline-flex items-center px-4 py-2 mb-4 text-xs font-semibold tracking-widest text-white uppercase transition bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring focus:ring-blue-200 disabled:opacity-25">
                        {{ __('Agregar Usuario') }}
                    </a>
                    
                    <!-- Contenedor para hacer la tabla desplazable horizontalmente en pantallas pequeñas -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase border-b border-gray-200">
                                        Nombre
                                    </th>
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase border-b border-gray-200">
                                        Email
                                    </th>
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase border-b border-gray-200">
                                        Estado
                                    </th>
                                    <th class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase border-b border-gray-200">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td class="px-4 py-4 border-b border-gray-200 whitespace-nowrap">
                                            {{ $user->name }}
                                        </td>
                                        <td class="px-4 py-4 border-b border-gray-200 whitespace-nowrap">
                                            {{ $user->email }}
                                        </td>
                                        <td class="px-4 py-4 border-b border-gray-200 whitespace-nowrap">
                                            {{ $user->activo ? 'Activo' : 'Inactivo' }}
                                        </td>
                                        <td class="px-4 py-4 border-b border-gray-200 whitespace-nowrap">
                                            <!-- Botón para editar -->
                                            <a href="{{ route('empleados.edit', $user->id) }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition bg-yellow-500 border border-transparent rounded-md hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:border-yellow-700 focus:ring focus:ring-yellow-200 disabled:opacity-25">
                                                Editar
                                            </a>

                                            <!-- Botón para activar/inactivar -->
                                            <form action="{{ route('empleados.toggle', $user->id) }}" method="POST" style="display:inline;" onsubmit="return confirmToggle('{{ $user->activo ? 'inactivar' : 'activar' }}', '{{ $user->name }}');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition {{ $user->activo ? 'bg-gray-600 hover:bg-gray-700' : 'bg-green-600 hover:bg-green-700' }} border border-transparent rounded-md active:bg-gray-800 focus:outline-none focus:border-gray-800 focus:ring focus:ring-gray-200 disabled:opacity-25">
                                                    {{ $user->activo ? 'Inactivar' : 'Activar' }}
                                                </button>
                                            </form>                                            
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $users->links() }} <!-- Paginación -->
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<script>
    function confirmToggle(action, userName) {
        return confirm(`¿Estás seguro de que deseas ${action} al usuario ${userName}?`);
    }
</script>
