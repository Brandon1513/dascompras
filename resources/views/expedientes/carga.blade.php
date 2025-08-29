<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">Carga de expediente</h2>
  </x-slot>

  <div class="py-12">
    <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
      <div class="p-6 bg-white shadow sm:rounded-lg">
        {{-- Mensajes --}}
        @if(session('success'))
          <div class="mb-4 p-3 rounded bg-green-50 text-green-800">
            {{ session('success') }}
            @if(session('folder_name'))
              <div class="text-sm text-gray-700">Carpeta: <span class="font-medium">{{ session('folder_name') }}</span></div>
            @endif
            @if(session('link'))
              <div class="mt-2">
                <a href="{{ session('link') }}" target="_blank" class="text-indigo-600 underline">
                  Abrir carpeta en SharePoint
                </a>
              </div>
            @endif
          </div>
        @endif

        @if ($errors->any())
          <div class="mb-4 p-3 rounded bg-red-50 text-red-700">
            <ul class="list-disc list-inside text-sm">
              @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
          </div>
        @endif

        {{-- Resumen de subidos --}}
        @if(session('subidos'))
          <div class="mb-6">
            <h3 class="font-semibold mb-2">Archivos subidos:</h3>
            <ul class="list-disc list-inside text-sm text-gray-700">
              @foreach(session('subidos') as $s)
                <li>
                  <span class="font-medium">{{ $s['tipo'] }}:</span> {{ $s['nombre'] }}
                  @if($s['url']) — <a href="{{ $s['url'] }}" target="_blank" class="text-indigo-600 underline">Ver</a>@endif
                </li>
              @endforeach
            </ul>
          </div>
        @endif

        {{-- Formulario --}}
        <form method="POST" action="{{ route('expedientes.carga.store') }}" enctype="multipart/form-data" class="space-y-6">
          @csrf

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la carpeta</label>
            <input name="carpeta" value="{{ old('carpeta') }}" required
                   class="w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500"/>
            <p class="text-xs text-gray-500 mt-1">Ej: EXP-2025-0003 - Proveedor XYZ</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Requisición (foto/pdf)</label>
            <input type="file" name="requi" accept=".jpg,.jpeg,.png,.pdf"
                   class="w-full border-gray-300 rounded-md"/>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Factura (foto/pdf)</label>
            <input type="file" name="factura" accept=".jpg,.jpeg,.png,.pdf"
                   class="w-full border-gray-300 rounded-md"/>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Otros (múltiples opcional)</label>
            <input type="file" name="otros[]" multiple accept=".jpg,.jpeg,.png,.pdf"
                   class="w-full border-gray-300 rounded-md"/>
          </div>

          <div class="flex justify-end">
            <button class="px-4 py-2 text-sm font-semibold text-white bg-gray-900 rounded-md">
              Subir a SharePoint
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</x-app-layout>
