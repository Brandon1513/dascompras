<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('requisiciones.show', $requisicion) }}"
               class="text-gray-400 transition hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    @if($requisicion->estado === 'rechazada_compras')
                        Corregir requisición — {{ $requisicion->folio }}
                    @else
                        Editar requisición — {{ $requisicion->folio }}
                    @endif
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    @if($requisicion->estado === 'rechazada_compras')
                        Corrige los puntos indicados por Compras y reenvía.
                    @else
                        La requisición está en borrador, puedes modificarla antes de enviarla.
                    @endif
                </p>
            </div>
        </div>
    </x-slot>

    {{-- Sin py-6 ni wrapper adicional — el componente Livewire ya tiene su propio layout --}}
    @if($requisicion->estado === 'rechazada_compras' && $requisicion->motivo_rechazo_compras)
        <div class="px-4 pt-6 mx-auto max-w-5xl sm:px-6 lg:px-8">
            <div class="flex items-start gap-3 px-4 py-4 border border-orange-200 rounded-xl bg-orange-50">
                <svg class="w-5 h-5 text-orange-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-orange-800">Motivo del rechazo por Compras:</p>
                    <p class="mt-1 text-sm text-orange-700 whitespace-pre-line">{{ $requisicion->motivo_rechazo_compras }}</p>
                    <p class="mt-2 text-xs text-orange-500">
                        Revisado por {{ $requisicion->revisadoPor?->name ?? '—' }}
                        @if($requisicion->revisado_en)
                            · {{ $requisicion->revisado_en->format('d/m/Y H:i') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @endif

    @livewire('requisiciones.requisicion-form', ['requisicionId' => $requisicion->id])

</x-app-layout>