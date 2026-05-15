{{-- PARTIAL: _form.blade.php — usar en create y edit --}}
{{-- Variables esperadas: $user (edit) o nuevo, $departamentos, $roles, $userRoles, $jefes --}}

<div class="space-y-5">

    {{-- Nombre --}}
    <div>
        <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
            Nombre completo <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name"
               value="{{ old('name', $user->name ?? '') }}"
               placeholder="Ej. Juan Pérez García"
               required
               class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500
                      @error('name') border-red-400 @enderror">
        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Email --}}
    <div>
        <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
            Correo electrónico <span class="text-red-500">*</span>
        </label>
        <input type="email" name="email"
               value="{{ old('email', $user->email ?? '') }}"
               placeholder="correo@dasavena.com"
               required
               class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500
                      @error('email') border-red-400 @enderror">
        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Departamento + Supervisor --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Departamento</label>
            <select name="departamento_id"
                    class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                <option value="">— Selecciona —</option>
                @foreach($departamentos as $d)
                    <option value="{{ $d->id }}"
                        {{ old('departamento_id', $user->departamento_id ?? '') == $d->id ? 'selected' : '' }}>
                        {{ $d->nombre }}
                    </option>
                @endforeach
            </select>
            @error('departamento_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Supervisor (Jefe directo)</label>
            <select name="supervisor_id"
                    class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                <option value="">— Sin supervisor —</option>
                @foreach($jefes as $j)
                    <option value="{{ $j->id }}"
                        {{ old('supervisor_id', $user->supervisor_id ?? '') == $j->id ? 'selected' : '' }}>
                        {{ $j->name }}
                    </option>
                @endforeach
            </select>
            @error('supervisor_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Roles --}}
    <div>
        <label class="block mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Roles</label>
        <div class="flex flex-wrap gap-3 p-4 border border-gray-100 rounded-xl bg-gray-50">
            @foreach($roles as $roleName)
                <label class="inline-flex items-center gap-2 cursor-pointer group">
                    <input type="checkbox" name="roles[]"
                           value="{{ $roleName }}"
                           {{ in_array($roleName, old('roles', $userRoles ?? [])) ? 'checked' : '' }}
                           class="text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                    <span class="text-sm text-gray-700 capitalize transition-colors group-hover:text-purple-700">
                        {{ $roleName }}
                    </span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Contraseña --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div x-data="{ ver: false }">
            <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                Contraseña
                @isset($user) <span class="font-normal text-gray-300 normal-case">(dejar vacío para no cambiar)</span> @endisset
                @empty($user) <span class="text-red-500">*</span> @endempty
            </label>
            <div class="relative">
                <input :type="ver ? 'text' : 'password'"
                       name="password"
                       placeholder="Mínimo 8 caracteres"
                       class="w-full pr-10 text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500
                              @error('password') border-red-400 @enderror">
                <button type="button" @click="ver = !ver"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 transition-colors hover:text-gray-600">
                    <svg x-show="!ver" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="ver" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div x-data="{ ver: false }">
            <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Confirmar contraseña</label>
            <div class="relative">
                <input :type="ver ? 'text' : 'password'"
                       name="password_confirmation"
                       placeholder="Repite la contraseña"
                       class="w-full pr-10 text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                <button type="button" @click="ver = !ver"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 transition-colors hover:text-gray-600">
                    <svg x-show="!ver" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="ver" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

</div>