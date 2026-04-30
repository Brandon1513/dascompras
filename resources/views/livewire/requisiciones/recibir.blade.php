<div class="max-w-5xl mx-auto space-y-5">

    {{-- ══ LEYENDA ══════════════════════════════════════════════════════ --}}
    <div class="flex items-start gap-3 px-4 py-3 border rounded-xl border-amber-200/80 bg-amber-50/80">
        <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <p class="text-xs font-medium text-amber-800">
            El seguimiento al flujo de aprobación es responsabilidad del solicitante y superiores.
        </p>
    </div>

    {{-- ══ DATOS GENERALES ══════════════════════════════════════════════ --}}
    <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
             style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
            <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-xs font-bold tracking-widest text-white uppercase">Datos generales</h3>
        </div>
        <div class="p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-gray-900">{{ $requisicion->folio }}</h2>
                    <p class="text-xs text-gray-400 mt-0.5">{{ optional($requisicion->fecha_emision)->format('d/m/Y') }} · {{ $requisicion->solicitante?->name }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @if($requisicion->es_pago_factura)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-violet-100 text-violet-700">Pago de factura</span>
                    @endif
                    @if($requisicion->urgencia === 'urgente')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold bg-red-100 text-red-700">🔴 Urgente</span>
                    @endif
                </div>
            </div>

            <dl class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
                <div>
                    <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Departamento</dt>
                    <dd class="font-semibold text-gray-800">{{ $requisicion->departamentoRef?->nombre ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Centro de costos</dt>
                    <dd class="font-semibold text-gray-800">{{ $requisicion->centroCostoRef?->nombre ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Subtotal</dt>
                    <dd class="font-semibold text-gray-800">${{ number_format($requisicion->subtotal, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total</dt>
                    <dd class="text-base font-bold" style="color: #4A1660">${{ number_format($requisicion->total, 2) }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- ══ PARTIDAS ════════════════════════════════════════════════════ --}}
    <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
             style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
            <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            <h3 class="text-xs font-bold tracking-widest text-white uppercase">Partidas</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Cant.</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Descripción</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Unidad</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Proveedor</th>
                        <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">P. Unit.</th>
                        <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Subtotal</th>
                        <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Impuesto</th>
                        <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($requisicion->items as $it)
                        <tr class="hover:bg-purple-50/20 transition-colors">
                            <td class="px-4 py-3 text-xs text-gray-500">{{ (int) $it->cantidad }}</td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-gray-800">{{ $it->descripcion }}</span>
                                @if($it->link_compra)
                                    <a href="{{ $it->link_compra }}" target="_blank" class="ml-1 text-xs text-indigo-500 hover:underline">ver link</a>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $it->unidad_label }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600">{{ $it->proveedor_sugerido ?: '—' }}</td>
                            <td class="px-4 py-3 text-right text-xs text-gray-600">${{ number_format($it->precio_unitario, 2) }}</td>
                            <td class="px-4 py-3 text-right text-xs text-gray-600">${{ number_format($it->subtotal, 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                @if($it->tipoImpuesto)
                                    <div class="text-xs text-gray-600">${{ number_format($it->monto_impuesto, 2) }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $it->tipoImpuesto->nombre }}</div>
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-gray-800 text-sm">
                                ${{ number_format($it->total_item ?: $it->subtotal, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400 text-sm">Sin partidas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex justify-end px-5 py-4 border-t border-gray-100 bg-gray-50">
            <div class="w-full max-w-xs space-y-2">
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Subtotal</span>
                    <span class="font-medium text-gray-700">${{ number_format($requisicion->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Impuestos</span>
                    <span class="font-medium text-gray-700">${{ number_format($requisicion->iva, 2) }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-200">
                    <span class="font-bold text-gray-800">Total</span>
                    <span class="text-lg font-bold" style="color: #4A1660">${{ number_format($requisicion->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ FORMULARIO DE RECEPCIÓN ══════════════════════════════════════ --}}
    <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
             style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
            <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-xs font-bold tracking-widest text-white uppercase">Registrar recepción</h3>
        </div>
        <div class="p-5 space-y-5">

            @if (session('status'))
                <div class="flex items-center gap-2 p-3 text-sm font-medium text-emerald-800 border border-emerald-200 rounded-lg bg-emerald-50">
                    ✅ {{ session('status') }}
                </div>
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Fecha de recibido</label>
                    <input type="date" wire:model.defer="fecha_recibido"
                           class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                    @error('fecha_recibido') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Área que recibe</label>
                    <select wire:model.defer="area_recibe"
                            class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                        <option value="">— Selecciona —</option>
                        @foreach($departamentos as $d)
                            <option value="{{ $d['nombre'] }}" @selected($area_recibe === $d['nombre'])>{{ $d['nombre'] }}</option>
                        @endforeach
                    </select>
                    @error('area_recibe') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Nombre de quien recibe</label>
                    <input type="text" wire:model.defer="recibe_nombre"
                           placeholder="Ej. Juan Pérez"
                           class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                    @error('recibe_nombre') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ── Firma ──────────────────────────────────────────────── --}}
            <div>
                <label class="block mb-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                    Firma de conformidad <span class="text-red-500">*</span>
                </label>

                {{-- Selector de modo --}}
                <div class="flex items-center gap-4 mb-3 text-sm">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="modoFirmaRecibir" value="dibujar" checked
                               class="text-purple-600 focus:ring-purple-500">
                        <span class="text-gray-700 font-medium">Dibujar</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="modoFirmaRecibir" value="escribir"
                               class="text-purple-600 focus:ring-purple-500">
                        <span class="text-gray-700 font-medium">Escribir nombre</span>
                    </label>
                </div>

                {{-- Canvas dibujar --}}
                <div id="sigDrawWrap" class="rounded-xl border-2 border-gray-200 bg-white overflow-hidden"
                     style="touch-action: none;">
                    <canvas id="canvasFirmaRecibir" style="display:block; width:100%; height:160px;"></canvas>
                </div>

                {{-- Canvas escribir --}}
                <div id="sigTypeWrap" class="hidden space-y-3">
                    <div class="flex gap-2">
                        <input id="firmaTextoRecibir" type="text"
                               placeholder="Escribe tu nombre completo…"
                               class="flex-1 text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500">
                        <button type="button" id="btnGenerarFirmaTexto"
                                class="px-4 py-2 text-sm font-semibold text-white rounded-lg transition"
                                style="background: linear-gradient(135deg, #4A1660, #7c3aed);">
                            Generar
                        </button>
                    </div>
                    <div class="rounded-xl border-2 border-gray-200 bg-white overflow-hidden">
                        <canvas id="canvasFirmaTexto" style="display:block; width:100%; height:160px;"></canvas>
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

            {{-- ── Botón guardar ──────────────────────────────────────── --}}
            <div class="pt-2">
                <button type="button" id="btnGuardarRecepcion"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-white rounded-xl shadow-md hover:shadow-lg transition-all"
                        style="background: linear-gradient(135deg, #059669, #047857);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Guardar recepción
                </button>
            </div>

        </div>
    </div>

</div>

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
@endpush

@push('scripts')
<script>
(function () {
    function initFirmaRecibir() {
        const canvasDraw = document.getElementById('canvasFirmaRecibir');
        const btnGuardar = document.getElementById('btnGuardarRecepcion');
        if (!canvasDraw || !btnGuardar) return;
        if (canvasDraw.dataset.inited === '1') return;
        canvasDraw.dataset.inited = '1';

        const canvasType = document.getElementById('canvasFirmaTexto');
        const clearBtn   = document.getElementById('clearFirmaRecibir');
        const hidden     = document.getElementById('firmaBase64Recibir');
        const radios     = document.querySelectorAll('input[name="modoFirmaRecibir"]');
        const drawWrap   = document.getElementById('sigDrawWrap');
        const typeWrap   = document.getElementById('sigTypeWrap');
        const inputTexto = document.getElementById('firmaTextoRecibir');
        const btnGenerar = document.getElementById('btnGenerarFirmaTexto');
        const dpr        = window.devicePixelRatio || 1;

        let drawing = false;
        let hasInk  = false;

        // ── Inicializar canvas con tamaño real ──────────────────────────
        function initCanvas(c) {
            if (!c) return;
            const w = c.offsetWidth  || c.parentElement.offsetWidth || 600;
            const h = 160;
            c.width  = w * dpr;
            c.height = h * dpr;
            c.style.width  = w + 'px';
            c.style.height = h + 'px';
            const ctx = c.getContext('2d');
            ctx.scale(dpr, dpr);
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, w, h);
        }

        // Esperar a que el DOM esté renderizado antes de inicializar
        requestAnimationFrame(() => {
            initCanvas(canvasDraw);
            if (canvasType) initCanvas(canvasType);
            setupDraw();
        });

        function setupDraw() {
            const ctx = canvasDraw.getContext('2d');
            ctx.lineWidth   = 2.5;
            ctx.lineCap     = 'round';
            ctx.lineJoin    = 'round';
            ctx.strokeStyle = '#111827';

            function getPos(e) {
                const rect = canvasDraw.getBoundingClientRect();
                const src  = e.touches ? e.touches[0] : e;
                return {
                    x: (src.clientX - rect.left),
                    y: (src.clientY - rect.top),
                };
            }

            canvasDraw.addEventListener('mousedown', e => {
                if (getModo() !== 'dibujar') return;
                e.preventDefault();
                drawing = true;
                hasInk  = true;
                const p = getPos(e);
                ctx.beginPath();
                ctx.moveTo(p.x, p.y);
            });

            canvasDraw.addEventListener('mousemove', e => {
                if (!drawing || getModo() !== 'dibujar') return;
                e.preventDefault();
                const p = getPos(e);
                ctx.lineTo(p.x, p.y);
                ctx.stroke();
            });

            window.addEventListener('mouseup', () => { drawing = false; });

            canvasDraw.addEventListener('touchstart', e => {
                if (getModo() !== 'dibujar') return;
                e.preventDefault();
                drawing = true;
                hasInk  = true;
                const p = getPos(e);
                ctx.beginPath();
                ctx.moveTo(p.x, p.y);
            }, { passive: false });

            canvasDraw.addEventListener('touchmove', e => {
                if (!drawing || getModo() !== 'dibujar') return;
                e.preventDefault();
                const p = getPos(e);
                ctx.lineTo(p.x, p.y);
                ctx.stroke();
            }, { passive: false });

            window.addEventListener('touchend', () => { drawing = false; }, { passive: false });
        }

        function getModo() {
            return document.querySelector('input[name="modoFirmaRecibir"]:checked')?.value || 'dibujar';
        }

        // ── Cambio de modo ──────────────────────────────────────────────
        radios.forEach(r => r.addEventListener('change', () => {
            const esDibujar = getModo() === 'dibujar';
            drawWrap?.classList.toggle('hidden', !esDibujar);
            typeWrap?.classList.toggle('hidden', esDibujar);
        }));

        // ── Limpiar ──────────────────────────────────────────────────────
        function clearAll() {
            const ctx = canvasDraw.getContext('2d');
            const w   = canvasDraw.width  / dpr;
            const h   = canvasDraw.height / dpr;
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, w, h);
            hasInk = false;
            hidden.value = '';
            Livewire.find(
                canvasDraw.closest('[wire\\:id]')?.getAttribute('wire:id')
            )?.set('firma_base64', null);
        }

        clearBtn?.addEventListener('click', clearAll);

        // ── Generar firma de texto ────────────────────────────────────────
        function renderTexto() {
            if (!canvasType) return;
            const nombre = (inputTexto?.value || '').trim();
            const c    = canvasType.getContext('2d');
            const w    = canvasType.width  / dpr;
            const h    = canvasType.height / dpr;
            c.fillStyle = '#ffffff';
            c.fillRect(0, 0, w, h);
            if (!nombre) return;

            let fontSize = 56;
            c.font = `${fontSize}px "Great Vibes", cursive`;
            while (c.measureText(nombre).width > w - 40 && fontSize > 24) {
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
            if (!(inputTexto?.value || '').trim()) {
                alert('Escribe tu nombre para generar la firma.');
                return;
            }
            renderTexto();
        });

        // ── Guardar recepción ─────────────────────────────────────────────
        btnGuardar.addEventListener('click', async () => {
            if (!hasInk) {
                alert('Por favor firma antes de guardar la recepción.');
                return;
            }

            let dataUrl;
            if (getModo() === 'dibujar') {
                dataUrl = canvasDraw.toDataURL('image/png');
            } else {
                if (!canvasType) return;
                dataUrl = canvasType.toDataURL('image/png');
            }

            hidden.value = dataUrl;

            // Livewire 3: usar $wire desde el elemento
            const wireEl  = document.querySelector('[wire\\:id]');
            const wireId  = wireEl?.getAttribute('wire:id');
            const component = Livewire.find(wireId);

            if (!component) {
                alert('Error interno: no se encontró el componente Livewire.');
                return;
            }

            await component.set('firma_base64', dataUrl);
            await component.call('save');
        });
    }

    // Inicializar en carga normal y en navegación de Livewire
    document.addEventListener('DOMContentLoaded', initFirmaRecibir);
    document.addEventListener('livewire:navigated', () => {
        // Resetear flag para permitir re-inicialización
        const c = document.getElementById('canvasFirmaRecibir');
        if (c) delete c.dataset.inited;
        initFirmaRecibir();
    });
    // También inicializar si Livewire ya está listo
    if (document.readyState !== 'loading') {
        setTimeout(initFirmaRecibir, 100);
    }
})();
</script>
@endpush