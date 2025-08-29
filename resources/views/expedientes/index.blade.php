<x-app-layout>
  <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">Expedientes</h2></x-slot>
    
  <div class="py-8">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="p-6 bg-white shadow sm:rounded-lg">

    
        {{-- Encabezado con buscador + botón Cargar --}}
<div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
  {{-- Filtros (tu formulario actual) --}}
  <form method="GET" class="flex flex-1 gap-3">
    <input name="q" value="{{ $q }}" placeholder="Buscar por carpeta..."
           class="w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500">
    <select name="estado" class="border-gray-300 rounded-md">
      <option value="">Estado</option>
      <option value="incompleto" @selected($estado==='incompleto')>Incompleto</option>
      <option value="completo" @selected($estado==='completo')>Completo</option>
    </select>
    <button class="px-4 py-2 text-sm text-white bg-gray-900 rounded-md">Filtrar</button>
  </form>

  {{-- Botón Cargar expediente (visible solo para administrador | compras) --}}
  @hasanyrole('administrador|compras')
    <a href="{{ route('expedientes.carga.create') }}"
       class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
      {{-- ícono subir (SVG simple) --}}
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5-5m0 0l5 5m-5-5v12"/>
      </svg>
      Cargar expediente
    </a>
  @endhasanyrole
</div>


        <div class="overflow-x-auto">
          <table class="min-w-full bg-white border">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Carpeta</th>
                <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Progreso</th>
                <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Requi</th>
                <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Factura</th>
                <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Otros</th>
                <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Estado</th>
                <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Acciones</th>
              </tr>
            </thead>
            <tbody>
            @foreach($expedientes as $e)
              <tr class="border-t">
                <td class="px-4 py-3">
                  <div class="font-medium text-gray-800">{{ $e->nombre_carpeta }}</div>
                  <a href="{{ $e->folder_link }}" target="_blank" class="text-sm text-indigo-600 underline">Abrir en SharePoint</a>
                </td>
                <td class="px-4 py-3">{{ $e->progreso }}</td>
                <td class="px-4 py-3">
                  <span class="px-2 py-1 rounded text-xs {{ $e->has_requi?'bg-green-100 text-green-700':'bg-gray-100 text-gray-600' }}">
                    {{ $e->has_requi ? 'Sí' : 'No' }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <span class="px-2 py-1 rounded text-xs {{ $e->has_factura?'bg-green-100 text-green-700':'bg-gray-100 text-gray-600' }}">
                    {{ $e->has_factura ? 'Sí' : 'No' }}
                  </span>
                </td>
                <td class="px-4 py-3">{{ $e->otros_count }}</td>
                <td class="px-4 py-3">
                  <span class="px-2 py-1 rounded text-xs {{ $e->estado==='completo'?'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700' }}">
                    {{ ucfirst($e->estado) }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <a href="{{ route('expedientes.show',$e) }}" class="text-sm text-indigo-600 underline">Ver detalles</a>
                  <span class="mx-1 text-gray-300">|</span>
                  @if($e->estado !== 'completo')
                    <a href="{{ route('expedientes.edit',$e) }}" class="text-sm text-indigo-600 underline">Completar</a>
                  @else
                    <a href="{{ route('expedientes.edit',$e) }}" class="text-sm text-indigo-600 underline">Editar archivos</a>
                  @endif
                </td>

              </tr>
            @endforeach
            </tbody>
          </table>
        </div>

        <div class="mt-4">{{ $expedientes->links() }}</div>
      </div>
    </div>
  </div>
</x-app-layout>
