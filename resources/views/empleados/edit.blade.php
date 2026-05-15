<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('empleados.index') }}"
               class="flex items-center justify-center w-8 h-8 text-gray-500 transition bg-gray-100 rounded-lg hover:bg-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h2 class="text-lg font-bold text-gray-900">Editar usuario</h2>
                <p class="text-xs text-gray-400 mt-0.5">{{ $user->name }}</p>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen py-6" style="background: linear-gradient(160deg, #f8f5ff 0%, #f1f5f9 50%, #f8f5ff 100%);">
        <div class="max-w-2xl px-4 mx-auto space-y-4 sm:px-6 lg:px-8">

            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
                     style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
                    <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    <h3 class="text-xs font-bold tracking-widest text-white uppercase">Datos del usuario</h3>
                </div>
                <div class="p-6">
                    @if (session('error'))
                        <div class="flex items-center gap-2 p-3 mb-5 text-sm font-medium text-red-800 border border-red-200 rounded-xl bg-red-50">
                            <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ session('error') }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('empleados.update', $user->id) }}" class="space-y-5">
                        @csrf @method('PUT')

                        @include('empleados._form')

                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <a href="{{ route('empleados.resend', $user->id) }}"
                               class="inline-flex items-center gap-1.5 text-xs text-indigo-600 hover:text-indigo-800 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                Reenviar correo de bienvenida
                            </a>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('empleados.index') }}"
                                   class="text-sm text-gray-500 transition hover:text-gray-700">
                                    Cancelar
                                </a>
                                <button type="submit"
                                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-white rounded-xl shadow-md hover:shadow-lg transition-all"
                                        style="background: linear-gradient(135deg, #4A1660, #7c3aed);">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Guardar cambios
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>