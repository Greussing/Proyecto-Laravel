<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Crear Producto
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">
                @if ($errors->any())
                    <div class="mb-4">
                        <ul class="text-red-500 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('productos.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="nombre" class="block text-gray-700 font-semibold">Nombre:</label>
                        <input type="text" name="nombre" id="nombre"
                               value="{{ old('nombre') }}"
                               class="w-full border-gray-300 rounded mt-1 @error('nombre') border-red-500 @enderror" required>
                        @error('nombre')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="cantidad" class="block text-gray-700 font-semibold">Cantidad:</label>
                        <input type="number" name="cantidad" id="cantidad"
                               value="{{ old('cantidad') }}"
                               class="w-full border-gray-300 rounded mt-1 @error('cantidad') border-red-500 @enderror" required>
                        @error('cantidad')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="precio" class="block text-gray-700 font-semibold">Precio:</label>
                        <input type="number" step="0.01" name="precio" id="precio"
                               value="{{ old('precio') }}"
                               class="w-full border-gray-300 rounded mt-1 @error('precio') border-red-500 @enderror" required>
                        @error('precio')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="categoria" class="block text-gray-700 font-semibold">Categoría:</label>
                        <input type="text" name="categoria" id="categoria"
                               value="{{ old('categoria') }}"
                               class="w-full border-gray-300 rounded mt-1 @error('categoria') border-red-500 @enderror">
                        @error('categoria')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="button" onclick="history.back()" class="text-gray-600 hover:underline mr-4">Cancelar</button>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>