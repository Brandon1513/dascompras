<x-app-layout>
    <div class="flex flex-col items-center justify-center h-screen bg-center bg-cover" style="background-image: url('{{ asset('images/background-pattern.png') }}');">
        <div class="max-w-xl px-4 text-center sm:px-6 lg:px-8 lg:max-w-7xl">
            <!-- Título Principal -->
            <h2 class="text-4xl font-extrabold leading-8 tracking-tight text-[#6A2C75] sm:text-6xl">
                Bienvenido a Dasavena Compras
            </h2>
            <!-- Descripción -->
            <p class="max-w-3xl mx-auto mt-4 text-xl text-gray-800">
                Encuentra información de apoyo, involúcrate en los procesos administrativos, sé parte de Dasavena.
                <span>Gracias por usar nuestra aplicación.</span>
            </p>

            <!-- Botón de inicio de sesión -->
            <div class="mt-6">
                <a href="{{ route('login') }}" class="inline-block px-6 py-3 text-base font-medium text-white bg-[#D4A018] border border-transparent rounded-md hover:bg-[#B38600]">
                    Iniciar Sesión
                </a>
            </div>
        </div>

        <!-- Logo en la esquina inferior derecha -->
        <img src="{{ asset('images/dasavena-logo.png') }}" alt="Dasavena Logo" class="absolute w-32 bottom-5 right-5 opacity-90">
    </div>
</x-app-layout>
