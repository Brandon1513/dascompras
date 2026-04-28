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

    {{-- ══ BANNER DE ESTADO ══════════════════════════════════════════════ --}}
    @if($apPendiente)
        <div class="flex items-center gap-3 px-4 py-3 text-sm border rounded-lg border-amber-200 bg-amber-50 text-amber-900">
            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Pendiente de tu firma: <strong>{{ $apPendiente->nivel?->nombre ?? '—' }}</strong></span>
        </div>
    @else
        <div class="flex items-center gap-3 px-4 py-3 text-sm border rounded-lg border-slate-200 bg-slate-50 text-slate-700">
            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>
                @if($requisicion->estado === 'aprobada_final')
                    ✅ Esta requisición ya quedó <strong>aprobada</strong>. Ya no requiere tu firma.
                @elseif($requisicion->estado === 'rechazada')
                    ⛔ Esta requisición fue <strong>rechazada</strong>.
                @elseif(!empty($yaFirmoEnEstaReq) && !empty($siguientePendiente))
                    📝 Ya firmaste tu etapa. Ahora está pendiente con: <strong>{{ $siguientePendiente->nivel?->nombre ?? '—' }}</strong>
                    ({{ $siguientePendiente->aprobador?->name ?? 'por rol: ' . ($siguientePendiente->nivel?->rol_aprobador ?? '—') }})
                @elseif(!empty($siguientePendiente))
                    ⏳ En espera de: <strong>{{ $siguientePendiente->nivel?->nombre ?? '—' }}</strong>
                @else
                    ℹ️ No hay aprobaciones pendientes en este momento.
                @endif
            </span>
        </div>
    @endif

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
            <h3 class="text-xs font-semibold tracking-wider text-gray-500 uppercase">Partidas</h3>
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
                        <tr><td colspan="9" class="px-4 py-6 text-center text-gray-400">Sin partidas.</td></tr>
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

    {{-- ══ CADENA DE APROBACIONES ═══════════════════════════════════════ --}}
    <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-xl">
        <h3 class="mb-4 text-xs font-semibold tracking-wider text-gray-500 uppercase">Cadena de aprobaciones</h3>
        <ul class="space-y-3">
            @foreach($requisicion->aprobaciones->sortBy(fn($a) => $a->nivel?->orden ?? 999) as $ap)
                <li class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span @class([
                            'w-2 h-2 rounded-full shrink-0',
                            'bg-amber-400'   => $ap->estado === 'pendiente',
                            'bg-emerald-500' => $ap->estado === 'aprobada',
                            'bg-rose-500'    => $ap->estado === 'rechazada',
                        ])></span>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $ap->nivel?->nombre ?? '—' }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $ap->aprobador?->name ?? 'Por rol: ' . ($ap->nivel?->rol_aprobador ?? '—') }}
                                @if($ap->firmado_en)
                                    · {{ $ap->firmado_en->format('d/m/Y H:i') }}
                                @endif
                            </p>
                            @if($ap->comentarios)
                                <p class="text-xs text-gray-400 italic mt-0.5">"{{ $ap->comentarios }}"</p>
                            @endif
                        </div>
                    </div>
                    <span @class([
                        'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border shrink-0',
                        'bg-amber-50 text-amber-700 border-amber-200'       => $ap->estado === 'pendiente',
                        'bg-emerald-50 text-emerald-700 border-emerald-200' => $ap->estado === 'aprobada',
                        'bg-rose-50 text-rose-700 border-rose-200'         => $ap->estado === 'rechazada',
                    ])>{{ ucfirst($ap->estado) }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- ══ FIRMA Y ACCIONES (solo si te toca) ══════════════════════════ --}}
    @if($apPendiente)

        {{-- Firma --}}
        <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-xl">
            <h3 class="mb-4 text-xs font-semibold tracking-wider text-gray-500 uppercase">Firma</h3>

            <div class="flex items-center gap-4 mb-4 text-sm">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="firma_modo" value="draw" checked class="text-indigo-600">
                    <span>Dibujar</span>
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="firma_modo" value="type" class="text-indigo-600">
                    <span>Escribir nombre</span>
                </label>
            </div>

            <div id="sigDrawWrap" class="p-2 border border-gray-200 rounded-lg bg-gray-50">
                <canvas id="sigPad" class="w-full" style="height:160px;"></canvas>
            </div>

            <div id="sigTypeWrap" class="hidden space-y-3">
                <input id="sigTypeName" type="text"
                       value="{{ auth()->user()->name }}"
                       class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Escribe tu nombre…">
                <div class="p-2 border border-gray-200 rounded-lg bg-gray-50">
                    <canvas id="sigTypeCanvas" class="w-full" style="height:160px;"></canvas>
                </div>
            </div>

            @error('firma_base64')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror

            <button type="button" id="btnClearSig"
                    class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Limpiar firma
            </button>
        </div>

        {{-- Comentarios y botones --}}
        <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-xl">

            @if (session('status'))
                <div class="flex items-center gap-2 p-3 mb-4 text-sm text-green-800 border border-green-200 rounded-lg bg-green-50">
                    {{ session('status') }}
                </div>
            @endif

            <label class="block mb-2 text-sm font-medium text-gray-700">Comentarios <span class="font-normal text-gray-400">(opcional)</span></label>
            <textarea wire:model.defer="comentarios" rows="3"
                      placeholder="Escribe comentarios para dejar rastro de la decisión…"
                      class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>

            <div class="flex items-center gap-3 mt-4">
                <button type="button" id="btnApprove"
                        wire:loading.attr="disabled" wire:target="approve"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="approve">
                        <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Aprobar
                    </span>
                    <span wire:loading wire:target="approve">Guardando…</span>
                </button>

                <button type="button" wire:click="reject"
                        wire:loading.attr="disabled" wire:target="reject"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-rose-600 rounded-lg hover:bg-rose-700 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="reject">
                        <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        Rechazar
                    </span>
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
    if (approveBtn.dataset.inited === '1') return;
    approveBtn.dataset.inited = '1';

    const radios     = document.querySelectorAll('input[name="firma_modo"]');
    const drawWrap   = document.getElementById('sigDrawWrap');
    const typeWrap   = document.getElementById('sigTypeWrap');
    const canvasDraw = document.getElementById('sigPad');
    const canvasType = document.getElementById('sigTypeCanvas');
    const inputName  = document.getElementById('sigTypeName');
    const clearBtn   = document.getElementById('btnClearSig');

    function resizeCanvas(canvas) {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect  = canvas.getBoundingClientRect();
        canvas.width  = rect.width * ratio;
        canvas.height = rect.height * ratio;
        canvas.getContext('2d').setTransform(ratio, 0, 0, ratio, 0, 0);
    }

    let pad = null;
    if (canvasDraw) {
        resizeCanvas(canvasDraw);
        pad = new SignaturePad(canvasDraw, { backgroundColor: 'rgba(255,255,255,1)' });
    }

    function renderTypedSig() {
        if (!canvasType) return;
        resizeCanvas(canvasType);
        const ctx  = canvasType.getContext('2d');
        const h    = canvasType.height / (window.devicePixelRatio || 1);
        const w    = canvasType.width  / (window.devicePixelRatio || 1);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, w, h);
        const name = (inputName?.value || '').trim();
        if (!name) return;
        ctx.fillStyle    = '#111';
        ctx.textBaseline = 'middle';
        ctx.font         = `italic 52px "Segoe Script","Brush Script MT",cursive`;
        ctx.fillText(name, 20, h / 2);
    }

    if (canvasType) renderTypedSig();
    inputName?.addEventListener('input', renderTypedSig);

    radios.forEach(r => r.addEventListener('change', () => {
        const draw = r.value === 'draw';
        drawWrap?.classList.toggle('hidden', !draw);
        typeWrap?.classList.toggle('hidden', draw);
        if (!draw) renderTypedSig();
    }));

    clearBtn?.addEventListener('click', () => {
        const mode = document.querySelector('input[name="firma_modo"]:checked')?.value || 'draw';
        if (mode === 'draw') { pad?.clear(); }
        else { if (inputName) inputName.value = ''; renderTypedSig(); }
    });

    approveBtn.addEventListener('click', async () => {
        const mode = document.querySelector('input[name="firma_modo"]:checked')?.value || 'draw';
        let dataUrl = null;

        if (mode === 'draw') {
            if (!pad || pad.isEmpty()) { alert('Por favor firma antes de aprobar.'); return; }
            dataUrl = pad.toDataURL('image/png');
        } else {
            if (!(inputName?.value || '').trim()) { alert('Escribe tu nombre para generar la firma.'); return; }
            renderTypedSig();
            dataUrl = canvasType.toDataURL('image/png');
        }

        await @this.set('firma_base64', dataUrl);
        await @this.call('approve');
    });

    window.addEventListener('resize', () => {
        const mode = document.querySelector('input[name="firma_modo"]:checked')?.value || 'draw';
        if (mode === 'draw' && canvasDraw && pad) {
            const data = pad.toData();
            resizeCanvas(canvasDraw);
            pad.clear();
            pad.fromData(data);
        } else { renderTypedSig(); }
    });
}
</script>