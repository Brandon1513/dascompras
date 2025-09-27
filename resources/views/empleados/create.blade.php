<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">Agregar Empleado</h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
      <div class="p-6 bg-white shadow sm:rounded-lg">
        <form method="POST" action="{{ route('empleados.store') }}" class="space-y-6">
          @csrf

          {{-- Nombre --}}
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">Nombre completo</label>
            <input name="name" value="{{ old('name') }}" required
                   class="w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500"/>
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Email --}}
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">Correo electrónico</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500"/>
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Departamento --}}
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">Departamento</label>
            <select name="departamento_id"
                    class="w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500">
              <option value="">— Selecciona —</option>
              @foreach($departamentos as $d)
                <option value="{{ $d->id }}" {{ old('departamento_id')==$d->id?'selected':'' }}>
                  {{ $d->nombre }}
                </option>
              @endforeach
            </select>
            @error('departamento_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
          <div class="mt-4">
            <label class="block text-sm font-medium">Supervisor (Jefe)</label>
            <select name="supervisor_id" class="w-full border-gray-300 rounded">
                <option value="">— Sin supervisor —</option>
                @foreach ($jefes as $j)
                    <option value="{{ $j->id }}" @selected(old('supervisor_id') == $j->id)>{{ $j->name }}</option>
                @endforeach
            </select>
            @error('supervisor_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
          </div>


          {{-- Contraseña --}}
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">Contraseña</label>
            <input type="password" name="password" required
                   class="w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500"/>
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" required
                   class="w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500"/>
          </div>

          {{-- Roles --}}
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700">Roles</label>
            <div class="flex flex-wrap gap-4">
              @foreach($roles as $roleName)
                <label class="inline-flex items-center gap-2">
                  <input type="checkbox" name="roles[]"
                         value="{{ $roleName }}"
                         {{ in_array($roleName, old('roles', [])) ? 'checked' : '' }}
                         class="text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                  <span class="text-sm text-gray-700 capitalize">{{ $roleName }}</span>
                </label>
              @endforeach
            </div>
            @error('roles') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            @error('roles.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Botones --}}
          <div class="flex items-center justify-between">
            <a href="{{ route('empleados.index') }}" class="text-sm text-gray-600 hover:underline">Volver</a>
            <button class="px-4 py-2 text-sm font-semibold text-white bg-gray-900 rounded-md">
              Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</x-app-layout>
