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
                <h2 class="text-lg font-bold text-gray-900">Agregar usuario</h2>
                <p class="text-xs text-gray-400 mt-0.5">Crea un nuevo usuario en el sistema</p>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen py-6" style="background: linear-gradient(160deg, #f8f5ff 0%, #f1f5f9 50%, #f8f5ff 100%);">
        <div class="max-w-2xl px-4 mx-auto sm:px-6 lg:px-8">

            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
                     style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
                    <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <h3 class="text-xs font-bold tracking-widest text-white uppercase">Datos del usuario</h3>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('empleados.store') }}" class="space-y-5">
                        @csrf

                        @include('empleados._form')

                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
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
                                Crear usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>