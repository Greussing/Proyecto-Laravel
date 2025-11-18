<x-app-layout>
    {{-- Encabezado de la página --}}
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-white">
            Listado de Productos
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Mensaje de éxito → se muestra si hay session('success') (ej: al crear/editar producto) --}}
            @if (session('success'))
                <div class="mb-4 text-green-700 bg-green-100 p-4 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg p-6">

                {{-- Contenedor filtros + botón crear producto --}}
                <div class="flex items-center justify-between mb-4 flex-wrap gap-2 w-full">

                    {{-- Filtros de búsqueda → GET hacia productos.index (ProductoController@index) --}}
                    <form method="GET" action="{{ route('productos.index') }}" class="flex flex-wrap gap-2">

                        {{-- Buscar por nombre --}}
                        <div class="relative">
                            <!-- Botón lupa (submit) -->
                            <button type="submit"
                                class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
                                </svg>
                            </button>

                            <!-- Input búsqueda -->
                            <input type="text" name="search" placeholder="Buscar por Nombre"
                                value="{{ request('search') }}"
                                class="border rounded pl-9 pr-14 py-1 w-60 md:w-72 focus:ring-2 focus:ring-indigo-500 outline-none"
                                oninput="toggleSearchIcons(this)">
                            {{-- Script búsqueda dinámica (AJAX) --}}
                            <script>
                                const busquedaUrl = "{{ route('productos.busqueda') }}";
                            </script>

                            <script>
                                document.addEventListener('DOMContentLoaded', () => {
                                    const input = document.querySelector('input[name="search"]');
                                    const tabla = document.querySelector('table tbody');
                                    let timeout = null;

                                    input.addEventListener('input', function() {
                                        clearTimeout(timeout);
                                        const valor = this.value.trim();

                                        timeout = setTimeout(() => {
                                            // 🟢 Si se borra todo → recargar página completa (restaura tabla original)
                                            if (valor === '') {
                                                window.location.reload();
                                                return;
                                            }

                                            // 🔵 Mostrar texto "Buscando..."
                                            tabla.innerHTML = `
                    <tr><td colspan="6" class="text-center py-4 text-gray-400">
                        Buscando...
                    </td></tr>`;

                                            // 🔍 Petición AJAX con el valor del input
                                            fetch(busquedaUrl + "?search=" + encodeURIComponent(valor))
                                                .then(res => res.json())
                                                .then(data => {
                                                    tabla.innerHTML = '';

                                                    if (data.length === 0) {
                                                        tabla.innerHTML = `
                                <tr><td colspan="6" class="text-center py-4 text-gray-500">
                                    No se encontraron productos
                                </td></tr>`;
                                                        return;
                                                    }

                                                    data.forEach((p, i) => {
                                                        tabla.innerHTML += `
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2">${i + 1}</td>
                                    <td class="px-4 py-2">${p.nombre}</td>
                                    <td class="px-4 py-2">${p.categoria_relacion ? p.categoria_relacion.nombre : '-'}</td>
                                    <td class="px-4 py-2">${p.cantidad}</td>
                                    <td class="px-4 py-2">Gs. ${Number(p.precio).toLocaleString()}</td>
                                </tr>`;
                                                    });
                                                })
                                                .catch(err => console.error('Error en búsqueda:', err));
                                        }, 300);
                                    });
                                });
                            </script>

                            <!-- Botón limpiar (×) → elimina el parámetro "search" conservando otros filtros -->
                            <div id="search-icons"
                                class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-2 {{ request('search') ? '' : 'hidden' }}">
                                <a href="{{ route('productos.index', request()->except(['search', 'page'])) }}"
                                    class="text-red-500 hover:text-red-700 font-bold">×</a>
                            </div>
                        </div>

                        {{-- Script mostrar/ocultar icono limpiar --}}
                        <script>
                            function toggleSearchIcons(input) {
                                const icons = document.getElementById('search-icons');
                                if (input.value.trim() !== '') {
                                    icons.classList.remove('hidden');
                                } else {
                                    icons.classList.add('hidden');
                                }
                            }
                        </script>

                        {{-- Filtro por Categorías --}}
                        <details class="relative border rounded px-2 py-1">
                            <summary
                                class="cursor-pointer select-none summary-arrow {{ request('categorias') ? 'text-blue-600 font-bold' : '' }}">
                                {{-- Si hay categorías seleccionadas, mostrarlas --}}
                                @if (request('categorias'))
                                    Categorías:
                                    @php
                                        $catsSeleccionadas = (array) request('categorias');
                                        $nombresSeleccionados = $categorias
                                            ->whereIn('id', $catsSeleccionadas)
                                            ->pluck('nombre')
                                            ->toArray();
                                    @endphp
                                    {{ implode(', ', $nombresSeleccionados) }}
                                    {{-- Botón limpiar categorías --}}
                                    <a href="{{ route('productos.index', request()->except(['categorias', 'page'])) }}"
                                        class="ml-2 text-red-500 font-bold hover:text-red-700">✕</a>
                                @else
                                    Categorías
                                @endif
                            </summary>
                            {{-- Listado de checkboxes de categorías --}}
                            <div
                                class="absolute bg-white border rounded shadow-md mt-1 z-10 p-2 w-56 max-h-60 overflow-y-auto">
                                @foreach ($categorias as $cat)
                                    <label class="flex items-center">
                                        <input type="checkbox" name="categorias[]" value="{{ $cat->id }}"
                                            {{ in_array($cat->id, (array) request('categorias')) ? 'checked' : '' }}
                                            onchange="this.form.submit()">
                                        <span class="ml-2">{{ $cat->nombre }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </details>

                        {{-- Filtro por Stock (disponibles/agotados) --}}
                        <details class="relative border rounded px-2 py-1">
                            <summary
                                class="cursor-pointer select-none summary-arrow {{ request('stock') ? 'text-blue-600 font-bold' : '' }}">
                                @if (request('stock'))
                                    Stock:
                                    @php $stocks = (array) request('stock'); @endphp
                                    {{ in_array('disponibles', $stocks) ? 'Disponibles' : '' }}
                                    {{ in_array('agotados', $stocks) ? (in_array('disponibles', $stocks) ? ', Agotados' : 'Agotados') : '' }}
                                    {{-- Botón limpiar stock --}}
                                    <a href="{{ route('productos.index', request()->except(['stock', 'page'])) }}"
                                        class="ml-2 text-red-500 font-bold hover:text-red-700">✕</a>
                                @else
                                    Stock
                                @endif
                            </summary>
                            {{-- Opciones de stock --}}
                            <div class="absolute bg-white border rounded shadow-md mt-1 z-10 p-2 w-56">
                                <label class="flex items-center">
                                    <input type="checkbox" id="chk-disponibles" name="stock[]" value="disponibles"
                                        {{ in_array('disponibles', (array) request('stock')) ? 'checked' : '' }}>
                                    <span class="ml-2">Disponibles</span>
                                </label>

                                <label class="flex items-center">
                                    <input type="checkbox" id="chk-agotados" name="stock[]" value="agotados"
                                        {{ in_array('agotados', (array) request('stock')) ? 'checked' : '' }}>
                                    <span class="ml-2">Agotados</span>
                                </label>
                            </div>
                        </details>
                        {{-- Script para stock (mutuamente excluyentes) --}}
                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                const disponibles = document.getElementById('chk-disponibles');
                                const agotados = document.getElementById('chk-agotados');
                                const form = disponibles.closest('form');

                                if (disponibles && agotados) {
                                    disponibles.addEventListener('change', () => { // Si se marca "disponibles", desmarcar "agotados"
                                        if (disponibles.checked) agotados.checked = false;
                                        form.submit();
                                    });

                                    agotados.addEventListener('change', () => { // Si se marca "agotados", desmarcar "disponibles"
                                        if (agotados.checked) disponibles.checked = false;
                                        form.submit();
                                    });
                                }
                            });
                        </script>

                        {{-- Filtro por Precio (mín y máx) --}}
                        <details class="relative border rounded px-2 py-1">
                            <summary
                                class="cursor-pointer select-none summary-arrow {{ request('precio_min') || request('precio_max') ? 'text-blue-600 font-bold' : '' }}">
                                @if (request('precio_min') || request('precio_max'))
                                    Precio:
                                    {{ request('precio_min')
                                        ? 'Gs. ' . number_format((int) str_replace(['.', ','], '', request('precio_min')), 0, ',', '.')
                                        : '0' }}
                                    –
                                    {{ request('precio_max')
                                        ? 'Gs. ' . number_format((int) str_replace(['.', ','], '', request('precio_max')), 0, ',', '.')
                                        : '∞' }}
                                    <a href="{{ route('productos.index', request()->except(['precio_min', 'precio_max', 'page'])) }}"
                                        class="ml-2 text-red-500 font-bold hover:text-red-700">✕</a>
                                @else
                                    Precio
                                @endif
                            </summary>
                            {{-- Inputs de precio --}}
                            <div class="absolute bg-white border rounded shadow-md mt-1 z-10 p-2 w-56">
                                <label class="block text-sm text-gray-700">Mínimo</label>
                                <div class="flex items-center border rounded px-2 py-1 w-full">
                                    <span class="text-gray-600 mr-1">Gs.</span>
                                    <input type="text" name="precio_min" id="precio_min"
                                        value="{{ request('precio_min') ? number_format((int) str_replace(['.', ','], '', request('precio_min')), 0, ',', '.') : '' }}"
                                        placeholder="Ej: 1.000"
                                        class="flex-1 text-sm border-0 focus:ring-0 p-0 outline-none">
                                </div>

                                <label class="block text-sm text-gray-700">Máximo</label>
                                <div class="flex items-center border rounded px-2 py-1 w-full">
                                    <span class="text-gray-600 mr-1">Gs.</span>
                                    <input type="text" name="precio_max" id="precio_max"
                                        value="{{ request('precio_max') ? number_format((int) str_replace(['.', ','], '', request('precio_max')), 0, ',', '.') : '' }}"
                                        placeholder="Ej: 5.000"
                                        class="flex-1 text-sm border-0 focus:ring-0 p-0 outline-none">
                                </div>

                                <button type="submit"
                                    class="mt-2 bg-blue-600 text-white py-1 rounded hover:bg-blue-700 text-sm w-full">
                                    Aplicar
                                </button>
                            </div>
                        </details>

                        {{-- Ordenar por --}}
                        @php
                            // Opciones disponibles de ordenamiento
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
                                {{-- Botón limpiar orden --}}
                                @if (request('ordenar'))
                                    <a href="{{ route('productos.index', request()->except(['ordenar', 'page'])) }}"
                                        class="ml-2 text-red-500 font-bold hover:text-red-700">✕</a>
                                @endif
                            </summary>
                            {{-- Listado de opciones --}}
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
                    </form>

                    {{-- Control de caducidad --}}
<details class="relative border rounded px-2 py-1">
                        <summary
                            class="cursor-pointer select-none summary-arrow {{ request()->is('caducidad*') ? 'text-blue-600 font-bold' : '' }}">
                            Control de Caducidad
                            {{-- Si estás en la ruta de , muestra botón para limpiar --}}
                            @if (request()->is('caducidad*'))
                                <a href="{{ route('productos.index') }}"
                                    class="ml-2 text-red-500 font-bold hover:text-red-700">✕</a>
                            @endif
                        </summary>

                        {{-- Menú desplegable --}}
                        <div class="absolute bg-white border rounded shadow-md mt-1 z-10 p-2 w-56">
                            <ul class="space-y-1">
                                <li>
                                    <a href="{{ route('caducidad.index') }}"
                                        class="block px-2 py-1 hover:bg-gray-100 text-gray-700">
                                        📋 Ver control de vencimientos 
                                    </a>
                                </li>
                <li>
                                    <a href="{{ route('caducidad.export.pdf') }}"
                                        class="block px-2 py-1 hover:bg-gray-100 text-gray-700">
                                        🧾 Descargar en PDF
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('caducidad.export.excel') }}"
                                        class="block px-2 py-1 hover:bg-gray-100 text-gray-700">
                                        📊 Exportar a Excel
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </details>

                    {{-- Historial de movimientos --}}
                    {{-- Botón Historial de Movimientos --}}
                    <details class="relative border rounded px-2 py-1">
                        <summary
                            class="cursor-pointer select-none summary-arrow {{ request()->is('historial*') ? 'text-blue-600 font-bold' : '' }}">
                            Historial de Movimientos
                            {{-- Si estás en la ruta de historial, muestra botón para limpiar --}}
                            @if (request()->is('historial*'))
                                <a href="{{ route('productos.index') }}"
                                    class="ml-2 text-red-500 font-bold hover:text-red-700">✕</a>
                            @endif
                        </summary>

                        {{-- Menú desplegable --}}
                        <div class="absolute bg-white border rounded shadow-md mt-1 z-10 p-2 w-56">
                            <ul class="space-y-1">
                                <li>
                                    <a href="{{ route('historial.index') }}"
                                        class="block px-2 py-1 hover:bg-gray-100 text-gray-700">
                                        📋 Ver historial completo
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('historial.export.pdf') }}"
                                        class="block px-2 py-1 hover:bg-gray-100 text-gray-700">
                                        🧾 Descargar en PDF
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('historial.export.excel') }}"
                                        class="block px-2 py-1 hover:bg-gray-100 text-gray-700">
                                        📊 Exportar a Excel
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </details>
                    <!-- Botón Crear Producto → conecta con productos.create (ProductoController@create + vista create.blade.php) -->
                    <a href="{{ route('productos.create') }}" class="text-gray-500 hover:text-green-600 transition"
                        title="Crear producto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    </a>
                </div>

                {{-- Script para formatear números (inputs de precio con separador de miles) --}}
                <script>
                    function formatNumber(value) {
                        if (!value) return '';
                        return value.toString()
                            .replace(/\D/g, '') // eliminar caracteres no numéricos
                            .replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // agregar puntos cada 3 dígitos
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

                {{-- Si no hay productos --}}
                @if ($productos->isEmpty())
                    <p class="p-4 text-center text-gray-500">
                        No hay productos registrados.
                    </p>
                @else
                    {{-- Tabla principal --}}
                    <table class="min-w-full border border-gray-200 rounded">
                        <thead class="bg-gray-100">
                            <tr>
                                {{-- Encabezados de la tabla --}}
                                <th class="px-4 py-2 border text-center">ID</th>
                                <th class="px-4 py-2 border">Nombre</th>
                                <th class="px-4 py-2 border">Categoría</th>
                                <th class="px-4 py-2 border text-center">Cantidad</th>
                                <th class="px-4 py-2 border text-right">Precio</th>
                                <th class="px-4 py-2 border text-center">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            {{-- Recorremos todos los productos --}}
                            @foreach ($productos as $producto)
                                <tr class="hover:bg-gray-50" x-data="{ open: false }">

                                    {{-- Id --}}
                                    <td class="px-4 py-2 border text-gray-700 font-medium text-center">
                                        {{ $producto->id }}
                                    </td>

                                    {{-- Nombre --}}
                                    <td class="px-4 py-2 border">
                                        {{ $producto->nombre }}
                                    </td>

                                    {{-- Categoría --}}
                                    <td class="px-4 py-2 border">
                                        @php
                                            $colores = [
                                                'Electrónica' => 'bg-indigo-100 text-indigo-700',
                                                'Alimentos' => 'bg-green-100 text-green-700',
                                                'Ropa' => 'bg-orange-100 text-orange-700',
                                                'Accesorios' => 'bg-purple-100 text-purple-700',
                                                'Herramientas' => 'bg-teal-100 text-teal-700',
                                            ];

                                            $nombreCategoria = $producto->categoriaRelacion->nombre ?? 'Sin Categoría';

                                            $color = $colores[$nombreCategoria] ?? 'bg-gray-100 text-gray-700';
                                        @endphp

                                        <span class="px-2 py-1 rounded text-sm font-medium {{ $color }}">
                                            {{ $nombreCategoria }}
                                        </span>
                                    </td>

                                    {{-- Cantidad --}}
                                    <td class="px-4 py-2 border text-center font-semibold text-gray-800">
                                        @php
                                            if ($producto->cantidad == 0) {
                                                $texto = 'Agotado';
                                                $clase = 'bg-red-100 text-red-700';
                                            } elseif ($producto->cantidad <= 5) {
                                                $texto = $producto->cantidad;
                                                $clase = 'bg-red-100 text-red-700';
                                            } elseif ($producto->cantidad <= 10) {
                                                $texto = $producto->cantidad;
                                                $clase = 'bg-yellow-100 text-yellow-700';
                                            } else {
                                                $texto = $producto->cantidad;
                                                $clase = 'bg-green-100 text-green-700';
                                            }
                                        @endphp

                                        <span class="px-2 py-1 rounded text-sm font-medium {{ $clase }}">
                                            {{ $texto }}
                                        </span>
                                    </td>

                                    {{-- Precio --}}
                                    <td class="px-4 py-2 border text-right font-bold text-blue-700">
                                        Gs. {{ number_format($producto->precio, 0, ',', '.') }}
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="px-4 py-2 border text-center">
                                        <div class="flex flex-col items-center gap-2">

                                            {{-- Editar --}}
                                            <a href="{{ route('productos.edit', $producto->id) }}"
                                                class="text-gray-500 hover:text-blue-600 transition" title="Editar">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.862 4.487l1.651 1.651a2 2 0 010 2.828l-8.486 8.486a2 2 0 01-.878.505l-3.722.931a.5.5 0 01-.606-.606l.93-3.722a2 2 0 01.506-.878l8.485-8.486a2 2 0 012.828 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19 20H5" />
                                                </svg>
                                            </a>

                                            {{-- Eliminar --}}
                                            <button @click="open = true"
                                                class="text-red-600 hover:text-red-800 transition" title="Eliminar">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-4 0a1 1 0 00-1 1v1h6V4a1 1 0 00-1-1m-4 0h4" />
                                                </svg>
                                            </button>

                                        </div>

                                        {{-- Modal de confirmación por producto --}}
                                        <div x-show="open" x-cloak
                                            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-96">
                                                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                                    ⚠️ Confirmar eliminación
                                                </h2>
                                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                                    ¿Seguro que quieres eliminar este producto?
                                                    Esta acción no se puede deshacer.
                                                </p>

                                                <div class="mt-4 flex justify-end gap-3">
                                                    {{-- Cancelar --}}
                                                    <button @click="open = false"
                                                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                                                        ❌ Cancelar
                                                    </button>

                                                    {{-- Confirmar --}}
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
                @endif

                {{-- Resumen + paginación --}}
                <div
                    class="mt-4 p-3 bg-gray-50 rounded-lg shadow-sm flex justify-between items-start text-sm text-gray-700">

                    {{-- Columna izquierda: información de la página actual --}}
                    <div class="flex flex-col gap-1">
                        <div>
                            Mostrando
                            <span class="font-bold">{{ $productos->firstItem() }}</span>
                            a
                            <span class="font-bold">{{ $productos->lastItem() }}</span>
                            de
                            <span class="font-bold">{{ $productos->total() }}</span>
                            resultados
                        </div>

                        <div class="flex items-center gap-1">
                            📦 <span>Stock total mostrado:
                                <span class="font-bold">{{ $pageStockTotal }}</span>
                                unidades
                            </span>
                        </div>

                        <div class="flex items-center gap-1">
                            💰 <span>Valor total mostrado:
                                <span class="font-bold">
                                    Gs. {{ number_format($pageValorTotal, 0, ',', '.') }}
                                </span>
                            </span>
                        </div>
                    </div>

                    {{-- Columna derecha: botones de paginación --}}
                    <div class="flex items-center">
                        {{ $productos->links() }}

                        {{-- Botón para volver al modo paginado si se usó "verTodo" --}}
                        @if (request()->has('verTodo'))
                            @php
                                $q = request()->query();
                                unset($q['verTodo']); // quitamos el parámetro verTodo
                                $urlSinVerTodo = request()->url() . (empty($q) ? '' : '?' . http_build_query($q));
                            @endphp

                            <a href="{{ $urlSinVerTodo }}"
                                class="ml-2 relative inline-flex items-center px-3 py-2 text-sm font-medium 
                   text-gray-700 bg-white border border-gray-300 leading-5 
                   hover:text-gray-500 focus:z-10 focus:outline-none focus:ring ring-gray-300 
                   focus:border-blue-300 active:bg-gray-100 active:text-gray-700 transition 
                   ease-in-out duration-150 dark:bg-gray-800 dark:border-gray-600 
                   dark:text-gray-400 dark:hover:text-gray-300 dark:active:bg-gray-700 
                   dark:focus:border-blue-800 rounded-md">
                                Ver paginado
                           </a>
        @endif
    </div>
</div>
</div> {{-- cierra tarjeta principal --}}
</div> {{-- cierra max-w-7xl --}}
</div> {{-- cierra py-6 --}}
</x-app-layout>