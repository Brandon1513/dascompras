{{-- 
    El toggle se maneja 100% con Alpine (x-data en este div)
    Livewire solo maneja: marcarLeida, marcarTodasLeidas, eliminar
    NO hay @entangle ni wire:model para el estado abierto/cerrado
--}}
<div x-data="{ abierto: false }" @click.outside="abierto = false" class="relative">

    {{-- Botón campana --}}
    <button @click="abierto = !abierto"
            class="relative flex items-center justify-center w-9 h-9 rounded-xl transition-all duration-150
                   {{ $noLeidas > 0 ? 'bg-white/15 text-white' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($noLeidas > 0)
        <span class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1
                     text-[10px] font-black text-white bg-rose-500 rounded-full shadow-sm">
            {{ $noLeidas > 9 ? '9+' : $noLeidas }}
        </span>
        @endif
    </button>

    {{-- Panel de notificaciones — controlado solo por Alpine --}}
    <div x-show="abierto"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
         class="absolute right-0 z-50 mt-2 overflow-hidden bg-white border border-gray-100 shadow-2xl w-80 rounded-2xl"
         style="top: 100%;">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100"
             style="background: linear-gradient(90deg, #4A1660 0%, #6d28d9 100%);">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="text-xs font-bold tracking-wider text-white uppercase">Notificaciones</span>
                @if($noLeidas > 0)
                <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-black text-white bg-rose-500 rounded-full">
                    {{ $noLeidas }}
                </span>
                @endif
            </div>
            @if($noLeidas > 0)
            <button wire:click="marcarTodasLeidas"
                    class="text-[10px] text-white/60 hover:text-white transition font-medium">
                Marcar todas leídas
            </button>
            @endif
        </div>

        {{-- Lista --}}
        <div class="overflow-y-auto max-h-96">
            @forelse($notificaciones as $n)
            <div wire:key="notif-{{ $n->id }}"
                 class="group flex items-start gap-3 px-4 py-3 border-b border-gray-50 transition-colors
                        {{ $n->leida ? 'bg-white hover:bg-gray-50' : 'bg-purple-50/40 hover:bg-purple-50' }}">

                <div class="flex items-center justify-center w-8 h-8 rounded-xl text-sm shrink-0 border {{ $n->color }}">
                    {{ $n->icono }}
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-1">
                        <p class="text-xs leading-snug text-gray-800 {{ $n->leida ? 'font-medium' : 'font-bold' }}">
                            {{ $n->titulo }}
                        </p>
                        @if(!$n->leida)
                        <span class="w-2 h-2 mt-1 bg-purple-500 rounded-full shrink-0"></span>
                        @endif
                    </div>
                    @if($n->cuerpo)
                    <p class="text-[11px] text-gray-500 mt-0.5 leading-snug line-clamp-2">{{ $n->cuerpo }}</p>
                    @endif
                    <p class="text-[10px] text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>

                    <div class="flex items-center gap-2 mt-1.5">
                        @if($n->url)
                        <a href="{{ $n->url }}"
                           wire:click="marcarLeida({{ $n->id }})"
                           class="text-[10px] font-semibold text-purple-600 hover:text-purple-800 transition">
                            Ver →
                        </a>
                        @endif
                        @if(!$n->leida)
                        <button wire:click="marcarLeida({{ $n->id }})"
                                class="text-[10px] text-gray-400 hover:text-gray-600 transition">
                            Marcar leída
                        </button>
                        @endif
                        <button wire:click="eliminar({{ $n->id }})"
                                class="text-[10px] text-gray-300 hover:text-rose-400 transition ml-auto opacity-0 group-hover:opacity-100">
                            ✕
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="flex items-center justify-center w-12 h-12 mb-3 rounded-2xl"
                     style="background: linear-gradient(135deg, #f3e8ff, #ede9fe);">
                    <svg class="w-6 h-6" style="color: #7c3aed" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-400">Sin notificaciones</p>
                <p class="text-[10px] text-gray-300 mt-0.5">Estás al día 🎉</p>
            </div>
            @endforelse
        </div>

        @if($notificaciones->count() > 0)
        <div class="px-4 py-2.5 border-t border-gray-100 bg-gray-50 text-center">
            <a href="{{ route('dashboard') }}"
               @click="abierto = false"
               class="text-[10px] font-semibold transition" style="color: #4A1660">
                Ver dashboard completo →
            </a>
        </div>
        @endif
    </div>
</div>