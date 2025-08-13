<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Listado de Productos
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 text-green-700 bg-green-100 p-4 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-end mb-4">
                    <a href="{{ route('productos.create') }}"
                       class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Crear Producto
                    </a>
                </div>

                @if($productos->isEmpty())
                    <p class="text-gray-600">No hay productos registrados.</p>
                @else
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 border">ID</th>
                                <th class="px-4 py-2 border">Nombre</th>
                                <th class="px-4 py-2 border">Cantidad</th>
                                <th class="px-4 py-2 border">Precio</th>
                                <th class="px-4 py-2 border">Categoría</th>
                                <th class="px-4 py-2 border">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productos as $producto)
                                <tr>
                                    <td class="px-4 py-2 border">{{ $producto->id }}</td>
                                    <td class="px-4 py-2 border">{{ $producto->nombre }}</td>
                                    <td class="px-4 py-2 border">{{ $producto->cantidad }}</td>
                                    <td class="px-4 py-2 border">{{ number_format($producto->precio, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2 border">{{ $producto->categoria }}</td>
                                    <td class="px-4 py-2 border flex gap-2">
                                        <a href="{{ route('productos.edit', $producto->id) }}"
                                           class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                                            Editar
                                        </a>
                                        <form action="{{ route('productos.destroy', $producto->id) }}" method="POST"
                                              onsubmit="return confirm('⚠️ ¿Seguro que quieres eliminar este producto? Esta acción no se puede deshacer.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>