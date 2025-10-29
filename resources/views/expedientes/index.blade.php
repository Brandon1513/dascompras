{{-- resources/views/expedientes/index.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold text-gray-800">Expedientes</h2>
  </x-slot>

  <div class="py-8" x-data="{ 
      openModal:false, 
      formAction:'', 
      carpeta:'', 
      nota:''
    }">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="p-6 bg-white shadow sm:rounded-lg">

        {{-- Flash --}}
        @if(session('status'))
          <div class="p-3 mb-4 text-sm text-green-800 bg-green-100 rounded">
            {{ session('status') }}
          </div>
        @endif
        @if(session('success'))
          <div class="p-3 mb-4 text-sm text-green-800 bg-green-100 rounded">
            {{ session('success') }}
          </div>
        @endif

        {{-- Encabezado con buscador + botón Cargar --}}
        <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
          {{-- Filtros --}}
          <form method="GET" class="flex flex-1 gap-3">
          <input
            name="q"
            value="{{ $q }}"
            placeholder="Buscar por carpeta..."
            class="w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500"
          >

          {{-- Estado general --}}
          <select name="estado" class="border-gray-300 rounded-md">
            <option value="">Estado</option>
            <option value="incompleto" @selected($estado==='incompleto')>Incompleto</option>
            <option value="completo"   @selected($estado==='completo')>Completo</option>
          </select>

          {{-- Completado manual --}}
          <select name="manual" class="border-gray-300 rounded-md">
            <option value="">Estado Manual</option>
            <option value="1" @selected(($manual ?? '')==='1')>Solo manual</option>
            <option value="0" @selected(($manual ?? '')==='0')>Sin manual</option>
          </select>

          <button class="px-4 py-2 text-sm text-white bg-gray-900 rounded-md">Filtrar</button>
        </form>


          {{-- Botón Cargar expediente (solo administrador | compras) --}}
          @hasanyrole('administrador|compras')
            <a href="{{ route('expedientes.carga.create') }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
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
                <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Recibos</th>
                <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Estado</th>
                <th class="px-4 py-3 text-xs font-medium text-left text-gray-500 uppercase">Acciones</th>
              </tr>
            </thead>
            <tbody>
            @foreach($expedientes as $e)
              <tr class="border-t">
                <td class="px-4 py-3">
                  <div class="font-medium text-gray-800">{{ $e->nombre_carpeta }}</div>
                  @if($e->folder_link)
                    <a href="{{ $e->folder_link }}" target="_blank" class="text-sm text-indigo-600 underline">Abrir en SharePoint</a>
                  @endif
                </td>

                <td class="px-4 py-3">{{ $e->progreso }}</td>

                <td class="px-4 py-3">
                  <span class="px-2 py-1 rounded text-xs {{ $e->has_requi ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ $e->has_requi ? 'Sí' : 'No' }}
                  </span>
                </td>

                <td class="px-4 py-3">
                  <span class="px-2 py-1 rounded text-xs {{ $e->has_factura ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ $e->has_factura ? 'Sí' : 'No' }}
                  </span>
                </td>

                <td class="px-4 py-3">{{ $e->otros_count }}</td>

                <td class="px-4 py-3">
                  @if($e->completado_manual)
                    <span class="px-2 py-1 text-xs font-medium text-indigo-800 bg-indigo-100 rounded">
                      Completo (manual)
                    </span>
                  @else
                    <span class="px-2 py-1 rounded text-xs {{ $e->estado==='completo' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                      {{ ucfirst($e->estado) }}
                    </span>
                  @endif
                </td>

                <td class="px-4 py-3 text-sm">
                <a href="{{ route('expedientes.show',$e) }}" class="text-indigo-600 underline">Ver detalles</a>
                <span class="mx-1 text-gray-300">|</span>

                @php
                  $isAuto   = $e->estado === 'completo';
                  $isManual = (bool) $e->completado_manual;
                @endphp

                @if($isAuto || $isManual)
                  {{-- Ya está completo (auto o manual) => solo Editar --}}
                  <a href="{{ route('expedientes.edit',$e) }}" class="text-indigo-600 underline">Editar archivos</a>
                @else
                  {{-- Aún incompleto => Completar --}}
                  <a href="{{ route('expedientes.edit',$e) }}" class="text-indigo-600 underline">Completar</a>
                @endif

                {{-- Completar manual (con modal de nota) --}}
                @can('marcarCompletado', $e)
                  @if(!$isManual && !$isAuto)
                    <span class="mx-1 text-gray-300">|</span>
                    <button
                      type="button"
                      class="text-indigo-700 hover:underline"
                      @click="
                        openModal = true;
                        formAction = '{{ route('expedientes.complete-manual', $e) }}';
                        carpeta = '{{ addslashes($e->nombre_carpeta) }}';
                        nota = '';
                      "
                    >
                      Completar manual
                    </button>
                  @endif
                @endcan

                {{-- Quitar completado manual --}}
                @can('desmarcarCompletado', $e)
                  @if($isManual)
                    <span class="mx-1 text-gray-300">|</span>
                    <form action="{{ route('expedientes.undo-complete-manual', $e) }}" method="post" class="inline"
                          onsubmit="return confirm('¿Quitar el completado manual de esta carpeta?');">
                      @csrf @method('DELETE')
                      <button class="text-rose-700 hover:underline">Quitar completado</button>
                    </form>
                  @endif
                @endcan
              </td>

              </tr>
            @endforeach
            </tbody>
          </table>
        </div>

        <div class="mt-4">
          {{ $expedientes->links() }}
        </div>
      </div>
    </div>

    {{-- Modal Completar Manual --}}
    <div 
      x-cloak
      x-show="openModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
      @keydown.escape.window="openModal=false"
    >
      <div class="w-full max-w-lg p-6 bg-white rounded-lg shadow-lg">
        <h3 class="text-lg font-semibold">Completar manual</h3>
        <p class="mt-1 text-sm text-gray-600">
          Vas a marcar el expediente <span class="font-medium" x-text="carpeta"></span> como <strong>Completo (manual)</strong>.
        </p>

        <form :action="formAction" method="POST" class="mt-4 space-y-3">
          @csrf
          <label class="block text-sm font-medium text-gray-700">Nota (opcional)</label>
          <textarea 
            name="nota" 
            x-model="nota"
            rows="4" 
            class="w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="Agrega una nota para justificar el completado manual (opcional)"></textarea>

          <div class="flex justify-end gap-3 pt-2">
            <button type="button" class="px-4 py-2 text-gray-700 bg-gray-100 rounded"
                    @click="openModal=false">
              Cancelar
            </button>
            <button type="submit" class="px-4 py-2 text-white bg-indigo-600 rounded hover:bg-indigo-700">
              Marcar como completo
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</x-app-layout>
