<div class="max-w-5xl mx-auto space-y-5">

    {{-- ══ LEYENDA ══════════════════════════════════════════════════════ --}}
    <div class="flex items-start gap-3 px-4 py-3 border rounded-lg border-amber-200 bg-amber-50">
        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm font-medium text-amber-800">
            El seguimiento al flujo de aprobación es responsabilidad del solicitante y superiores.
        </p>
    </div>

    {{-- ══ DATOS GENERALES ══════════════════════════════════════════════ --}}
    <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-800">{{ $requisicion->folio }}</h2>
            <div class="flex items-center gap-2">
                @if($requisicion->es_pago_factura)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-violet-100 text-violet-700 border border-violet-200">
                        Pago de factura
                    </span>
                @endif
                @if($requisicion->urgencia === 'urgente')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                        🔴 Urgente
                    </span>
                @endif
            </div>
        </div>

        <dl class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
            <div>
                <dt class="text-xs text-gray-500 mb-0.5">Fecha de elaboración</dt>
                <dd class="font-medium text-gray-800">{{ optional($requisicion->fecha_emision)->format('d/m/Y') }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500 mb-0.5">Solicitante</dt>
                <dd class="font-medium text-gray-800">{{ $requisicion->solicitante?->name }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500 mb-0.5">Departamento</dt>
                <dd class="font-medium text-gray-800">{{ $requisicion->departamentoRef?->nombre ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500 mb-0.5">Centro de costos</dt>
                <dd class="font-medium text-gray-800">{{ $requisicion->centroCostoRef?->nombre ?? '—' }}</dd>
            </div>
        </dl>

        @if($requisicion->justificacion)
        <div class="pt-4 mt-4 border-t border-gray-100">
            <dt class="mb-1 text-xs text-gray-500">Justificación</dt>
            <dd class="text-sm text-gray-700 whitespace-pre-line">{{ $requisicion->justificacion }}</dd>
        </div>
        @endif
    </div>

    {{-- ══ PARTIDAS ════════════════════════════════════════════════════ --}}
    <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-xs font-semibold tracking-wider text-gray-500 uppercase">Partidas de la requisición</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-xs tracking-wider text-gray-500 uppercase bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 font-medium text-left">Cant.</th>
                        <th class="px-4 py-3 font-medium text-left">Descripción</th>
                        <th class="px-4 py-3 font-medium text-left">Unidad</th>
                        <th class="px-4 py-3 font-medium text-left">Proveedor</th>
                        <th class="px-4 py-3 font-medium text-left">Archivos</th>
                        <th class="px-4 py-3 font-medium text-right">P. Unit.</th>
                        <th class="px-4 py-3 font-medium text-right">Subtotal</th>
                        <th class="px-4 py-3 font-medium text-right">Impuesto</th>
                        <th class="px-4 py-3 font-medium text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($requisicion->items as $it)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-600">
                                {{ rtrim(rtrim(number_format($it->cantidad,3,'.',''), '0'), '.') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-gray-800">{{ $it->descripcion }}</span>
                                @if($it->link_compra)
                                    <a href="{{ $it->link_compra }}" target="_blank" rel="noopener"
                                       class="ml-1 text-xs text-indigo-600 hover:underline">Ver link</a>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $it->unidad_label }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $it->proveedor_sugerido ?: '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    @foreach($it->archivos as $arch)
                                        <a href="{{ Storage::url($arch->path) }}" target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:underline max-w-[160px]">
                                            <span>{{ $arch->icono }}</span>
                                            <span class="truncate">{{ $arch->nombre_original }}</span>
                                        </a>
                                    @endforeach
                                    @if($it->ficha_tecnica_path && $it->archivos->isEmpty())
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($it->ficha_tecnica_path) }}"
                                           target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:underline">
                                            📄 {{ $it->ficha_tecnica_nombre ?: 'Ficha técnica' }}
                                        </a>
                                    @endif
                                    @if($it->archivos->isEmpty() && !$it->ficha_tecnica_path)
                                        <span class="text-xs text-gray-300">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">${{ number_format($it->precio_unitario,2) }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">${{ number_format($it->subtotal,2) }}</td>
                            <td class="px-4 py-3 text-right">
                                @if($it->tipoImpuesto)
                                    <div class="text-gray-700">${{ number_format($it->monto_impuesto,2) }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $it->tipoImpuesto->nombre }}</div>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-semibold text-right text-gray-800">
                                ${{ number_format($it->total_item ?: $it->subtotal, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-gray-400">Sin partidas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex justify-end px-5 py-4 border-t border-gray-100">
            <div class="w-full max-w-xs space-y-1.5 text-sm">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span class="font-medium text-gray-800">${{ number_format($requisicion->subtotal,2) }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Impuestos</span>
                    <span class="font-medium text-gray-800">${{ number_format($requisicion->iva,2) }}</span>
                </div>
                <div class="flex justify-between pt-2 text-base font-semibold text-gray-900 border-t border-gray-200">
                    <span>Total</span>
                    <span>${{ number_format($requisicion->total,2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ FORMULARIO DE RECEPCIÓN ══════════════════════════════════════ --}}
    <div class="p-5 space-y-5 bg-white border border-gray-200 shadow-sm rounded-xl">
        <h3 class="text-xs font-semibold tracking-wider text-gray-500 uppercase">Registrar recepción</h3>

        @if (session('status'))
            <div class="flex items-center gap-2 p-3 text-sm text-green-800 border border-green-200 rounded-lg bg-green-50">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">Fecha de recibido</label>
                <input type="date"
                       wire:model.defer="fecha_recibido"
                       class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('fecha_recibido') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">Área que recibe</label>
                <select wire:model.defer="area_recibe"
                        class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @if(!empty($area_recibe))
                        <option value="{{ $area_recibe }}">{{ $area_recibe }}</option>
                    @else
                        <option value="">— Selecciona —</option>
                    @endif
                    @foreach($departamentos as $d)
                        <option value="{{ $d['nombre'] }}">{{ $d['nombre'] }}</option>
                    @endforeach
                </select>
                @error('area_recibe') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block mb-1 text-sm font-medium text-gray-700">Nombre de quien recibe</label>
                <input type="text"
                       wire:model.defer="recibe_nombre"
                       placeholder="Ej. Juan Pérez (Compras)"
                       class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('recibe_nombre') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- ── Firma ──────────────────────────────────────────────────── --}}
        <div>
            <label class="block mb-3 text-sm font-medium text-gray-700">Firma de conformidad <span class="text-red-500">*</span></label>

            <div class="flex items-center gap-4 mb-4 text-sm">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="modoFirmaRecibir" value="dibujar" checked class="text-indigo-600">
                    <span>Dibujar</span>
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="modoFirmaRecibir" value="escribir" class="text-indigo-600">
                    <span>Escribir nombre</span>
                </label>
            </div>

            {{-- Modo dibujar --}}
            <div id="sigDrawWrap" class="p-2 border border-gray-200 rounded-lg bg-gray-50">
                <canvas id="canvasFirmaRecibir" class="w-full" style="height:160px;"></canvas>
            </div>

            {{-- Modo escribir --}}
            <div id="sigTypeWrap" class="hidden space-y-3">
                <div class="flex gap-2">
                    <input id="firmaTextoRecibir" type="text"
                           placeholder="Escribe tu nombre…"
                           class="flex-1 text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <button type="button" id="btnGenerarFirmaTexto"
                            class="px-4 py-2 text-sm font-medium text-white transition bg-indigo-600 rounded-lg hover:bg-indigo-700">
                        Generar
                    </button>
                </div>
                <div class="p-2 border border-gray-200 rounded-lg bg-gray-50">
                    <canvas id="canvasFirmaTexto" class="w-full" style="height:160px;"></canvas>
                </div>
            </div>

            <input type="hidden" wire:model.defer="firma_base64" id="firmaBase64Recibir">

            @error('firma_base64')
                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
            @enderror

            <button type="button" id="clearFirmaRecibir"
                    class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Limpiar firma
            </button>
        </div>

        {{-- ── Botón guardar ────────────────────────────────────────── --}}
        <div class="pt-2">
            <button type="button" id="btnGuardarRecepcion"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition disabled:opacity-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <span wire:loading.remove wire:target="save">Guardar recepción</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </button>
        </div>
    </div>

</div>

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', initFirmaRecibir);
document.addEventListener('livewire:navigated', initFirmaRecibir);

function initFirmaRecibir() {
    const canvasDraw  = document.getElementById('canvasFirmaRecibir');
    const btnGuardar  = document.getElementById('btnGuardarRecepcion');
    if (!canvasDraw || !btnGuardar) return;
    if (canvasDraw.dataset.inited === '1') return;
    canvasDraw.dataset.inited = '1';

    const ctx         = canvasDraw.getContext('2d');
    const clearBtn    = document.getElementById('clearFirmaRecibir');
    const hidden      = document.getElementById('firmaBase64Recibir');
    const radios      = document.querySelectorAll('input[name="modoFirmaRecibir"]');
    const drawWrap    = document.getElementById('sigDrawWrap');
    const typeWrap    = document.getElementById('sigTypeWrap');
    const canvasType  = document.getElementById('canvasFirmaTexto');
    const inputTexto  = document.getElementById('firmaTextoRecibir');
    const btnGenerar  = document.getElementById('btnGenerarFirmaTexto');

    let drawing = false;
    let hasInk  = false;

    function resizeCanvas(c) {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect  = c.getBoundingClientRect();
        const prev  = (c === canvasDraw && hasInk) ? c.toDataURL() : null;
        c.width     = rect.width * ratio;
        c.height    = 160 * ratio;
        c.style.height = '160px';
        c.getContext('2d').setTransform(ratio, 0, 0, ratio, 0, 0);
        if (prev) {
            const img = new Image();
            img.onload = () => c.getContext('2d').drawImage(img, 0, 0, rect.width, 160);
            img.src = prev;
        }
        setupStroke();
    }

    function setupStroke() {
        ctx.lineWidth   = 2;
        ctx.lineCap     = 'round';
        ctx.strokeStyle = '#111827';
    }

    setTimeout(() => { resizeCanvas(canvasDraw); if (canvasType) resizeCanvas(canvasType); }, 50);
    window.addEventListener('resize', () => { resizeCanvas(canvasDraw); if (canvasType) resizeCanvas(canvasType); });

    function getModo() {
        return document.querySelector('input[name="modoFirmaRecibir"]:checked')?.value || 'dibujar';
    }

    function getPos(e) {
        const rect = canvasDraw.getBoundingClientRect();
        return {
            x: (e.touches ? e.touches[0].clientX : e.clientX) - rect.left,
            y: (e.touches ? e.touches[0].clientY : e.clientY) - rect.top,
        };
    }

    canvasDraw.addEventListener('mousedown',  e => { if (getModo() !== 'dibujar') return; e.preventDefault(); drawing = true; hasInk = true; const p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); });
    canvasDraw.addEventListener('mousemove',  e => { if (!drawing || getModo() !== 'dibujar') return; e.preventDefault(); const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); });
    window.addEventListener('mouseup',        e => { drawing = false; });
    canvasDraw.addEventListener('touchstart', e => { if (getModo() !== 'dibujar') return; e.preventDefault(); drawing = true; hasInk = true; const p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); }, { passive: false });
    canvasDraw.addEventListener('touchmove',  e => { if (!drawing || getModo() !== 'dibujar') return; e.preventDefault(); const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); }, { passive: false });
    window.addEventListener('touchend',       e => { drawing = false; }, { passive: false });

    radios.forEach(r => r.addEventListener('change', () => {
        const draw = getModo() === 'dibujar';
        drawWrap?.classList.toggle('hidden', !draw);
        typeWrap?.classList.toggle('hidden', draw);
    }));

    function clearAll() {
        const rect = canvasDraw.getBoundingClientRect();
        ctx.clearRect(0, 0, rect.width, 160);
        hasInk = false;
        hidden.value = '';
        @this.set('firma_base64', null);
    }

    clearBtn?.addEventListener('click', clearAll);

    function renderTexto() {
        if (!canvasType) return;
        const nombre = (inputTexto?.value || '').trim();
        const c      = canvasType.getContext('2d');
        const w      = canvasType.width  / (window.devicePixelRatio || 1);
        const h      = canvasType.height / (window.devicePixelRatio || 1);
        c.fillStyle  = '#ffffff';
        c.fillRect(0, 0, w, h);
        if (!nombre) return;
        let fontSize = 56;
        c.font = `${fontSize}px "Great Vibes", cursive`;
        while (c.measureText(nombre).width > w - 30 && fontSize > 28) {
            fontSize -= 2;
            c.font = `${fontSize}px "Great Vibes", cursive`;
        }
        c.fillStyle    = '#111827';
        c.textAlign    = 'center';
        c.textBaseline = 'middle';
        c.fillText(nombre, w / 2, h / 2);
        hasInk = true;
    }

    btnGenerar?.addEventListener('click', () => {
        if (!(inputTexto?.value || '').trim()) { alert('Escribe tu nombre para generar la firma.'); return; }
        renderTexto();
    });

    btnGuardar.addEventListener('click', async () => {
        if (!hasInk) { alert('Por favor firma antes de guardar la recepción.'); return; }

        let dataUrl;
        if (getModo() === 'dibujar') {
            dataUrl = canvasDraw.toDataURL('image/png');
        } else {
            if (!canvasType) return;
            dataUrl = canvasType.toDataURL('image/png');
        }

        hidden.value = dataUrl;
        await @this.set('firma_base64', dataUrl);
        await @this.call('save');
    });
}
</script>
@endpush