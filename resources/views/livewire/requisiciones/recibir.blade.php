{{-- resources/views/livewire/requisiciones/recibir.blade.php --}}

<div class="max-w-5xl mx-auto space-y-6">
    {{-- Título --}}
    <div>
        <h1 class="text-2xl font-semibold">
            Registrar recepción — {{ $requisicion->folio }}
        </h1>
    </div>

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
    </div>

    {{-- Partidas solicitadas --}}
<div class="p-4 bg-white border rounded">
    <h3 class="mb-3 text-sm font-medium text-gray-700">Partidas de la requisición</h3>

    <div class="overflow-x-auto">
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
                                <a href="{{ $it->link_compra }}"
                                   class="ml-2 text-indigo-600 hover:underline"
                                   target="_blank" rel="noopener">link</a>
                            @endif
                        </td>

                        <td class="py-2">{{ $it->unidad }}</td>

                        {{-- NUEVO: proveedor sugerido --}}
                        <td class="py-2">
                            {{ $it->proveedor_sugerido ?: '—' }}
                        </td>

                        {{-- NUEVO: ficha técnica con ícono --}}
                        <td class="py-2">
                            @if($it->ficha_tecnica_path)
                                <a class="inline-flex items-center gap-2 text-indigo-600 hover:underline"
                                   href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($it->ficha_tecnica_path) }}"
                                   target="_blank" rel="noopener">

                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <path d="M14 2v6h6"/>
                                        <path d="M16 13H8"/>
                                        <path d="M16 17H8"/>
                                        <path d="M10 9H8"/>
                                    </svg>

                                    <span class="text-xs">
                                        {{ $it->ficha_tecnica_nombre ?: 'Ficha técnica' }}
                                    </span>
                                </a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>

                        <td class="py-2 text-right">${{ number_format($it->precio_unitario,2) }}</td>
                        <td class="py-2 text-right">${{ number_format($it->subtotal,2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-gray-500">Sin partidas</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Totales --}}
    <div class="mt-4 ml-auto text-right max-w-64">
        <div class="flex justify-between text-sm">
            <span>Subtotal</span>
            <strong>${{ number_format($requisicion->subtotal,2) }}</strong>
        </div>
        <div class="flex justify-between text-sm">
            <span>IVA (16%)</span>
            <strong>${{ number_format($requisicion->iva,2) }}</strong>
        </div>
        <div class="flex justify-between text-lg">
            <span>Total</span>
            <strong>${{ number_format($requisicion->total,2) }}</strong>
        </div>
    </div>
</div>


    {{-- Formulario de recepción --}}
    <div class="p-4 bg-white border rounded">
        @if (session('status'))
            <div class="p-3 mb-4 text-green-800 bg-green-100 rounded">{{ session('status') }}</div>
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium">Fecha de recibido</label>
                <input type="date"
                       wire:model.defer="fecha_recibido"
                       class="w-full mt-1 border-gray-300 rounded">
                @error('fecha_recibido') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium">Área que recibe</label>
                <select wire:model.defer="area_recibe" class="w-full mt-1 border-gray-300 rounded">
                    @if(!empty($area_recibe))
                        <option value="{{ $area_recibe }}">{{ $area_recibe }}</option>
                    @else
                        <option value="">— Selecciona —</option>
                    @endif

                    @foreach($departamentos as $d)
                        <option value="{{ $d['nombre'] }}">{{ $d['nombre'] }}</option>
                    @endforeach
                </select>
                @error('area_recibe') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium">Nombre de quien recibe</label>
                <input type="text"
                       wire:model.defer="recibe_nombre"
                       class="w-full mt-1 border-gray-300 rounded"
                       placeholder="Ej. Juan Pérez (Compras)">
                @error('recibe_nombre') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Firma --}}
        <div class="mt-6">
            <label class="block text-sm font-medium">Firma de conformidad de recepción</label>

            <div class="p-3 mt-2 border rounded bg-gray-50">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <p class="text-sm text-gray-600">Firma (obligatoria)</p>

                    <div class="flex items-center gap-2">
                        <button type="button" id="clearFirmaRecibir"
                                class="px-3 py-1 text-sm bg-white border rounded hover:bg-gray-100">
                            Limpiar firma
                        </button>
                    </div>
                </div>

                {{-- Modo de firma --}}
                <div class="mt-3 flex flex-wrap items-center gap-4">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="radio" name="modoFirmaRecibir" value="dibujar" checked>
                        <span>Firmar dibujando</span>
                    </label>

                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="radio" name="modoFirmaRecibir" value="escribir">
                        <span>Firmar escribiendo</span>
                    </label>
                </div>

                {{-- Box escribir nombre --}}
                <div id="boxFirmaTexto" class="hidden mt-3 p-3 bg-white border rounded">
                    <label class="block text-sm font-medium text-gray-700">Escribe tu nombre</label>
                    <div class="mt-2 flex flex-col gap-2 md:flex-row md:items-center">
                        <input id="firmaTextoRecibir" type="text"
                               class="w-full border-gray-300 rounded"
                               placeholder="Ej. Juan Pérez">
                        <button type="button" id="btnGenerarFirmaTexto"
                                class="px-4 py-2 text-white bg-indigo-600 rounded hover:bg-indigo-700">
                            Generar firma
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">
                        Tip: al generar, tu nombre se dibuja en el recuadro como firma.
                    </p>
                </div>

                {{-- Canvas --}}
                <div class="mt-3 bg-white border rounded">
                    <canvas id="canvasFirmaRecibir" height="180" class="w-full"></canvas>
                </div>

                {{-- Hidden --}}
                <input type="hidden" wire:model.defer="firma_base64" id="firmaBase64Recibir">

                @error('firma_base64') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

                <p class="mt-2 text-xs text-gray-500">
                    Tip: si estás en PC usa el mouse, en celular puedes firmar con el dedo.
                </p>
            </div>
        </div>

        <div class="mt-6">
            <button
                type="button"
                id="btnGuardarRecepcion"
                wire:loading.attr="disabled"
                class="px-4 py-2 text-white bg-indigo-600 rounded hover:bg-indigo-700"
            >
                <span wire:loading.remove wire:target="save">Guardar recepción</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </button>
        </div>
    </div>
</div>

@push('styles')
    {{-- Fuente “autógrafa” para firma por texto --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', initFirmaRecibir);
document.addEventListener('livewire:navigated', initFirmaRecibir);

function initFirmaRecibir() {
  const canvas = document.getElementById('canvasFirmaRecibir');
  if (!canvas) return;

  // Evita doble inicialización
  if (canvas.dataset.inited === '1') return;
  canvas.dataset.inited = '1';

  const ctx = canvas.getContext('2d');

  const clearBtn = document.getElementById('clearFirmaRecibir');
  const hidden = document.getElementById('firmaBase64Recibir');

  const radios = document.querySelectorAll('input[name="modoFirmaRecibir"]');
  const boxTexto = document.getElementById('boxFirmaTexto');
  const inputTexto = document.getElementById('firmaTextoRecibir');
  const btnGenerarTexto = document.getElementById('btnGenerarFirmaTexto');
  const btnGuardar = document.getElementById('btnGuardarRecepcion');

  let drawing = false;
  let hasInk = false;

  function setupStroke() {
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.strokeStyle = '#111827';
  }

  function resizeCanvas() {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    const rect = canvas.getBoundingClientRect();

    const prev = hasInk ? canvas.toDataURL('image/png') : null;

    canvas.width = rect.width * ratio;
    canvas.height = 180 * ratio;
    canvas.style.height = '180px';

    ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
    setupStroke();

    if (prev) {
      const img = new Image();
      img.onload = () => {
        ctx.clearRect(0, 0, rect.width, 180);
        ctx.drawImage(img, 0, 0, rect.width, 180);
      };
      img.src = prev;
    }
  }

  setTimeout(resizeCanvas, 50);
  window.addEventListener('resize', resizeCanvas);

  function getModo() {
    const r = document.querySelector('input[name="modoFirmaRecibir"]:checked');
    return r ? r.value : 'dibujar';
  }

  function getPos(e) {
    const rect = canvas.getBoundingClientRect();
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
    return { x: clientX - rect.left, y: clientY - rect.top };
  }

  function start(e) {
    if (getModo() !== 'dibujar') return;
    e.preventDefault();
    drawing = true;
    hasInk = true;
    const p = getPos(e);
    ctx.beginPath();
    ctx.moveTo(p.x, p.y);
  }

  function move(e) {
    if (!drawing || getModo() !== 'dibujar') return;
    e.preventDefault();
    const p = getPos(e);
    ctx.lineTo(p.x, p.y);
    ctx.stroke();
  }

  function end(e) {
    if (!drawing) return;
    e.preventDefault();
    drawing = false;
  }

  canvas.addEventListener('mousedown', start);
  canvas.addEventListener('mousemove', move);
  window.addEventListener('mouseup', end);

  canvas.addEventListener('touchstart', start, { passive: false });
  canvas.addEventListener('touchmove', move, { passive: false });
  window.addEventListener('touchend', end, { passive: false });

  function clearCanvas() {
    const rect = canvas.getBoundingClientRect();
    ctx.clearRect(0, 0, rect.width, 180);
    hasInk = false;
    hidden.value = '';
    @this.set('firma_base64', null);
  }

  clearBtn?.addEventListener('click', clearCanvas);

  radios.forEach(r => {
    r.addEventListener('change', () => {
      const modo = getModo();
      boxTexto.classList.toggle('hidden', modo !== 'escribir');
    });
  });

  btnGenerarTexto?.addEventListener('click', () => {
    const nombre = (inputTexto?.value || '').trim();
    if (!nombre) {
      alert('Escribe tu nombre para generar la firma.');
      return;
    }

    const rect = canvas.getBoundingClientRect();
    ctx.clearRect(0, 0, rect.width, 180);

    ctx.fillStyle = '#111827';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';

    const maxWidth = rect.width - 30;
    let fontSize = 56;
    ctx.font = `${fontSize}px "Great Vibes", cursive`;

    while (ctx.measureText(nombre).width > maxWidth && fontSize > 28) {
      fontSize -= 2;
      ctx.font = `${fontSize}px "Great Vibes", cursive`;
    }

    ctx.fillText(nombre, rect.width / 2, 90);
    hasInk = true;
  });

  function captureBase64() {
    const dataUrl = canvas.toDataURL('image/png');
    hidden.value = dataUrl;
    return dataUrl;
  }

  btnGuardar?.addEventListener('click', async () => {
    if (!hasInk) {
      alert('Por favor firma antes de guardar la recepción.');
      return;
    }

    const dataUrl = captureBase64();
    await @this.set('firma_base64', dataUrl);
    await @this.call('save');
  });
}
</script>
@endpush
