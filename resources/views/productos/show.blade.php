<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Detalles del Producto
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">
                <p><strong>ID:</strong> {{ $producto->id }}</p>
                <p><strong>Nombre:</strong> {{ $producto->nombre }}</p>
                <p><strong>Cantidad:</strong> {{ $producto->cantidad }}</p>
                <p><strong>Precio:</strong> {{ number_format($producto->precio, 2, ',', '.') }}</p>
                <p><strong>Categoría:</strong> {{ $producto->categoria ?? 'N/A' }}</p>

                <div class="mt-6 flex gap-4">
                    <a href="{{ route('productos.index') }}"
                       class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                        Volver
                    </a>
                    <a href="{{ route('productos.edit', $producto->id) }}"
                       class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                        Editar
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>