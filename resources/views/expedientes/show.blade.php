<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold text-gray-800">Expediente: {{ $expediente->nombre_carpeta }}</h2>
  </x-slot>

  <div class="py-8">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
      <div class="p-6 bg-white shadow sm:rounded-lg">
        <div class="mb-4">
          <a href="{{ $expediente->folder_link }}" target="_blank" class="text-indigo-600 underline">Abrir en SharePoint</a>
        </div>
        <div class="mb-6 text-sm text-gray-700">
          <div><span class="font-medium">Progreso:</span> {{ $expediente->progreso }}</div>
          <div><span class="font-medium">Estado:</span> {{ ucfirst($expediente->estado) }}</div>
          <div><span class="font-medium">Creado por:</span> {{ $expediente->creador->name }}</div>
          <div><span class="font-medium">Fecha:</span> {{ $expediente->created_at->format('Y-m-d H:i') }}</div>
        </div>

        <h3 class="mb-2 font-semibold">Archivos</h3>
        <ul class="space-y-1 text-sm text-gray-800 list-disc list-inside">
          @foreach($expediente->archivos as $a)
            <li>
              <span class="uppercase text-xs bg-gray-100 px-1.5 py-0.5 rounded">{{ $a->tipo }}</span>
              {{ $a->nombre_original }}
              @if($a->web_url)
                — <a href="{{ $a->web_url }}" target="_blank" class="text-indigo-600 underline">Ver</a>
              @endif
              <span class="text-gray-500">({{ number_format(($a->tamano ?? 0)/1024,0) }} KB)</span>
            </li>
          @endforeach
        </ul>
        @php
          $btnLabel = $expediente->estado === 'completo'
                        ? 'Editar archivos'
                        : 'Adjuntar archivos';
        @endphp

        <div class="mt-4">
          <a href="{{ route('expedientes.edit', $expediente) }}"
            class="inline-flex items-center px-3 py-1.5 text-sm text-white bg-indigo-600 rounded hover:bg-indigo-700">
            {{ $btnLabel }}
          </a>
        </div>
      </div>
      

    </div>
  </div>
</x-app-layout>
