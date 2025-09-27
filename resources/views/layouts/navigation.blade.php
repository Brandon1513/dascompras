<nav x-data="{ open: false }" class="bg-[#6A2C75] border-b border-gray-100">
  <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
    <div class="flex justify-between h-16">
      {{-- Izquierda: Logo + Menú --}}
      <div class="flex">
        {{-- Logo --}}
        <div class="flex items-center shrink-0">
          <a href="{{ route('dashboard') }}">
            <x-application-logo class="block w-auto text-white fill-current h-9" />
          </a>
        </div>

        {{-- Menú principal (solo logueados) --}}
        @if (Auth::check())
          <div class="hidden space-x-8 sm:flex sm:items-center sm:ms-6">
            {{-- Dashboard --}}
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-white hover:text-gray-300">
              {{ __('Dashboard') }}
            </x-nav-link>

            {{-- Administración (solo admin y RH ven contenedor; Empleados solo admin) --}}
            @if (Auth::user()->hasAnyRole(['administrador','recursos_humanos']))
              <div class="relative">
                <x-dropdown align="left">
                  <x-slot name="trigger">
                    <button class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-white transition duration-150 ease-in-out bg-transparent rounded-md hover:text-gray-300 focus:outline-none">
                      <div>{{ __('Administración') }}</div>
                      <div class="ms-1">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                      </div>
                    </button>
                  </x-slot>

                  <x-slot name="content">
                    @if (Auth::user()->hasRole('administrador'))
                      <x-dropdown-link :href="route('empleados.index')" :active="request()->routeIs('empleados.*')">
                        {{ __('Gestionar Usuarios') }}
                      </x-dropdown-link>
                    @endif
                  </x-slot>
                </x-dropdown>
              </div>
            @endif

            {{-- Expedientes (admin | compras) --}}
            @if (Auth::user()->hasAnyRole(['administrador','compras']))
              <div class="relative">
                <x-dropdown align="left">
                  <x-slot name="trigger">
                    <button class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-white transition duration-150 ease-in-out bg-transparent rounded-md hover:text-gray-300 focus:outline-none">
                      <div>{{ __('Expedientes') }}</div>
                      <div class="ms-1">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                      </div>
                    </button>
                  </x-slot>

                  <x-slot name="content">
                    <x-dropdown-link :href="route('expedientes.index')" :active="request()->routeIs('expedientes.index')">
                      {{ __('Listado') }}
                    </x-dropdown-link>
                    <x-dropdown-link :href="route('expedientes.carga.create')" :active="request()->routeIs('expedientes.carga.*')">
                      {{ __('Cargar documentos') }}
                    </x-dropdown-link>
                  </x-slot>
                </x-dropdown>
              </div>
            @endif

            {{-- Requisiciones (todos autenticados) --}}
            <div class="relative">
              <x-dropdown align="left">
                <x-slot name="trigger">
                  <button class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-white transition duration-150 ease-in-out bg-transparent rounded-md hover:text-gray-300 focus:outline-none">
                    <div>{{ __('Requisiciones') }}</div>
                    <div class="ms-1">
                      <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </div>
                  </button>
                </x-slot>

                <x-slot name="content">
                  <x-dropdown-link :href="route('requisiciones.index')" :active="request()->routeIs('requisiciones.index')">
                    {{ __('Listado') }}
                  </x-dropdown-link>
                  <x-dropdown-link :href="route('requisiciones.create')" :active="request()->routeIs('requisiciones.create')">
                    {{ __('Crear Requisición') }}
                  </x-dropdown-link>
                </x-slot>
              </x-dropdown>
            </div>
          </div>
        @endif
      </div>

      {{-- Derecha: Usuario / Login --}}
      @if (Auth::check())
        <div class="hidden sm:flex sm:items-center sm:ms-6">
          <x-dropdown align="right" width="48">
            <x-slot name="trigger">
              <button class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-white transition duration-150 ease-in-out bg-transparent rounded-md hover:text-gray-300 focus:outline-none">
                <div>{{ Auth::user()->name }}</div>
                <div class="ms-1">
                  <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </div>
              </button>
            </x-slot>

            <x-slot name="content">
              <x-dropdown-link :href="route('profile.edit')">
                {{ __('Perfil') }}
              </x-dropdown-link>

              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-dropdown-link :href="route('logout')"
                  onclick="event.preventDefault(); this.closest('form').submit();">
                  {{ __('Salir') }}
                </x-dropdown-link>
              </form>
            </x-slot>
          </x-dropdown>
        </div>
      @else
        <div class="hidden sm:flex sm:items-center sm:ms-6">
          <a href="{{ route('login') }}" class="text-sm text-white underline">Iniciar Sesión</a>
        </div>
      @endif

      {{-- Hamburguesa (móvil) --}}
      <div class="flex items-center -me-2 sm:hidden">
        <button @click="open = ! open" class="inline-flex items-center justify-center p-2 text-white rounded-md hover:text-gray-500 hover:bg-gray-100 focus:outline-none">
          <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  </div>

  {{-- Responsive --}}
  <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
    <div class="pt-2 pb-3 space-y-1">
      <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-white">
        {{ __('Dashboard') }}
      </x-responsive-nav-link>

      @role('administrador')
        <x-responsive-nav-link :href="route('empleados.index')" :active="request()->routeIs('empleados.*')" class="text-white">
          {{ __('Gestionar Usuarios') }}
        </x-responsive-nav-link>
      @endrole

      @hasanyrole('administrador|compras')
        <x-responsive-nav-link :href="route('expedientes.index')" :active="request()->routeIs('expedientes.index')" class="text-white">
          {{ __('Expedientes – Listado') }}
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('expedientes.carga.create')" :active="request()->routeIs('expedientes.carga.*')" class="text-white">
          {{ __('Expedientes – Cargar') }}
        </x-responsive-nav-link>
      @endhasanyrole

      <x-responsive-nav-link :href="route('requisiciones.index')" :active="request()->routeIs('requisiciones.index')" class="text-white">
        {{ __('Requisiciones – Listado') }}
      </x-responsive-nav-link>
      <x-responsive-nav-link :href="route('requisiciones.create')" :active="request()->routeIs('requisiciones.create')" class="text-white">
        {{ __('Requisiciones – Crear') }}
      </x-responsive-nav-link>
    </div>

    @if (Auth::check())
      <div class="pt-4 pb-1 border-t border-gray-200">
        <div class="px-4">
          <div class="text-base font-medium text-white">{{ Auth::user()->name }}</div>
          <div class="text-sm font-medium text-gray-300">{{ Auth::user()->email }}</div>
        </div>
        <div class="mt-3 space-y-1">
          <x-responsive-nav-link :href="route('profile.edit')" class="text-white">
            {{ __('Perfil') }}
          </x-responsive-nav-link>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-responsive-nav-link :href="route('logout')" class="text-white"
              onclick="event.preventDefault(); this.closest('form').submit();">
              {{ __('Salir') }}
            </x-responsive-nav-link>
          </form>
        </div>
      </div>
    @else
      <div class="pt-4 pb-1 border-t border-gray-200">
        <x-responsive-nav-link :href="route('login')" class="text-white">
          {{ __('Iniciar Sesión') }}
        </x-responsive-nav-link>
      </div>
    @endif
  </div>
</nav>
