<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Notificaciones</h2>
                <p class="text-xs text-gray-400 mt-0.5">
                    @if($noLeidas > 0)
                        {{ $noLeidas }} sin leer
                    @else
                        Estás al día 🎉
                    @endif
                </p>
            </div>
            @if($notificaciones->count() > 0)
            <div class="flex items-center gap-2">
                @if($noLeidas > 0)
                <form method="POST" action="{{ route('notificaciones.todas-leidas') }}">
                    @csrf @method('PATCH')
                    <button class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-purple-700 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 transition">
                        ✓ Marcar todas leídas
                    </button>
                </form>
                @endif
                <form method="POST" action="{{ route('notificaciones.eliminar-todas') }}"
                      onsubmit="return confirm('¿Eliminar todas las notificaciones?')">
                    @csrf @method('DELETE')
                    <button class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-rose-600 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-100 transition">
                        🗑 Eliminar todas
                    </button>
                </form>
            </div>
            @endif
        </div>
    </x-slot>

    <div class="min-h-screen py-6" style="background: linear-gradient(160deg, #f8f5ff 0%, #f1f5f9 50%, #f8f5ff 100%);">
        <div class="max-w-3xl px-4 mx-auto space-y-3 sm:px-6 lg:px-8">

            @if(session('status'))
            <div class="flex items-center gap-2 p-4 text-sm font-medium border shadow-sm text-emerald-800 border-emerald-200 rounded-xl bg-emerald-50">
                ✅ {{ session('status') }}
            </div>
            @endif

            @forelse($notificaciones as $n)
            <div class="overflow-hidden bg-white border rounded-xl shadow-sm transition-all
                        {{ $n->leida ? 'border-gray-100' : 'border-purple-200 shadow-md' }}">
                <div class="flex items-start gap-4 p-4">

                    {{-- Ícono --}}
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl text-lg shrink-0 border {{ $n->color }}">
                        {{ $n->icono }}
                    </div>

                    {{-- Contenido --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm text-gray-900 {{ $n->leida ? 'font-medium' : 'font-bold' }}">
                                    {{ $n->titulo }}
                                    @if(!$n->leida)
                                    <span class="inline-flex items-center ml-1.5 px-1.5 py-0.5 rounded-full text-[10px] font-black text-white bg-purple-500">Nueva</span>
                                    @endif
                                </p>
                                @if($n->cuerpo)
                                <p class="mt-1 text-xs leading-relaxed text-gray-500">{{ $n->cuerpo }}</p>
                                @endif
                                <p class="text-[10px] text-gray-400 mt-1.5">{{ $n->created_at->diffForHumans() }} · {{ $n->created_at->format('d/m/Y H:i') }}</p>
                            </div>

                            {{-- Punto no leída --}}
                            @if(!$n->leida)
                            <div class="w-2.5 h-2.5 rounded-full bg-purple-500 shrink-0 mt-1"></div>
                            @endif
                        </div>

                        {{-- Acciones --}}
                        <div class="flex items-center gap-3 mt-3">
                            @if($n->url)
                            <form method="POST" action="{{ route('notificaciones.leida', $n) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="redirect" value="1">
                                <button class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-white rounded-lg transition"
                                        style="background: linear-gradient(135deg, #4A1660, #7c3aed);">
                                    Ver →
                                </button>
                            </form>
                            @endif

                            @if(!$n->leida)
                            <form method="POST" action="{{ route('notificaciones.leida', $n) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="text-xs text-gray-400 transition hover:text-gray-600">
                                    Marcar leída
                                </button>
                            </form>
                            @endif

                            <form method="POST" action="{{ route('notificaciones.eliminar', $n) }}"
                                  class="inline ml-auto"
                                  onsubmit="return confirm('¿Eliminar esta notificación?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-gray-300 transition hover:text-rose-500">
                                    ✕ Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-20 text-center bg-white border border-gray-100 shadow-sm rounded-2xl">
                <div class="flex items-center justify-center w-16 h-16 mb-4 rounded-2xl"
                     style="background: linear-gradient(135deg, #f3e8ff, #ede9fe);">
                    <svg class="w-8 h-8" style="color: #7c3aed" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-500">Sin notificaciones</p>
                <p class="mt-1 text-xs text-gray-400">Estás completamente al día 🎉</p>
                <a href="{{ route('dashboard') }}"
                   class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white rounded-xl transition"
                   style="background: linear-gradient(135deg, #4A1660, #7c3aed);">
                    Ir al dashboard
                </a>
            </div>
            @endforelse

            @if($notificaciones->hasPages())
            <div class="mt-4">
                {{ $notificaciones->links() }}
            </div>
            @endif

        </div>
    </div>
</x-app-layout>