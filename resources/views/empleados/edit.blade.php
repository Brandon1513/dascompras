<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">Editar Empleado</h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
      <div class="p-6 bg-white shadow sm:rounded-lg">
        <form method="POST" action="{{ route('empleados.update', $user->id) }}" class="space-y-6">
          @csrf @method('PUT')

          {{-- Nombre --}}
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">Nombre completo</label>
            <input name="name" value="{{ old('name',$user->name) }}" required
                   class="w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500"/>
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Email --}}
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">Correo electrónico</label>
            <input type="email" name="email" value="{{ old('email',$user->email) }}" required
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
                <option value="{{ $d->id }}" {{ old('departamento_id',$user->departamento_id)==$d->id?'selected':'' }}>
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
                    <option value="{{ $j->id }}" @selected(old('supervisor_id', $user->supervisor_id) == $j->id)>
                        {{ $j->name }}
                    </option>
                @endforeach
            </select>
            @error('supervisor_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
          </div>


          {{-- Roles (checks) --}}
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700">Roles</label>
            <div class="flex flex-wrap gap-4">
              @foreach($roles as $roleName)
                <label class="inline-flex items-center gap-2">
                  <input type="checkbox" name="roles[]"
                         value="{{ $roleName }}"
                         {{ in_array($roleName, old('roles',$userRoles)) ? 'checked' : '' }}
                         class="text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                  <span class="text-sm text-gray-700 capitalize">{{ $roleName }}</span>
                </label>
              @endforeach
            </div>
          </div>

          {{-- Password opcional --}}
          <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">Contraseña (opcional)</label>
            <input type="password" name="password"
                   class="w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500"/>
            <p class="mt-1 text-xs text-gray-500">Si no deseas cambiar la contraseña, deja este campo vacío.</p>
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="block mb-1 text-sm font-medium text-gray-700">Confirmar Contraseña</label>
            <input type="password" name="password_confirmation"
                   class="w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500"/>
          </div>

          {{-- Botones --}}
          <div class="flex items-center justify-between">
            <a href="{{ route('empleados.index') }}" class="text-sm text-gray-600 hover:underline">Volver</a>
            <div class="flex items-center gap-3">
              <a href="{{ route('empleados.resend', $user->id) }}"
                 class="text-sm text-indigo-700 hover:underline">Reenviar correo de bienvenida</a>
              <button class="px-4 py-2 text-sm font-semibold text-white bg-gray-900 rounded-md">
                Guardar cambios
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</x-app-layout>
