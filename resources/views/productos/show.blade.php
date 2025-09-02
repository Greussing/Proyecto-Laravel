<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Detalles del Producto
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">

                <!-- ID del producto (consecutivo) -->
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">ID del Producto:</h3>
                    <p class="text-gray-800">{{ $producto->id }}</p>
                </div>

                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Nombre:</h3>
                    <p class="text-gray-800">{{ $producto->nombre }}</p>
                </div>

                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Cantidad:</h3>
                    <p class="text-gray-800">{{ $producto->cantidad }}</p>
                </div>

                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Precio:</h3>
                    <p class="text-gray-800"> Gs. {{ number_format($producto->precio, 0, ',', '.') }}</p>
                </div>

                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Categoría:</h3>
                    <p class="text-gray-800">
                        {{ $producto->categoriaRelacion?->nombre ?? 'Sin categoría' }}
                    </p>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <a href="{{ route('productos.edit', $producto->id) }}"
                        class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                        Editar
                    </a>
                    <a href="{{ route('productos.index') }}"
                        class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                        Volver
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
