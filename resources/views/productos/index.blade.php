<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Listado de Productos
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Mensaje de éxito --}}
            @if (session('success'))
                <div class="mb-4 text-green-700 bg-green-100 p-4 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg p-6">

                {{-- Contenedor filtros + botón --}}
                <div class="flex items-center justify-between mb-4 flex-wrap gap-2 w-full">

                    {{-- Filtros --}}
                    <form method="GET" action="{{ route('productos.index') }}" class="flex flex-wrap gap-2">

                        {{-- Buscar por nombre --}}
                        <div class="relative">
                            <input type="text" name="search" placeholder="Buscar por nombre"
                                value="{{ request('search') }}" class="border rounded px-2 py-1 pr-14 w-56"
                                oninput="toggleSearchIcons(this)">

                            <div id="search-icons"
                                class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-2 {{ request('search') ? '' : 'hidden' }}">
                                {{-- Botón buscar (flecha) --}}
                                <button type="submit" class="text-gray-500 hover:text-blue-600 font-bold">
                                    →
                                </button>

                                {{-- Botón limpiar --}}
                                <a href="{{ route('productos.index', request()->except(['search', 'page'])) }}"
                                    class="text-red-500 hover:text-red-700 font-bold">
                                    ×
                                </a>
                            </div>
                        </div>

                        <script>
                            function toggleSearchIcons(input) {
                                let icons = document.getElementById('search-icons');
                                if (input.value.trim().length > 0) {
                                    icons.classList.remove('hidden');
                                } else {
                                    icons.classList.add('hidden');
                                }
                            }

                            document.addEventListener("DOMContentLoaded", function() {
                                let input = document.querySelector("input[name='search']");
                                if (input) toggleSearchIcons(input);
                            });
                        </script>

                        {{-- Categorías --}}
                        <div class="flex flex-wrap gap-2">
                            @php
                                $categoriaSeleccionada = request('categoria');
                                $categoriaActual = $categoriaSeleccionada
                                    ? $categorias->firstWhere('id', $categoriaSeleccionada)->nombre ?? 'Categorías'
                                    : 'Categorías';
                            @endphp

                            <details class="relative border rounded px-2 py-1">
                                <summary
                                    class="cursor-pointer select-none summary-arrow {{ request('categoria') ? 'text-blue-600 font-bold' : '' }}">
                                    {{ $categoriaActual }}
                                    @if (request('categoria'))
                                        <a href="{{ route('productos.index', request()->except(['categoria', 'page'])) }}"
                                            class="ml-2 text-red-500 font-bold hover:text-red-700">✕</a>
                                    @endif
                                </summary>
                                <div
                                    class="absolute bg-white border rounded shadow-md mt-1 w-56 z-10 max-h-60 overflow-y-auto">
                                    <ul>
                                        @foreach ($categorias as $cat)
                                            @if (request('categoria') != $cat->id)
                                                <li>
                                                    <a href="{{ route('productos.index', array_merge(request()->except(['categoria', 'page']), ['categoria' => $cat->id])) }}"
                                                        class="block px-3 py-2 hover:bg-gray-100">
                                                        {{ $cat->nombre }}
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </details>

                            {{-- Filtro por precio --}}
                            <details class="relative border rounded px-2 py-1">
                                <summary
                                    class="cursor-pointer select-none summary-arrow {{ request('precio_min') || request('precio_max') ? 'text-blue-600 font-bold' : '' }}">
                                    @if (request('precio_min') || request('precio_max'))
                                        Precio:
                                        {{ request('precio_min')
                                            ? 'Gs. ' . number_format((float) str_replace(['.', ','], '', request('precio_min')), 0, ',', '.')
                                            : '0' }}
                                        –
                                        {{ request('precio_max')
                                            ? 'Gs. ' . number_format((float) str_replace(['.', ','], '', request('precio_max')), 0, ',', '.')
                                            : '∞' }}
                                        <a href="{{ route('productos.index', request()->except(['precio_min', 'precio_max', 'page'])) }}"
                                            class="ml-2 text-red-500 font-bold hover:text-red-700">✕</a>
                                    @else
                                        Precio
                                    @endif
                                </summary>
                                <div class="absolute bg-white border rounded shadow-md mt-1 z-10 p-2 w-56">
                                    <form method="GET" action="{{ route('productos.index') }}"
                                        class="flex flex-wrap gap-2">

                                        {{-- Mantener filtros --}}
                                        @foreach (request()->except(['precio_min', 'precio_max', 'page']) as $key => $value)
                                            @if (is_array($value))
                                                @foreach ($value as $v)
                                                    <input type="hidden" name="{{ $key }}[]"
                                                        value="{{ $v }}">
                                                @endforeach
                                            @else
                                                <input type="hidden" name="{{ $key }}"
                                                    value="{{ $value }}">
                                            @endif
                                        @endforeach

                                        {{-- Precio mínimo --}}
                                        <label class="block text-sm text-gray-700">Mínimo</label>
                                        <div class="flex items-center border rounded px-2 py-1 w-full">
                                            <span class="text-gray-600 mr-1">Gs.</span>
                                            <input type="text" name="precio_min" id="precio_min"
                                                value="{{ request('precio_min') ? number_format(request('precio_min'), 0, ',', '.') : '' }}"
                                                placeholder="Ej: 1.000"
                                                class="flex-1 text-sm border-0 focus:ring-0 p-0 outline-none">
                                        </div>

                                        {{-- Precio máximo --}}
                                        <label class="block text-sm text-gray-700">Máximo</label>
                                        <div class="flex items-center border rounded px-2 py-1 w-full">
                                            <span class="text-gray-600 mr-1">Gs.</span>
                                            <input type="text" name="precio_max" id="precio_max"
                                                value="{{ request('precio_max') ? number_format(request('precio_max'), 0, ',', '.') : '' }}"
                                                placeholder="Ej: 5.000"
                                                class="flex-1 text-sm border-0 focus:ring-0 p-0 outline-none">
                                        </div>

                                        <button type="submit"
                                            class="mt-2 bg-blue-600 text-white py-1 rounded hover:bg-blue-700 text-sm w-full">
                                            Aplicar
                                        </button>
                                    </form>
                                </div>
                            </details>

                            {{-- Ordenar por --}}
                            @php
                                $opciones = [
                                    'nombre_asc' => 'Nombre (A-Z)',
                                    'nombre_desc' => 'Nombre (Z-A)',
                                    'precio_asc' => 'Precio (menor a mayor)',
                                    'precio_desc' => 'Precio (mayor a menor)',
                                    'stock_asc' => 'Stock (menor a mayor)',
                                    'stock_desc' => 'Stock (mayor a menor)',
                                ];
                                $ordenSeleccionado = request('ordenar');
                                $ordenActual = $ordenSeleccionado
                                    ? $opciones[$ordenSeleccionado] ?? 'Ordenar por'
                                    : 'Ordenar por';
                            @endphp

                            <details class="relative border rounded px-2 py-1">
                                <summary
                                    class="cursor-pointer select-none summary-arrow {{ request('ordenar') ? 'text-blue-600 font-bold' : '' }}">
                                    {{ $ordenActual }}
                                    @if (request('ordenar'))
                                        <a href="{{ route('productos.index', request()->except(['ordenar', 'page'])) }}"
                                            class="ml-2 text-red-500 font-bold hover:text-red-700">✕</a>
                                    @endif
                                </summary>
                                <div class="absolute bg-white border rounded shadow-md mt-1 w-56 z-10">
                                    <ul>
                                        @foreach ($opciones as $valor => $texto)
                                            @if (!$ordenSeleccionado || $valor !== $ordenSeleccionado)
                                                <li>
                                                    <a href="{{ route('productos.index', array_merge(request()->except(['ordenar', 'page']), ['ordenar' => $valor])) }}"
                                                        class="block px-3 py-2 hover:bg-gray-100">
                                                        {{ $texto }}
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </details>

                            {{-- Filtro por stock --}}
                            <details class="relative border rounded px-2 py-1">
                                <summary
                                    class="cursor-pointer select-none summary-arrow {{ request('stock') ? 'text-blue-600 font-bold' : '' }}">
                                    @if (request('stock'))
                                        Stock:
                                        @php $stocks = (array) request('stock'); @endphp
                                        {{ in_array('disponibles', $stocks) ? 'Disponibles' : '' }}
                                        {{ in_array('agotados', $stocks) ? (in_array('disponibles', $stocks) ? ', Agotados' : 'Agotados') : '' }}

                                        <a href="{{ route('productos.index', request()->except(['stock', 'page'])) }}"
                                            class="ml-2 text-red-500 font-bold hover:text-red-700">✕</a>
                                    @else
                                        Stock
                                    @endif
                                </summary>

                                <div class="absolute bg-white border rounded shadow-md mt-1 z-10 p-2 w-56">
                                    <form method="GET" action="{{ route('productos.index') }}"
                                        class="flex flex-wrap gap-2">
                                        {{-- Mantener filtros activos --}}
                                        @foreach (request()->except(['stock', 'page']) as $key => $value)
                                            @if (is_array($value))
                                                @foreach ($value as $v)
                                                    <input type="hidden" name="{{ $key }}[]"
                                                        value="{{ $v }}">
                                                @endforeach
                                            @else
                                                <input type="hidden" name="{{ $key }}"
                                                    value="{{ $value }}">
                                            @endif
                                        @endforeach

                                        <label class="flex items-center">
                                            <input type="checkbox" id="chk-disponibles" name="stock[]"
                                                value="disponibles"
                                                {{ in_array('disponibles', (array) request('stock')) ? 'checked' : '' }}>
                                            <span class="ml-2">Disponibles</span>
                                        </label>

                                        <label class="flex items-center">
                                            <input type="checkbox" id="chk-agotados" name="stock[]" value="agotados"
                                                {{ in_array('agotados', (array) request('stock')) ? 'checked' : '' }}>
                                            <span class="ml-2">Agotados</span>
                                        </label>
                                    </form>
                                </div>
                            </details>

                            <script>
                                document.addEventListener('DOMContentLoaded', () => {
                                    const disponibles = document.getElementById('chk-disponibles');
                                    const agotados = document.getElementById('chk-agotados');
                                    const form = disponibles.closest('form');

                                    if (disponibles && agotados) {
                                        disponibles.addEventListener('change', () => {
                                            if (disponibles.checked) agotados.checked = false;
                                            form.submit();
                                        });

                                        agotados.addEventListener('change', () => {
                                            if (agotados.checked) disponibles.checked = false;
                                            form.submit();
                                        });
                                    }
                                });
                            </script>
                        </div>
                    </form>

                    {{-- Botón crear producto (arriba a la derecha) --}}
                    <a href="{{ route('productos.create') }}"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Crear producto
                    </a>
                </div>

                {{-- Script para formatear números --}}
                <script>
                    function formatNumber(value) {
                        if (!value) return '';
                        return value.toString()
                            .replace(/\D/g, '')
                            .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    }

                    function applyFormat(input) {
                        input.value = formatNumber(input.value);
                        input.addEventListener('input', function() {
                            let cursorPos = this.selectionStart;
                            let originalLength = this.value.length;

                            this.value = formatNumber(this.value);

                            let newLength = this.value.length;
                            this.selectionEnd = cursorPos + (newLength - originalLength);
                        });
                    }

                    document.addEventListener('DOMContentLoaded', function() {
                        const precioMin = document.getElementById('precio_min');
                        const precioMax = document.getElementById('precio_max');

                        if (precioMin) applyFormat(precioMin);
                        if (precioMax) applyFormat(precioMax);
                    });
                </script>

                {{-- Tabla de productos --}}
                @if ($productos->isEmpty())
                    <p class="text-gray-600">No hay productos registrados.</p>
                @else
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 border">Id</th>
                                <th class="px-4 py-2 border">Nombre</th>
                                <th class="px-4 py-2 border">Cantidad</th>
                                <th class="px-4 py-2 border">Precio</th>
                                <th class="px-4 py-2 border">Categoría</th>
                                <th class="px-4 py-2 border">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productos as $producto)
                                <tr class="{{ $producto->cantidad < 5 ? 'bg-red-100' : '' }}">

                                    {{-- Id --}}
                                    <td class="px-4 py-2 border">
                                        {{ $producto->id }}
                                    </td>

                                    {{-- Nombre --}}
                                    <td class="px-4 py-2 border">
                                        {{ $producto->nombre }}
                                    </td>


                                    {{-- Cantidad --}}
                                    <td class="px-4 py-2">
                                        @if ($producto->cantidad == 0)
                                            <span class="text-red-600 font-bold">Agotado</span>
                                        @elseif ($producto->cantidad <= 0)
                                            <span class="text-red-600 font-bold">{{ $producto->cantidad }}</span>
                                        @elseif ($producto->cantidad <= 10)
                                            <span class="text-yellow-600 font-bold">{{ $producto->cantidad }}</span>
                                        @else
                                            <span class="text-gray-800">{{ $producto->cantidad }}</span>
                                        @endif
                                    </td>

                                    {{-- Precio --}}
                                    <td class="px-4 py-2 border">
                                        Gs. {{ number_format($producto->precio, 0, ',', '.') }}
                                    </td>

                                    {{-- Categoría --}}
                                    <td class="px-4 py-2 border">
                                        @php
                                            $colores = [
                                                'Electrónica' => 'text-indigo-700 font-bold',
                                                'Alimentos' => 'text-green-700 font-bold',
                                                'Ropa' => 'text-orange-700 font-bold',
                                                'Accesorios' => 'text-purple-700 font-bold',
                                                'Herramientas' => 'text-teal-700 font-bold',
                                            ];

                                            $nombreCategoria = $producto->categoriaRelacion
                                                ? $producto->categoriaRelacion->nombre
                                                : 'Sin Categoría';
                                            $color = $colores[$nombreCategoria] ?? 'text-gray-700 font-bold';
                                        @endphp

                                        <span class="{{ $color }}">
                                            {{ $nombreCategoria }}
                                        </span>
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="px-4 py-2 border flex gap-2" x-data="{ open: false }">
                                        <a href="{{ route('productos.edit', ['producto' => $producto->id, 'page' => request('page'), 'search' => request('search'), 'categoria' => request('categoria'), 'precio_min' => request('precio_min'), 'precio_max' => request('precio_max'), 'stock_bajo' => request('stock_bajo'), 'ordenar' => request('ordenar')]) }}"
                                            class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                            Editar
                                        </a>

                                        <a href="{{ route('productos.show', $producto->id) }}"
                                            class="bg-gray-600 text-white px-3 py-1 rounded hover:bg-gray-700">
                                            Detalles
                                        </a>

                                        <!-- Botón para abrir modal -->
                                        <button @click="open = true"
                                            class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                            Eliminar
                                        </button>

                                        <!-- Modal de confirmación -->
                                        <div x-show="open" x-cloak
                                            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-96">
                                                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                                    ⚠️ Confirmar eliminación
                                                </h2>
                                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                                    ¿Seguro que quieres eliminar este producto? Esta acción no se puede
                                                    deshacer.
                                                </p>

                                                <div class="mt-4 flex justify-end gap-3">
                                                    <button @click="open = false"
                                                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                                                        ❌ Cancelar
                                                    </button>

                                                    <form action="{{ route('productos.destroy', $producto->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                                            ✔️ Confirmar
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="flex items-center justify-between mt-1">

                        {{-- Texto de resultados --}}
                        @if ($productos->total() > 0)
                            <div class="text-black">
                                Mostrando
                                <span class="font-bold">{{ $productos->firstItem() }}</span>
                                a
                                <span class="font-bold">{{ $productos->lastItem() }}</span>
                                de
                                <span class="font-bold">{{ $productos->total() }}</span>
                                resultados
                            </div>
                        @else
                            <div class="text-black">
                                No se encontraron resultados
                            </div>
                        @endif


                        {{-- Paginación --}}
                        <div class="mt-1">
                            {{ $productos->links() }}
                        </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
