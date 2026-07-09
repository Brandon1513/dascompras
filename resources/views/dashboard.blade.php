<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">
                    Buenos días, {{ explode(' ', Auth::user()->name)[0] }} 👋
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">{{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
            </div>
            <a href="{{ route('requisiciones.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white transition-all shadow-md rounded-xl hover:shadow-lg"
               style="background: linear-gradient(135deg, #4A1660, #7c3aed);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva requisición
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen py-6" style="background: linear-gradient(160deg, #f8f5ff 0%, #f1f5f9 50%, #f8f5ff 100%);">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <livewire:dashboard />
        </div>
    </div>
</x-app-layout>