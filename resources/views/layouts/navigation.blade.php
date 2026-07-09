<nav x-data="{ open: false }" class="bg-[#4A1660] shadow-lg">

    {{-- Línea decorativa superior --}}
    <div class="h-0.5 w-full" style="background: linear-gradient(90deg, #c084fc 0%, #e879f9 50%, #a855f7 100%); opacity: 0.5;"></div>

    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- ── Izquierda: Logo + Nav ─────────────────────────────────── --}}
            <div class="flex items-center gap-6">

                {{-- Logo --}}
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 shrink-0">
                    <x-application-logo class="block w-auto text-white fill-current h-9" />
                    <span class="hidden text-xs font-semibold tracking-widest text-purple-300 uppercase lg:block">
                        Compras
                    </span>
                </a>

                {{-- ── Menú desktop ──────────────────────────────────────── --}}
                @auth
                <div class="hidden md:flex items-center gap-0.5">

                    {{-- Dashboard --}}
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150
                              {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Inicio
                    </a>

                    {{-- Administración --}}
                    @if(Auth::user()->hasAnyRole(['administrador','recursos_humanos']))
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150
                                       {{ request()->routeIs('empleados.*','departamentos.*') ? 'bg-white/20 text-white' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Administración
                            <svg class="w-3 h-3 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-1"
                             class="absolute top-full left-0 mt-1.5 w-56 bg-white rounded-xl shadow-xl border border-gray-100 py-1.5 z-50">
                            @if(Auth::user()->hasRole('administrador'))
                            <a href="{{ route('empleados.index') }}"
                               class="flex items-center gap-2.5 px-3 py-2 mx-1.5 rounded-lg text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-colors duration-100
                                      {{ request()->routeIs('empleados.*') ? 'bg-purple-50 text-purple-700 font-medium' : '' }}">
                                <span class="flex items-center justify-center rounded-lg w-7 h-7 bg-violet-100 text-violet-600 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </span>
                                Gestionar Usuarios
                            </a>
                            <a href="{{ route('departamentos.gerentes.index') }}"
                               class="flex items-center gap-2.5 px-3 py-2 mx-1.5 rounded-lg text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-colors duration-100
                                      {{ request()->routeIs('departamentos.*') ? 'bg-purple-50 text-purple-700 font-medium' : '' }}">
                                <span class="flex items-center justify-center text-blue-600 bg-blue-100 rounded-lg w-7 h-7 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </span>
                                Departamentos
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Expedientes --}}
                    @if(Auth::user()->hasAnyRole(['administrador','compras']))
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150
                                       {{ request()->routeIs('expedientes.*') ? 'bg-white/20 text-white' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            Expedientes
                            <svg class="w-3 h-3 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-1"
                             class="absolute top-full left-0 mt-1.5 w-52 bg-white rounded-xl shadow-xl border border-gray-100 py-1.5 z-50">
                            <a href="{{ route('expedientes.index') }}"
                               class="flex items-center gap-2.5 px-3 py-2 mx-1.5 rounded-lg text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-colors duration-100
                                      {{ request()->routeIs('expedientes.index') ? 'bg-purple-50 text-purple-700 font-medium' : '' }}">
                                <span class="flex items-center justify-center rounded-lg w-7 h-7 bg-emerald-100 text-emerald-600 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                </span>
                                Listado
                            </a>
                            <a href="{{ route('expedientes.carga.create') }}"
                               class="flex items-center gap-2.5 px-3 py-2 mx-1.5 rounded-lg text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-colors duration-100
                                      {{ request()->routeIs('expedientes.carga.*') ? 'bg-purple-50 text-purple-700 font-medium' : '' }}">
                                <span class="flex items-center justify-center rounded-lg w-7 h-7 bg-sky-100 text-sky-600 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                </span>
                                Cargar Documentos
                            </a>
                        </div>
                    </div>
                    @endif

                    {{-- Requisiciones --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150
                                       {{ request()->routeIs('requisiciones.*','catalogos.*') ? 'bg-white/20 text-white' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Requisiciones
                            <svg class="w-3 h-3 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-1"
                             class="absolute top-full left-0 mt-1.5 w-64 bg-white rounded-xl shadow-xl border border-gray-100 py-1.5 z-50">
                            <a href="{{ route('requisiciones.index') }}"
                               class="flex items-center gap-2.5 px-3 py-2 mx-1.5 rounded-lg text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-colors duration-100
                                      {{ request()->routeIs('requisiciones.index') ? 'bg-purple-50 text-purple-700 font-medium' : '' }}">
                                <span class="flex items-center justify-center text-purple-600 bg-purple-100 rounded-lg w-7 h-7 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                </span>
                                Mis Solicitudes
                            </a>
                            <a href="{{ route('requisiciones.create') }}"
                               class="flex items-center gap-2.5 px-3 py-2 mx-1.5 rounded-lg text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-colors duration-100
                                      {{ request()->routeIs('requisiciones.create') ? 'bg-purple-50 text-purple-700 font-medium' : '' }}">
                                <span class="flex items-center justify-center rounded-lg w-7 h-7 bg-fuchsia-100 text-fuchsia-600 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                </span>
                                Nueva Requisición
                            </a>

                            {{-- Catálogos (solo compras/admin) --}}
                            @if(Auth::user()->hasAnyRole(['administrador','compras']))
                            <div class="mx-3 my-2 border-t border-gray-100"></div>
                            <p class="px-4 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Catálogos</p>
                            <a href="{{ route('catalogos.unidades.index') }}"
                               class="flex items-center gap-2.5 px-3 py-2 mx-1.5 rounded-lg text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-colors duration-100
                                      {{ request()->routeIs('catalogos.unidades.*') ? 'bg-purple-50 text-purple-700 font-medium' : '' }}">
                                <span class="flex items-center justify-center text-teal-600 bg-teal-100 rounded-lg w-7 h-7 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                                </span>
                                Unidades de Medida
                            </a>
                            <a href="{{ route('catalogos.impuestos.index') }}"
                               class="flex items-center gap-2.5 px-3 py-2 mx-1.5 rounded-lg text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-colors duration-100
                                      {{ request()->routeIs('catalogos.impuestos.*') ? 'bg-purple-50 text-purple-700 font-medium' : '' }}">
                                <span class="flex items-center justify-center rounded-lg w-7 h-7 bg-amber-100 text-amber-600 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                                </span>
                                Tipos de Impuesto
                            </a>
                            <a href="{{ route('catalogos.retenciones.index') }}"
                               class="flex items-center gap-2.5 px-3 py-2 mx-1.5 rounded-lg text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-colors duration-100
                                      {{ request()->routeIs('catalogos.retenciones.*') ? 'bg-purple-50 text-purple-700 font-medium' : '' }}">
                                <span class="flex items-center justify-center rounded-lg w-7 h-7 bg-rose-100 text-rose-600 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/></svg>
                                </span>
                                Tipos de Retención
                            </a>
                            @endif
                        </div>
                    </div>

                    {{-- Ayuda --}}
                    <a href="{{ route('ayuda') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150
                              {{ request()->routeIs('ayuda') ? 'bg-white/20 text-white' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        Ayuda
                    </a>

                </div>
                @endauth
            </div>

            {{-- ── Derecha: usuario ─────────────────────────────────────────── --}}
            <div class="flex items-center gap-3">
                @auth
                <span class="hidden lg:inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest border"
                      style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6); border-color: rgba(255,255,255,0.12);">
                    {{ Auth::user()->getRoleNames()->first() ?? 'usuario' }}
                </span>

                {{-- Campana de notificaciones --}}
                {{-- Campana estática con badge --}}
                <a href="{{ route('notificaciones.index') }}"
                class="relative flex items-center justify-center transition-all w-9 h-9 rounded-xl text-white/60 hover:text-white hover:bg-white/10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @php $noLeidas = \App\Models\NotificacionInterna::where('user_id', auth()->id())->where('leida', false)->count(); @endphp
                    @if($noLeidas > 0)
                    <span class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-black text-white bg-rose-500 rounded-full">
                        {{ $noLeidas > 9 ? '9+' : $noLeidas }}
                    </span>
                    @endif
                </a>

                <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                    <button class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-xl border transition-all duration-150 hover:bg-white/10"
                            style="background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.12);">
                        <span class="flex items-center justify-center text-xs font-bold text-white rounded-lg w-7 h-7 shrink-0"
                              style="background: rgba(232,121,249,0.35);">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </span>
                        <span class="hidden sm:block text-sm font-medium text-white max-w-[130px] truncate">
                            {{ Auth::user()->name }}
                        </span>
                        <svg class="w-3.5 h-3.5 text-white/50 transition-transform duration-200 shrink-0" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1"
                         class="absolute top-full right-0 mt-1.5 w-52 bg-white rounded-xl shadow-xl border border-gray-100 py-1.5 z-50">
                        <div class="px-4 py-2 mb-1">
                            <p class="text-xs font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-[11px] text-gray-400 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <div class="mx-3 mb-1 border-t border-gray-100"></div>
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center gap-2.5 px-3 py-2 mx-1.5 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-100">
                            <span class="flex items-center justify-center text-gray-500 bg-gray-100 rounded-lg w-7 h-7 shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                            Mi Perfil
                        </a>
                        <div class="mx-3 my-1 border-t border-gray-100"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="flex items-center gap-2.5 w-full px-3 py-2 mx-1.5 rounded-lg text-sm text-red-600 hover:bg-red-50 transition-colors duration-100"
                                    style="width: calc(100% - 12px);">
                                <span class="flex items-center justify-center text-red-500 bg-red-100 rounded-lg w-7 h-7 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                </span>
                                Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>

                <button @click="open = !open"
                        class="flex items-center justify-center transition-all duration-150 rounded-lg md:hidden w-9 h-9 text-white/70 hover:text-white hover:bg-white/10">
                    <svg class="w-5 h-5" :class="open ? 'hidden' : 'block'" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg class="w-5 h-5" :class="open ? 'block' : 'hidden'" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                @else
                <a href="{{ route('login') }}"
                   class="hidden md:inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white border transition-all duration-150 hover:bg-white/10"
                   style="border-color: rgba(255,255,255,0.2);">
                    Iniciar Sesión
                </a>
                <button @click="open = !open"
                        class="flex items-center justify-center transition-all duration-150 rounded-lg md:hidden w-9 h-9 text-white/70 hover:text-white hover:bg-white/10">
                    <svg class="w-5 h-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                @endauth
            </div>
        </div>
    </div>

    {{-- ── Menú móvil ───────────────────────────────────────────────────────── --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         style="background-color: #3D0F55; border-top: 1px solid rgba(255,255,255,0.1);">
        @auth
        <div class="px-4 py-3 space-y-0.5 max-w-7xl mx-auto">

            <a href="{{ route('dashboard') }}"
               class="flex items-center w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-100
                      {{ request()->routeIs('dashboard') ? 'bg-white/15 text-white font-semibold' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                Inicio
            </a>

            @if(Auth::user()->hasAnyRole(['administrador','recursos_humanos']))
            <p class="pt-3 pb-1 px-3 text-[10px] font-bold uppercase tracking-widest text-white/35">Administración</p>
            @if(Auth::user()->hasRole('administrador'))
            <a href="{{ route('empleados.index') }}"
               class="flex items-center w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-100
                      {{ request()->routeIs('empleados.*') ? 'bg-white/15 text-white font-semibold' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                Gestionar Usuarios
            </a>
            <a href="{{ route('departamentos.gerentes.index') }}"
               class="flex items-center w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-100
                      {{ request()->routeIs('departamentos.*') ? 'bg-white/15 text-white font-semibold' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                Departamentos
            </a>
            @endif
            @endif

            @if(Auth::user()->hasAnyRole(['administrador','compras']))
            <p class="pt-3 pb-1 px-3 text-[10px] font-bold uppercase tracking-widest text-white/35">Expedientes</p>
            <a href="{{ route('expedientes.index') }}"
               class="flex items-center w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-100
                      {{ request()->routeIs('expedientes.index') ? 'bg-white/15 text-white font-semibold' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                Listado
            </a>
            <a href="{{ route('expedientes.carga.create') }}"
               class="flex items-center w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-100
                      {{ request()->routeIs('expedientes.carga.*') ? 'bg-white/15 text-white font-semibold' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                Cargar Documentos
            </a>
            @endif

            <p class="pt-3 pb-1 px-3 text-[10px] font-bold uppercase tracking-widest text-white/35">Requisiciones</p>
            <a href="{{ route('requisiciones.index') }}"
               class="flex items-center w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-100
                      {{ request()->routeIs('requisiciones.index') ? 'bg-white/15 text-white font-semibold' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                Mis Solicitudes
            </a>
            <a href="{{ route('requisiciones.create') }}"
               class="flex items-center w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-100
                      {{ request()->routeIs('requisiciones.create') ? 'bg-white/15 text-white font-semibold' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                Nueva Requisición
            </a>

            @if(Auth::user()->hasAnyRole(['administrador','compras']))
            <p class="pt-3 pb-1 px-3 text-[10px] font-bold uppercase tracking-widest text-white/35">Catálogos</p>
            <a href="{{ route('catalogos.unidades.index') }}"
               class="flex items-center w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-100
                      {{ request()->routeIs('catalogos.unidades.*') ? 'bg-white/15 text-white font-semibold' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                Unidades de Medida
            </a>
            <a href="{{ route('catalogos.impuestos.index') }}"
               class="flex items-center w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-100
                      {{ request()->routeIs('catalogos.impuestos.*') ? 'bg-white/15 text-white font-semibold' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                Tipos de Impuesto
            </a>
            <a href="{{ route('catalogos.retenciones.index') }}"
               class="flex items-center w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-100
                      {{ request()->routeIs('catalogos.retenciones.*') ? 'bg-white/15 text-white font-semibold' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                Tipos de Retención
            </a>
            @endif

            {{-- Ayuda móvil --}}
            <a href="{{ route('ayuda') }}"
               class="flex items-center w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-100
                      {{ request()->routeIs('ayuda') ? 'bg-white/15 text-white font-semibold' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Ayuda
            </a>

            <div class="pt-3 mt-1" style="border-top: 1px solid rgba(255,255,255,0.1);">
                <div class="px-3 py-2 mb-1">
                    <p class="text-sm font-semibold text-white">{{ Auth::user()->name }}</p>
                    <p class="text-xs" style="color: rgba(255,255,255,0.4);">{{ Auth::user()->email }}</p>
                </div>
                <a href="{{ route('profile.edit') }}"
                   class="flex items-center w-full px-3 py-2.5 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition-colors duration-100">
                    Mi Perfil
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-100"
                            style="color: #f87171;"
                            onmouseover="this.style.background='rgba(239,68,68,0.1)'"
                            onmouseout="this.style.background='transparent'">
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="px-4 py-3 mx-auto max-w-7xl">
            <a href="{{ route('login') }}"
               class="flex items-center w-full px-3 py-2.5 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/10 transition-colors duration-100">
                Iniciar Sesión
            </a>
        </div>
        @endauth
    </div>

</nav>