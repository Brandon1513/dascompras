{{-- resources/views/catalogos/retenciones/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('catalogos.retenciones.index') }}" class="text-gray-400 transition hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editar Retención: <span class="text-indigo-600">{{ $retencion->nombre }}</span>
            </h2>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-xl px-4 mx-auto sm:px-6 lg:px-8">
            <div class="p-6 bg-white border border-gray-200 shadow-sm rounded-xl">
                @include('catalogos.retenciones._form', [
                    'action'    => route('catalogos.retenciones.update', $retencion),
                    'method'    => 'PUT',
                    'retencion' => $retencion,
                ])
            </div>
        </div>
    </div>
</x-app-layout>