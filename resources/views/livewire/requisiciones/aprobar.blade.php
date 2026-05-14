<div class="space-y-4">

    {{-- ══ BANNER DE ESTADO ══════════════════════════════════════════════ --}}
    @if($apPendiente)
        <div class="flex items-center gap-3 px-4 py-3 border rounded-xl border-amber-200 bg-amber-50">
            <div class="flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 shrink-0">
                <svg class="w-4 h-4 text-amber-600 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-xs text-amber-800">
                <span class="font-bold">Pendiente de tu firma:</span>
                {{ $apPendiente->nivel?->nombre ?? '—' }}
            </p>
        </div>
    @else
        <div class="flex items-center gap-3 px-4 py-3 border rounded-xl border-gray-200 bg-gray-50">
            <div class="flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 shrink-0">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-xs text-gray-600">
                @if($requisicion->estado === 'aprobada_final')
                    ✅ Esta requisición ya quedó <strong>aprobada</strong>. Ya no requiere tu firma.
                @elseif($requisicion->estado === 'rechazada')
                    ⛔ Esta requisición fue <strong>rechazada</strong>.
                @elseif(!empty($yaFirmoEnEstaReq) && !empty($siguientePendiente))
                    📝 Ya firmaste tu etapa. Ahora está pendiente con:
                    <strong>{{ $siguientePendiente->nivel?->nombre ?? '—' }}</strong>
                    ({{ $siguientePendiente->aprobador?->name ?? 'por rol: ' . ($siguientePendiente->nivel?->rol_aprobador ?? '—') }})
                @elseif(!empty($siguientePendiente))
                    ⏳ En espera de: <strong>{{ $siguientePendiente->nivel?->nombre ?? '—' }}</strong>
                @else
                    ℹ️ No hay aprobaciones pendientes en este momento.
                @endif
            </p>
        </div>
    @endif

    {{-- ══ FIRMA Y ACCIONES (solo si te toca) ══════════════════════════ --}}
    @if($apPendiente)
    <div x-data="{
            modalRechazo: false,
            motivoRechazo: '',
            errorMotivo: false,
            enviando: false,
            abrirModal() {
                this.modalRechazo = true;
                this.motivoRechazo = '';
                this.errorMotivo = false;
                this.$nextTick(() => this.$refs.motivoInput?.focus());
            },
            confirmarRechazo() {
                if (!this.motivoRechazo.trim()) {
                    this.errorMotivo = true;
                    return;
                }
                this.errorMotivo = false;
                $wire.set('comentarios', this.motivoRechazo);
                $wire.call('reject');
                this.modalRechazo = false;
            }
        }">

        {{-- Firma --}}
        <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
            <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100"
                 style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
                <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                <h3 class="text-xs font-bold tracking-widest text-white uppercase">Firma de aprobación</h3>
            </div>
            <div class="p-5 space-y-4">

                <div class="flex items-center gap-4 text-sm">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="firma_modo" value="draw" checked class="text-purple-600 focus:ring-purple-500">
                        <span class="text-gray-700 font-medium">Dibujar</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="firma_modo" value="type" class="text-purple-600 focus:ring-purple-500">
                        <span class="text-gray-700 font-medium">Escribir nombre</span>
                    </label>
                </div>

                <div id="sigDrawWrap" class="rounded-xl border-2 border-gray-200 bg-white overflow-hidden"
                     style="touch-action: none;">
                    <canvas id="sigPad" style="display:block; width:100%; height:160px;"></canvas>
                </div>

                <div id="sigTypeWrap" class="hidden space-y-3">
                    <input id="sigTypeName" type="text"
                           value="{{ auth()->user()->name }}"
                           class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500"
                           placeholder="Escribe tu nombre…">
                    <div class="rounded-xl border-2 border-gray-200 bg-white overflow-hidden">
                        <canvas id="sigTypeCanvas" style="display:block; width:100%; height:160px;"></canvas>
                    </div>
                </div>

                @error('firma_base64')
                    <p class="text-xs text-rose-600">{{ $message }}</p>
                @enderror

                <button type="button" id="btnClearSig"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Limpiar firma
                </button>
            </div>
        </div>

        {{-- Comentarios y botones --}}
        <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
            <div class="p-5 space-y-4">

                @if (session('status'))
                    <div class="flex items-center gap-2 p-3 text-sm font-medium text-emerald-800 border border-emerald-200 rounded-lg bg-emerald-50">
                        {{ session('status') }}
                    </div>
                @endif

                <div>
                    <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                        Comentarios de aprobación
                        <span class="font-normal text-gray-300 normal-case ml-1">(opcional)</span>
                    </label>
                    <textarea wire:model.defer="comentarios" rows="2"
                              placeholder="Observaciones adicionales para la aprobación…"
                              class="w-full text-sm border-gray-200 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500 resize-none"></textarea>
                </div>

                <div class="flex items-center gap-3 pt-1">
                    <button type="button" id="btnApprove"
                            :disabled="enviando"
                            :class="enviando ? 'opacity-60 cursor-not-allowed' : 'hover:shadow-lg'"
                            class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-white rounded-xl shadow-md transition-all"
                            style="background: linear-gradient(135deg, #059669, #047857);">
                        <template x-if="!enviando">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                Aprobar
                            </span>
                        </template>
                        <template x-if="enviando">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Enviando…
                            </span>
                        </template>
                    </button>

                    <button type="button"
                            @click="abrirModal()"
                            class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-white rounded-xl shadow-md hover:shadow-lg transition-all"
                            style="background: linear-gradient(135deg, #dc2626, #b91c1c);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Rechazar
                    </button>
                </div>
            </div>
        </div>

        {{-- ══ MODAL RECHAZO ══════════════════════════════════════════ --}}
        <div x-show="modalRechazo"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display: none;">

            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"
                 @click="modalRechazo = false"></div>

            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 @click.stop>

                <div class="flex items-center gap-3 px-6 py-4 bg-rose-50 border-b border-rose-100">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-rose-100 shrink-0">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-rose-800">Rechazar requisición</h3>
                        <p class="text-xs text-rose-500 mt-0.5">{{ $requisicion->folio }}</p>
                    </div>
                    <button type="button" @click="modalRechazo = false"
                            class="ml-auto text-rose-400 hover:text-rose-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-gray-600">
                        Estás a punto de <strong class="text-rose-700">rechazar</strong> esta requisición.
                        Esta acción notificará al solicitante y detendrá el proceso de aprobación.
                    </p>
                    <div>
                        <label class="block mb-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                            Motivo del rechazo <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            x-ref="motivoInput"
                            x-model="motivoRechazo"
                            @input="errorMotivo = false"
                            rows="4"
                            placeholder="Explica el motivo del rechazo para que el solicitante pueda corregir o justificar la requisición…"
                            :class="errorMotivo ? 'border-red-400 focus:ring-red-500 focus:border-red-500' : 'border-gray-200 focus:ring-rose-500 focus:border-rose-500'"
                            class="w-full text-sm rounded-lg shadow-sm resize-none transition-colors">
                        </textarea>
                        <p x-show="errorMotivo" class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            El motivo del rechazo es obligatorio.
                        </p>
                    </div>
                    <div class="p-3 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-700 flex items-start gap-2">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span>El motivo quedará registrado en el historial y será enviado al solicitante por notificación.</span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-100">
                    <button type="button" @click="modalRechazo = false"
                            class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button type="button"
                            @click="confirmarRechazo()"
                            wire:loading.attr="disabled" wire:target="reject"
                            class="inline-flex items-center gap-2 px-5 py-2 text-sm font-bold text-white bg-rose-600 rounded-lg hover:bg-rose-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="reject">
                            <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Confirmar rechazo
                        </span>
                        <span wire:loading wire:target="reject" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Rechazando…
                        </span>
                    </button>
                </div>
            </div>
        </div>

    </div>{{-- fin x-data --}}
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
(function () {
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
        const dpr        = window.devicePixelRatio || 1;

        let pad = null;

        function initCanvas(c) {
            if (!c) return;
            const w = c.offsetWidth || c.parentElement.offsetWidth || 600;
            c.width  = w * dpr;
            c.height = 160 * dpr;
            c.style.width  = w + 'px';
            c.style.height = '160px';
            const ctx = c.getContext('2d');
            ctx.scale(dpr, dpr);
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, w, 160);
        }

        requestAnimationFrame(() => {
            initCanvas(canvasDraw);
            if (canvasType) initCanvas(canvasType);
            if (canvasDraw) {
                pad = new SignaturePad(canvasDraw, { backgroundColor: 'rgba(255,255,255,1)' });
            }
            renderTypedSig();
        });

        function renderTypedSig() {
            if (!canvasType) return;
            const ctx  = canvasType.getContext('2d');
            const w    = canvasType.width  / dpr;
            const h    = canvasType.height / dpr;
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, w, h);
            const name = (inputName?.value || '').trim();
            if (!name) return;
            ctx.fillStyle    = '#111';
            ctx.textBaseline = 'middle';
            ctx.font         = `italic 52px "Segoe Script","Brush Script MT",cursive`;
            ctx.fillText(name, 20, h / 2);
        }

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
                if (!pad || pad.isEmpty()) {
                    alert('Por favor firma antes de aprobar.');
                    return;
                }
                dataUrl = pad.toDataURL('image/png');
            } else {
                if (!(inputName?.value || '').trim()) {
                    alert('Escribe tu nombre para generar la firma.');
                    return;
                }
                renderTypedSig();
                dataUrl = canvasType.toDataURL('image/png');
            }

            const wireEl = document.querySelector('[wire\\:id]');
            const wireId = wireEl?.getAttribute('wire:id');
            const component = Livewire.find(wireId);
            if (!component) { alert('Error interno. Recarga la página.'); return; }

            // Bloquear botón via Alpine
            const alpineEl = approveBtn.closest('[x-data]');
            if (alpineEl && alpineEl._x_dataStack) {
                const alpineData = Alpine.$data(alpineEl);
                if (alpineData) alpineData.enviando = true;
            }
            approveBtn.disabled = true;

            await component.set('firma_base64', dataUrl);
            await component.call('approve');
        });
    }

    document.addEventListener('DOMContentLoaded', initSig);
    document.addEventListener('livewire:navigated', () => {
        const c = document.getElementById('sigPad');
        if (c) delete c.dataset.inited;
        const b = document.getElementById('btnApprove');
        if (b) delete b.dataset.inited;
        initSig();
    });
    if (document.readyState !== 'loading') setTimeout(initSig, 100);
})();
</script>