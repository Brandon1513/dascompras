<div class="max-w-6xl mx-auto space-y-6">
    {{-- BANNER: quién tiene la aprobación pendiente --}}
    @if($apPendiente)
        <div class="p-3 text-sm rounded bg-amber-50 text-amber-900 border border-amber-200">
            <span class="font-semibold">Pendiente</span>:
            {{ $apPendiente->nivel?->nombre ?? '—' }}
            <span class="text-amber-700"> · </span>
            {{ $apPendiente->aprobador?->name ?? 'Por rol: ' . ($apPendiente->nivel?->rol_aprobador ?? '—') }}
        </div>
    @endif

    {{-- ✅ MENSAJE CHIDO CUANDO YA NO TE TOCA APROBAR --}}
    @if(!$apPendiente)
        <div class="p-3 text-sm rounded border bg-slate-50 text-slate-800 border-slate-200">
            @if($requisicion->estado === 'aprobada_final')
                ✅ Esta requisición ya quedó <b>aprobada final</b>. Ya no requiere tu firma.
            @elseif($requisicion->estado === 'rechazada')
                ⛔ Esta requisición fue <b>rechazada</b>. Ya no requiere tu firma.
            @elseif(!empty($yaFirmoEnEstaReq) && !empty($siguientePendiente))
                📝 Tú ya firmaste tu etapa. Ahora está pendiente con:
                <b>{{ $siguientePendiente->nivel?->nombre ?? '—' }}</b>
                ({{ $siguientePendiente->aprobador?->name ?? 'por rol: '.($siguientePendiente->nivel?->rol_aprobador ?? '—') }}).
            @elseif(!empty($siguientePendiente))
                ⏳ Esta requisición está en espera de:
                <b>{{ $siguientePendiente->nivel?->nombre ?? '—' }}</b>.
            @else
                ℹ️ No hay aprobaciones pendientes en este momento.
            @endif
        </div>
    @endif

    {{-- RESUMEN --}}
    <div class="p-4 bg-white border rounded">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">Requisición {{ $requisicion->folio }}</h2>
            <span class="px-2 py-0.5 text-xs border rounded">
                {{ str_replace('_',' ', $requisicion->estado) }}
            </span>
        </div>

        <dl class="grid grid-cols-1 mt-4 text-sm md:grid-cols-4 gap-x-6 gap-y-2">
            <div>
                <dt class="text-gray-500">Fecha de elaboración</dt>
                <dd>{{ optional($requisicion->fecha_emision)->format('Y-m-d') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Solicitante</dt>
                <dd>{{ $requisicion->solicitante?->name }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Departamento</dt>
                <dd>{{ $requisicion->departamentoRef?->nombre ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Centro de costos</dt>
                <dd>{{ $requisicion->centroCostoRef?->nombre ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Urgencia</dt>
                <dd class="capitalize">{{ $requisicion->urgencia }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Fecha requerida</dt>
                <dd>{{ optional($requisicion->fecha_requerida)->format('Y-m-d') ?: '—' }}</dd>
            </div>
        </dl>
    </div>

    {{-- JUSTIFICACIÓN --}}
    <div class="p-4 bg-white border rounded">
        <h3 class="mb-2 text-sm font-medium text-gray-700">Justificación de la compra</h3>
        <div class="p-3 text-sm bg-gray-50 rounded border">
            {{ $requisicion->justificacion ?: '—' }}
        </div>
    </div>

    {{-- PARTIDAS --}}
<div class="p-4 bg-white border rounded overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="text-gray-600">
            <tr class="text-left">
                <th class="px-3 py-2">Cant.</th>
                <th class="px-3 py-2">Descripción</th>
                <th class="px-3 py-2">Unidad</th>

                {{-- NUEVO --}}
                <th class="px-3 py-2">Proveedor</th>
                <th class="px-3 py-2">Ficha técnica</th>

                <th class="px-3 py-2 text-right">P. Unitario</th>
                <th class="px-3 py-2 text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @foreach($requisicion->items as $it)
                <tr>
                    <td class="px-3 py-2">
                        {{ rtrim(rtrim(number_format($it->cantidad,3,'.',''), '0'), '.') }}
                    </td>

                    <td class="px-3 py-2">
                        {{ $it->descripcion }}

                        @if($it->link_compra)
                            <a href="{{ $it->link_compra }}"
                               class="ml-2 text-indigo-600 hover:underline"
                               target="_blank" rel="noopener">
                                link
                            </a>
                        @endif
                    </td>

                    <td class="px-3 py-2">{{ $it->unidad ?: '—' }}</td>

                    {{-- NUEVO: proveedor --}}
                    <td class="px-3 py-2">
                        {{ $it->proveedor_sugerido ?: '—' }}
                    </td>

                    {{-- NUEVO: ficha técnica con ícono --}}
                    <td class="px-3 py-2">
                        @if($it->ficha_tecnica_path)
                            <a class="inline-flex items-center gap-2 text-indigo-600 hover:underline"
                            href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($it->ficha_tecnica_path) }}"
                            target="_blank" rel="noopener">

                                {{-- Icono documento --}}
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


                    <td class="px-3 py-2 text-right">${{ number_format($it->precio_unitario, 2) }}</td>
                    <td class="px-3 py-2 text-right">${{ number_format($it->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totales --}}
    <div class="grid justify-end mt-4">
        <div class="p-4 text-sm bg-gray-50 border rounded w-full max-w-xs">
            <div class="flex justify-between">
                <span>Subtotal</span>
                <strong>${{ number_format($requisicion->subtotal,2) }}</strong>
            </div>
            <div class="flex justify-between">
                <span>IVA (16%)</span>
                <strong>${{ number_format($requisicion->iva,2) }}</strong>
            </div>
            <div class="flex justify-between text-base">
                <span>Total</span>
                <strong>${{ number_format($requisicion->total,2) }}</strong>
            </div>
        </div>
    </div>
</div>


    {{-- CADENA DE APROBACIONES / HISTORIAL --}}
    <div class="p-4 bg-white border rounded">
        <h3 class="mb-3 text-sm font-medium text-gray-700">Cadena de aprobaciones</h3>
        <ul class="space-y-2 text-sm">
            @foreach($requisicion->aprobaciones->sortBy('created_at') as $ap)
                <li class="flex items-center justify-between">
                    <div>
                        <span class="font-medium">{{ $ap->nivel?->nombre ?? '—' }}</span>
                        <span class="text-gray-500"> · </span>
                        <span>{{ $ap->aprobador?->name ?? 'Por rol: ' . ($ap->nivel?->rol_aprobador ?? '—') }}</span>

                        @if($ap->firmado_en)
                            <span class="text-gray-500"> · </span>
                            <span>{{ $ap->firmado_en->format('Y-m-d H:i') }}</span>
                        @endif
                    </div>
                    <span @class([
                        'px-2 py-0.5 rounded border',
                        'bg-amber-50 text-amber-800 border-amber-200' => $ap->estado==='pendiente',
                        'bg-emerald-50 text-emerald-800 border-emerald-200' => $ap->estado==='aprobada',
                        'bg-rose-50 text-rose-800 border-rose-200' => $ap->estado==='rechazada',
                    ])>
                        {{ $ap->estado }}
                    </span>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- ✅ FIRMA + FORMULARIO SOLO SI TE TOCA APROBAR --}}
    @if($apPendiente)
        {{-- FIRMA --}}
        <div class="mt-4 p-4 bg-white border rounded">
            <label class="block mb-2 text-sm font-medium text-gray-700">Firma (obligatoria para aprobar)</label>

            {{-- Selector de modo --}}
            <div class="flex items-center gap-4 mb-3 text-sm">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="firma_modo" value="draw" checked>
                    <span>Dibujar</span>
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="firma_modo" value="type">
                    <span>Escribir</span>
                </label>
            </div>

            {{-- MODO DIBUJAR --}}
            <div id="sigDrawWrap" class="border rounded p-2 bg-gray-50">
                <canvas id="sigPad" class="w-full" style="height:170px;"></canvas>
            </div>

            {{-- MODO ESCRIBIR --}}
            <div id="sigTypeWrap" class="hidden">
                <div class="grid gap-2">
                    <input
                        id="sigTypeName"
                        type="text"
                        class="w-full border rounded px-3 py-2"
                        placeholder="Escribe tu nombre para generar la firma…"
                        value="{{ auth()->user()->name }}"
                    />

                    <div class="border rounded p-2 bg-gray-50">
                        <canvas id="sigTypeCanvas" class="w-full" style="height:170px;"></canvas>
                    </div>

                    <p class="text-xs text-gray-500">
                        Nota: esto genera una firma tipográfica a partir del nombre escrito.
                    </p>
                </div>
            </div>

            @error('firma_base64')
                <div class="mt-2 text-sm text-rose-600">{{ $message }}</div>
            @enderror

            <div class="mt-2 flex gap-2">
                <button type="button" id="btnClearSig" class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300">
                    Limpiar firma
                </button>
            </div>
        </div>


        {{-- FORMULARIO APROBAR/RECHAZAR --}}
        <div class="p-4 bg-white border rounded">
            @if (session('status'))
                <div class="p-3 mb-3 text-green-800 bg-green-100 border border-green-200 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <label class="block mb-2 text-sm font-medium text-gray-700">Comentarios (opcional)</label>
            <textarea
                class="w-full border rounded focus:ring-0 focus:border-gray-400"
                rows="4"
                wire:model.defer="comentarios"
                placeholder="Escribe comentarios para dejar rastro de la decisión…"
            ></textarea>

            <div class="flex items-center gap-3 mt-4">
                <button
                    type="button"
                    id="btnApprove"
                    wire:loading.attr="disabled"
                    wire:target="approve"
                    class="px-4 py-2 text-white bg-emerald-600 rounded hover:bg-emerald-700"
                >
                    <span wire:loading.remove wire:target="approve">Aprobar</span>
                    <span wire:loading wire:target="approve">Guardando…</span>
                </button>

                <button
                    type="button"
                    wire:click="reject"
                    wire:loading.attr="disabled"
                    wire:target="reject"
                    class="px-4 py-2 text-white bg-rose-600 rounded hover:bg-rose-700"
                >
                    <span wire:loading.remove wire:target="reject">Rechazar</span>
                    <span wire:loading wire:target="reject">Guardando…</span>
                </button>
            </div>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', initSig);
document.addEventListener('livewire:navigated', initSig);

function initSig() {
  const approveBtn = document.getElementById('btnApprove');
  if (!approveBtn) return;

  // Evita doble inicialización
  if (approveBtn.dataset.inited === '1') return;
  approveBtn.dataset.inited = '1';

  // --- elementos ---
  const radios = document.querySelectorAll('input[name="firma_modo"]');
  const drawWrap = document.getElementById('sigDrawWrap');
  const typeWrap = document.getElementById('sigTypeWrap');

  const canvasDraw = document.getElementById('sigPad');
  const canvasType = document.getElementById('sigTypeCanvas');
  const inputName  = document.getElementById('sigTypeName');

  const clearBtn = document.getElementById('btnClearSig');

  // --- helpers resize ---
  function resizeCanvas(canvas) {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * ratio;
    canvas.height = rect.height * ratio;
    const ctx = canvas.getContext("2d");
    ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
  }

  // --- SignaturePad (modo dibujar) ---
  let pad = null;
  if (canvasDraw) {
    resizeCanvas(canvasDraw);
    pad = new SignaturePad(canvasDraw, { backgroundColor: 'rgba(255,255,255,1)' });
  }

  // --- Canvas tipográfico (modo escribir) ---
  function renderTypedSignature() {
    if (!canvasType) return;
    resizeCanvas(canvasType);

    const ctx = canvasType.getContext('2d');
    ctx.clearRect(0, 0, canvasType.width, canvasType.height);

    // fondo blanco para PDF
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvasType.width, canvasType.height);

    const name = (inputName?.value || '').trim();

    // si está vacío, no dibujes nada
    if (!name) return;

    // estilo tipo firma (si quieres una font “script” real, te explico abajo)
    ctx.fillStyle = '#111';
    ctx.textBaseline = 'middle';

    // tamaño adaptable
    const fontSize = 54;
    ctx.font = `italic ${fontSize}px "Segoe Script", "Brush Script MT", "Pacifico", cursive`;

    // centrar
    const x = 20;
    const y = (canvasType.height / (window.devicePixelRatio || 1)) / 2;

    ctx.fillText(name, x, y);
  }

  if (canvasType) renderTypedSignature();
  inputName?.addEventListener('input', renderTypedSignature);

  // --- cambiar modo ---
  function setMode(mode) {
    if (mode === 'type') {
      drawWrap?.classList.add('hidden');
      typeWrap?.classList.remove('hidden');
      renderTypedSignature();
    } else {
      typeWrap?.classList.add('hidden');
      drawWrap?.classList.remove('hidden');
    }
  }

  radios.forEach(r => {
    r.addEventListener('change', () => setMode(r.value));
  });

  // --- limpiar ---
  clearBtn?.addEventListener('click', () => {
    const mode = document.querySelector('input[name="firma_modo"]:checked')?.value || 'draw';
    if (mode === 'draw') {
      pad?.clear();
    } else {
      if (inputName) inputName.value = '';
      renderTypedSignature();
    }
  });

  // --- aprobar: genera dataURL según modo y lo manda a Livewire ---
  approveBtn.addEventListener('click', async () => {
    const mode = document.querySelector('input[name="firma_modo"]:checked')?.value || 'draw';

    let dataUrl = null;

    if (mode === 'draw') {
      if (!pad || pad.isEmpty()) {
        alert('Por favor firma antes de aprobar.');
        return;
      }
      dataUrl = pad.toDataURL('image/png');
    } else {
      const name = (inputName?.value || '').trim();
      if (!name) {
        alert('Escribe tu nombre para generar la firma.');
        return;
      }
      // asegurar que esté renderizado
      renderTypedSignature();
      dataUrl = canvasType.toDataURL('image/png');
    }

    await @this.set('firma_base64', dataUrl);
    await @this.call('approve');
  });

  // --- resize window ---
  window.addEventListener('resize', () => {
    // redibuja según modo
    const mode = document.querySelector('input[name="firma_modo"]:checked')?.value || 'draw';
    if (mode === 'draw' && canvasDraw && pad) {
      const data = pad.toData();
      resizeCanvas(canvasDraw);
      pad.clear();
      pad.fromData(data);
    } else {
      renderTypedSignature();
    }
  });
}
</script>
