{{-- resources/views/requisiciones/show.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight">Requisición {{ $requisicion->folio }}</h2>
  </x-slot>

  <div class="py-6">
    <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-6">

      {{-- Resumen --}}
      <div class="p-4 bg-white border rounded">
        <dl class="grid grid-cols-1 gap-4 text-sm md:grid-cols-4">
          <div>
            <dt class="text-gray-500">Fecha</dt>
            <dd class="font-medium">{{ optional($requisicion->fecha_emision)->format('Y-m-d') }}</dd>
          </div>
          <div>
            <dt class="text-gray-500">Solicitante</dt>
            <dd class="font-medium">{{ $requisicion->solicitante?->name }}</dd>
          </div>
          <div>
            <dt class="text-gray-500">Departamento</dt>
            <dd class="font-medium">{{ $requisicion->departamentoRef?->nombre ?? '—' }}</dd>
          </div>
          <div>
            <dt class="text-gray-500">Centro de costos</dt>
            <dd class="font-medium">{{ $requisicion->centroCostoRef?->nombre ?? '—' }}</dd>
          </div>
        </dl>

        <div class="mt-4">
          <span class="inline-flex items-center px-2 py-0.5 text-xs border rounded">
            {{ str_replace('_',' ', $requisicion->estado) }}
          </span>
        </div>
      </div>

      {{-- Justificación --}}
      @if($requisicion->justificacion)
        <div class="p-4 bg-white border rounded">
          <h3 class="mb-2 text-sm font-medium text-gray-600">Justificación</h3>
          <p class="text-sm whitespace-pre-line">{{ $requisicion->justificacion }}</p>
        </div>
      @endif

      {{-- Partidas --}}
      <div class="p-4 bg-white border rounded">
        <table class="w-full text-sm">
          <thead class="text-left text-gray-600">
            <tr>
              <th class="py-2">Cant.</th>
              <th class="py-2">Descripción</th>
              <th class="py-2">Unidad</th>

              {{-- NUEVO --}}
              <th class="py-2">Proveedor</th>
              <th class="py-2">Ficha técnica</th>

              <th class="py-2 text-right">P. unit</th>
              <th class="py-2 text-right">Subtotal</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            @forelse($requisicion->items as $it)
              <tr>
                <td class="py-2">
                  {{ rtrim(rtrim(number_format($it->cantidad,3,'.',''), '0'), '.') }}
                </td>

                <td class="py-2">
                  {{ $it->descripcion }}
                  @if($it->link_compra)
                    <a href="{{ $it->link_compra }}" target="_blank" rel="noopener" class="ml-2 text-indigo-600">link</a>
                  @endif
                </td>

                <td class="py-2">{{ $it->unidad }}</td>

                {{-- NUEVO: Proveedor --}}
                <td class="py-2">
                  {{ $it->proveedor_sugerido ?: '—' }}
                </td>

                {{-- NUEVO: Ficha técnica --}}
                <td class="py-2">
                  @if($it->ficha_tecnica_path)
                    <a class="text-indigo-600 hover:underline"
                       href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($it->ficha_tecnica_path) }}"
                       target="_blank" rel="noopener">
                      {{ $it->ficha_tecnica_nombre ?: 'Ver archivo' }}
                    </a>
                  @else
                    —
                  @endif
                </td>

                <td class="py-2 text-right">${{ number_format($it->precio_unitario,2) }}</td>
                <td class="py-2 text-right">${{ number_format($it->subtotal,2) }}</td>
              </tr>
            @empty
              <tr><td colspan="7" class="py-6 text-center text-gray-500">Sin partidas</td></tr>
            @endforelse
          </tbody>
        </table>

        <div class="mt-4 ml-auto text-right max-w-64">
          <div class="flex justify-between text-sm">
            <span>Subtotal</span><strong>${{ number_format($requisicion->subtotal,2) }}</strong>
          </div>
          <div class="flex justify-between text-sm">
            <span>IVA (16%)</span><strong>${{ number_format($requisicion->iva,2) }}</strong>
          </div>
          <div class="flex justify-between text-lg">
            <span>Total</span><strong>${{ number_format($requisicion->total,2) }}</strong>
          </div>
        </div>
      </div>

      {{-- Cadena de aprobaciones --}}
      <div class="p-4 bg-white border rounded">
        <h3 class="mb-2 text-sm font-medium text-gray-700">Aprobaciones</h3>
        <ul class="space-y-2 text-sm">
          @forelse($requisicion->aprobaciones->sortBy('created_at') as $ap)
            <li class="flex items-center justify-between">
              <div>
                <span class="font-medium">{{ $ap->nivel->nombre ?? '—' }}</span>
                <span class="text-gray-500"> · </span>
                <span>{{ $ap->aprobador?->name ?? 'Sin asignar' }}</span>
                @if($ap->firmado_en)
                  <span class="text-gray-500"> · {{ optional($ap->firmado_en)->format('Y-m-d H:i') }}</span>
                @endif
              </div>
              <span class="px-2 py-0.5 text-xs rounded
                @class([
                  'bg-amber-100 text-amber-800'    => $ap->estado==='pendiente',
                  'bg-emerald-100 text-emerald-800'=> $ap->estado==='aprobada',
                  'bg-rose-100 text-rose-800'      => $ap->estado==='rechazada',
                ])">
                {{ $ap->estado }}
              </span>
            </li>
          @empty
            <li class="text-gray-500">Sin registros.</li>
          @endforelse
        </ul>
      </div>

      {{-- Acciones (aprobación / recepción) --}}
      @if($puedeFirmar && $requisicion->estado === 'en_aprobacion')
        <livewire:requisiciones.aprobar :requisicion="$requisicion" />
      @endif

      @can('receive', $requisicion)
        @if(in_array($requisicion->estado, ['aprobada_final']) )
          <div class="flex justify-end">
            <a href="{{ route('requisiciones.recibir', $requisicion) }}"
               class="px-4 py-2 text-white bg-indigo-600 rounded hover:bg-indigo-700">
              Registrar recepción
            </a>
          </div>
        @endif
      @endcan

      <div>
        <a href="{{ route('requisiciones.index') }}" class="text-sm text-gray-600 hover:underline">← Volver</a>
      </div>

    </div>
  </div>
</x-app-layout>
