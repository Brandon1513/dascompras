<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">
      {{ $expediente->estado === 'completo' ? 'Editar archivos' : 'Adjuntar archivos' }}
      — {{ $expediente->nombre_carpeta }}
    </h2>
  </x-slot>

  <div class="py-8">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
      <div class="p-6 bg-white shadow sm:rounded-lg">
        @if ($errors->any())
          <div class="p-3 mb-4 text-red-700 rounded bg-red-50">
            <ul class="text-sm list-disc list-inside">
              @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
          </div>
        @endif

        <form method="POST"
              action="{{ route('expedientes.attach',$expediente) }}"
              enctype="multipart/form-data"
              class="space-y-6">
          @csrf

          <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            {{-- REQUISICIÓN (múltiples) --}}
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700">Requisición (múltiples)</label>
              <input type="file"
                     name="requi[]"
                     multiple
                     accept=".jpg,.jpeg,.png,.pdf"
                     class="w-full border-gray-300 rounded-md" />
              <p class="mt-1 text-xs text-gray-500">Actual: {{ $expediente->has_requi ? 'Sí' : 'No' }}</p>
            </div>

            {{-- FACTURA (múltiples) --}}
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700">Factura (múltiples)</label>
              <input type="file"
                     name="factura[]"
                     multiple
                     accept=".jpg,.jpeg,.png,.pdf"
                     class="w-full border-gray-300 rounded-md" />
              <p class="mt-1 text-xs text-gray-500">Actual: {{ $expediente->has_factura ? 'Sí' : 'No' }}</p>
            </div>

            {{-- RECIBOS (múltiples) --}}
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700">Recibos (múltiples)</label>
              <input type="file"
                     name="otros[]"
                     multiple
                     accept=".jpg,.jpeg,.png,.pdf"
                     class="w-full border-gray-300 rounded-md" />
              <p class="mt-1 text-xs text-gray-500">Actual: {{ $expediente->otros_count }}</p>
            </div>
          </div>

          <div class="flex justify-end">
            <a href="{{ route('expedientes.show',$expediente) }}" class="px-4 py-2 mr-2 text-sm border rounded-md">
              Cancelar
            </a>
            <button class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
              {{ $expediente->estado === 'completo' ? 'Guardar cambios' : 'Adjuntar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</x-app-layout>
