<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Mi perfil</h2>
            <p class="text-xs text-gray-400 mt-0.5">Administra tu información personal y contraseña</p>
        </div>
    </x-slot>

    <div class="min-h-screen py-6" style="background: linear-gradient(160deg, #f8f5ff 0%, #f1f5f9 50%, #f8f5ff 100%);">
        <div class="max-w-2xl px-4 mx-auto space-y-5 sm:px-6 lg:px-8">

            {{-- ══ INFORMACIÓN DEL PERFIL ══════════════════════════════ --}}
            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
                     style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
                    <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <h3 class="text-xs font-bold tracking-widest text-white uppercase">Información personal</h3>
                </div>
                <div class="p-5">
                    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>
                    <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
                        @csrf @method('patch')

                        <div>
                            <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                Nombre completo
                            </label>
                            <input type="text" id="name" name="name"
                                   value="{{ old('name', $user->name) }}"
                                   required autofocus autocomplete="name"
                                   class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500
                                          @error('name') border-red-400 @enderror">
                            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                Correo electrónico
                            </label>
                            <input type="email" id="email" name="email"
                                   value="{{ old('email', $user->email) }}"
                                   required autocomplete="username"
                                   class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500
                                          @error('email') border-red-400 @enderror">
                            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                <div class="p-3 mt-2 border rounded-lg bg-amber-50 border-amber-200">
                                    <p class="text-xs text-amber-700">
                                        Tu correo no está verificado.
                                        <button form="send-verification"
                                                class="font-semibold underline hover:text-amber-900">
                                            Reenviar verificación
                                        </button>
                                    </p>
                                    @if (session('status') === 'verification-link-sent')
                                        <p class="mt-1 text-xs font-medium text-emerald-600">
                                            ✅ Enlace de verificación enviado.
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2 text-sm font-bold text-white transition-all rounded-lg shadow-md hover:shadow-lg"
                                    style="background: linear-gradient(135deg, #4A1660, #7c3aed);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                Guardar
                            </button>
                            @if (session('status') === 'profile-updated')
                                <span x-data="{ show: true }" x-show="show" x-transition
                                      x-init="setTimeout(() => show = false, 2500)"
                                      class="text-xs font-medium text-emerald-600">
                                    ✅ Guardado correctamente.
                                </span>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- ══ CAMBIAR CONTRASEÑA ══════════════════════════════════ --}}
            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
                     style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
                    <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <h3 class="text-xs font-bold tracking-widest text-white uppercase">Cambiar contraseña</h3>
                </div>
                <div class="p-5">
                    <form method="post" action="{{ route('password.update') }}" class="space-y-4">
                        @csrf @method('put')

                        <div x-data="{ ver: false }">
                            <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                Contraseña actual
                            </label>
                            <div class="relative">
                                <input :type="ver ? 'text' : 'password'"
                                       id="update_password_current_password"
                                       name="current_password"
                                       autocomplete="current-password"
                                       class="w-full pr-10 text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500
                                              @error('current_password', 'updatePassword') border-red-400 @enderror">
                                <button type="button" @click="ver = !ver"
                                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600">
                                    <svg x-show="!ver" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="ver" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            @error('current_password', 'updatePassword')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div x-data="{ ver: false }">
                                <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    Nueva contraseña
                                </label>
                                <div class="relative">
                                    <input :type="ver ? 'text' : 'password'"
                                           id="update_password_password"
                                           name="password"
                                           autocomplete="new-password"
                                           class="w-full pr-10 text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500
                                                  @error('password', 'updatePassword') border-red-400 @enderror">
                                    <button type="button" @click="ver = !ver"
                                            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600">
                                        <svg x-show="!ver" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="ver" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                                @error('password', 'updatePassword')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div x-data="{ ver: false }">
                                <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    Confirmar contraseña
                                </label>
                                <div class="relative">
                                    <input :type="ver ? 'text' : 'password'"
                                           id="update_password_password_confirmation"
                                           name="password_confirmation"
                                           autocomplete="new-password"
                                           class="w-full pr-10 text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                                    <button type="button" @click="ver = !ver"
                                            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600">
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

                        <div class="flex items-center gap-3 pt-2">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2 text-sm font-bold text-white transition-all rounded-lg shadow-md hover:shadow-lg"
                                    style="background: linear-gradient(135deg, #4A1660, #7c3aed);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                Actualizar contraseña
                            </button>
                            @if (session('status') === 'password-updated')
                                <span x-data="{ show: true }" x-show="show" x-transition
                                      x-init="setTimeout(() => show = false, 2500)"
                                      class="text-xs font-medium text-emerald-600">
                                    ✅ Contraseña actualizada.
                                </span>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- ══ ELIMINAR CUENTA (solo administrador, pero no puede eliminarse a sí mismo) ══ --}}
            @role('administrador')
            <div class="overflow-hidden bg-white border border-red-100 shadow-sm rounded-xl">
                <div class="flex items-center gap-2 px-5 py-3 border-b border-red-100 bg-red-50">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <h3 class="text-xs font-bold tracking-widest text-red-700 uppercase">Zona de peligro</h3>
                </div>
                <div class="p-5">
                    {{-- El administrador logueado no puede eliminarse ni inactivarse --}}
                    @if(session('status') === 'no-delete-admin')
                        <div class="flex items-center gap-2 p-3 mb-4 text-sm font-medium text-red-800 border border-red-200 rounded-xl bg-red-50">
                            <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Como administrador del sistema no puedes eliminar tu propia cuenta por razones de seguridad.
                        </div>
                    @endif

                    <p class="mb-4 text-sm text-gray-600">
                        Una vez eliminada la cuenta, todos los datos serán borrados permanentemente. Esta acción no se puede deshacer.
                    </p>

                    {{-- Botón bloqueado si el admin logueado es este usuario --}}
                    <div class="flex items-center gap-3">
                        <button type="button"
                                disabled
                                class="inline-flex items-center gap-2 px-5 py-2 text-sm font-bold text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed"
                                title="Los administradores no pueden eliminar su propia cuenta">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Acción bloqueada
                        </button>
                        <p class="text-xs text-gray-400">Los administradores no pueden eliminar ni inactivar su propia cuenta. Solicita a otro administrador que lo haga.</p>
                    </div>
                </div>
            </div>
            @endrole

        </div>
    </div>
</x-app-layout>