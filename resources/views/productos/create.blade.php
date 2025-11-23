<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Crear Producto') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                {{-- Mensajes de error globales --}}
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/50 border-l-4 border-red-500 text-red-700 dark:text-red-400">
                        <p class="font-bold">¡Atención!</p>
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Formulario de creación --}}
                <form action="{{ route('productos.store') }}" method="POST">
                    @csrf
                    @include('productos._form', ['categorias' => $categorias])
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>