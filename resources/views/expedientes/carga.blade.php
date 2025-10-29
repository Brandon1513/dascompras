<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">Carga de expediente</h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
      <div class="p-6 bg-white shadow sm:rounded-lg">
        {{-- Mensajes --}}
        @if(session('success'))
          <div class="p-3 mb-4 text-green-800 rounded bg-green-50">
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
          <div class="p-3 mb-4 text-red-700 rounded bg-red-50">
            <ul class="text-sm list-disc list-inside">
              @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
          </div>
        @endif

        {{-- Resumen de subidos --}}
        @if(session('subidos'))
          <div class="mb-6">
            <h3 class="mb-2 font-semibold">Archivos subidos:</h3>
            <ul class="text-sm text-gray-700 list-disc list-inside">
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
            <label class="block mb-1 text-sm font-medium text-gray-700">Nombre de la carpeta</label>
            <input name="carpeta" value="{{ old('carpeta') }}" required
                   class="w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500"/>
            <p class="mt-1 text-xs text-gray-500">Ej: EXP-2025-0003 - Proveedor XYZ</p>
          </div>

          <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">Requisición ( multiples foto/pdf)</label>
            <input type="file" name="requi[]" multiple accept=".jpg,.jpeg,.png,.pdf"
                  class="w-full border-gray-300 rounded-md"/>
          </div>

          <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">Factura ( multiples foto/pdf)</label>
            <input type="file" name="factura[]" multiple accept=".jpg,.jpeg,.png,.pdf"
                  class="w-full border-gray-300 rounded-md"/>
          </div>

          <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">Recibos (múltiples opcional)</label>
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
