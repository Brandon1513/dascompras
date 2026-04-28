<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('catalogos.unidades.index') }}" class="text-gray-400 transition hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Nueva Unidad de Medida
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl px-4 mx-auto sm:px-6 lg:px-8">
            <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl">
                @include('catalogos.unidades._form', [
                    'action' => route('catalogos.unidades.store'),
                    'method' => 'POST',
                ])
            </div>
        </div>
    </div>
</x-app-layout>