{{-- resources/views/requisiciones/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight">Editar requisición (borrador)</h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow sm:rounded-lg">
                @livewire('requisiciones.requisicion-form', ['requisicionId' => $requisicion->id])
            </div>
        </div>
    </div>
</x-app-layout>
