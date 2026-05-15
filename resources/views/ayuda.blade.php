<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Centro de ayuda</h2>
            <p class="text-xs text-gray-400 mt-0.5">Manual de usuario del módulo de compras</p>
        </div>
    </x-slot>

    <div class="min-h-screen py-6" style="background: linear-gradient(160deg, #f8f5ff 0%, #f1f5f9 50%, #f8f5ff 100%);">
        <div class="max-w-5xl px-4 mx-auto space-y-4 sm:px-6 lg:px-8">

            {{-- Header con botón descarga --}}
            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center justify-between px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl"
                             style="background: linear-gradient(135deg, #4A1660, #7c3aed);">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Manual de Usuario — Módulo de Compras</p>
                            <p class="text-xs text-gray-400 mt-0.5">Dasavena · Versión 1.0 · Mayo 2026</p>
                        </div>
                    </div>
                    <a href="{{ asset('storage/manual/manual_usuario_dasavena.pdf') }}"
                       download="Manual_Usuario_Compras_Dasavena.pdf"
                       class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white rounded-xl shadow-md hover:shadow-lg transition-all"
                       style="background: linear-gradient(135deg, #4A1660, #7c3aed);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Descargar PDF
                    </a>
                </div>
            </div>

            {{-- Visor PDF --}}
            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
                     style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
                    <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="text-xs font-bold tracking-widest text-white uppercase">Vista previa del manual</h3>
                </div>
                <div class="p-0">
                    <iframe
                        src="{{ asset('storage/manual/manual_usuario_dasavena.pdf') }}#toolbar=1&navpanes=1&scrollbar=1"
                        class="w-full"
                        style="height: 78vh; border: none;"
                        title="Manual de Usuario Módulo de Compras Dasavena">
                    </iframe>
                </div>
            </div>

            {{-- Fallback por si el navegador no soporta iframe PDF --}}
            <div class="p-4 text-xs text-center text-gray-400 bg-white border border-gray-100 shadow-sm rounded-xl">
                Si el manual no se muestra correctamente en tu navegador,
                <a href="{{ asset('storage/manual/manual_usuario_dasavena.pdf') }}"
                   target="_blank"
                   class="font-semibold text-indigo-600 hover:underline">
                    ábrelo en una nueva pestaña
                </a>
                o descárgalo con el botón de arriba.
            </div>

        </div>
    </div>
</x-app-layout>