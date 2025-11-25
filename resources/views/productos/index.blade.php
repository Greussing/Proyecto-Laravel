<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Listado de Productos') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Mensaje de éxito --}}
            @if (session('success'))
                <div class="mb-4 text-green-700 bg-green-100 dark:bg-green-900 dark:text-green-300 p-4 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <x-card>
                {{-- Contenedor filtros + botón crear producto --}}
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-3 w-full">

    {{-- Filtros de búsqueda --}}
    <form method="GET"
          action="{{ route('productos.index') }}"
          class="flex flex-wrap md:flex-nowrap gap-2 items-center flex-1 min-w-0">

        {{-- Buscar por nombre --}}
        <div class="relative">
            <button type="submit"
                    class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
                </svg>
            </button>

            <input type="text" name="search" placeholder="Buscar por Nombre"
                   value="{{ request('search') }}"
                   class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300
                          focus:border-indigo-500 dark:focus:border-indigo-600
                          focus:ring-indigo-500 dark:focus:ring-indigo-600
                          rounded-md shadow-sm pl-9 pr-8 py-2
                          w-52 md:w-60 lg:w-72"
                   oninput="toggleSearchIcons(this)">

            <div id="search-icons"
                 class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-2 {{ request('search') ? '' : 'hidden' }}">
                <a href="{{ route('productos.index', request()->except(['search', 'page'])) }}"
                   class="text-red-500 hover:text-red-700 font-bold">×</a>
            </div>
        </div>


                        {{-- Filtro Categorías --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false" type="button" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                @if (request('categorias'))
                                    <span class="text-blue-600 dark:text-blue-400">Categorías ({{ count((array)request('categorias')) }})</span>
                                @else
                                    Categorías
                                @endif
                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="open" class="absolute z-50 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 overflow-y-auto max-h-60 p-2">
                                @foreach ($categorias as $cat)
                                    <label class="flex items-center p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded cursor-pointer">
                                        <input type="checkbox" name="categorias[]" value="{{ $cat->id }}"
                                            {{ in_array($cat->id, (array) request('categorias')) ? 'checked' : '' }}
                                            onchange="this.form.submit()"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">{{ $cat->nombre }}</span>
                                    </label>
                                @endforeach
                                @if (request('categorias'))
                                    <div class="border-t border-gray-200 dark:border-gray-600 mt-2 pt-2">
                                        <a href="{{ route('productos.index', request()->except(['categorias', 'page'])) }}" class="block text-center text-xs text-red-500 hover:text-red-700">Limpiar filtro</a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Filtro Stock --}}
<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" @click.away="open = false" type="button"
        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600
        rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest
        shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2
        focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">

        @if (request('stock'))
            @php
                $stockLabel = [
                    'disponibles' => 'Disponibles',
                    'agotados'    => 'Agotados',
                ];
                $label = $stockLabel[request('stock')] ?? request('stock');
            @endphp
            <span class="text-blue-600 dark:text-blue-400">Stock: {{ $label }}</span>
        @else
            Stock
        @endif

        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd" />
        </svg>
    </button>

    <div x-show="open"
        class="absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-700
        ring-1 ring-black ring-opacity-5 p-2">

        @php
            $opcionesStock = [
                'disponibles' => 'Disponibles',
                'agotados'    => 'Agotados',
            ];
        @endphp

        @foreach ($opcionesStock as $value => $label)
            <label
                class="flex items-center p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded cursor-pointer">
                <input type="radio" name="stock" value="{{ $value }}"
                    {{ request('stock') === $value ? 'checked' : '' }}
                    onchange="this.form.submit()"
                    class="rounded-full border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300
                    focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">{{ $label }}</span>
            </label>
        @endforeach

        @if (request('stock'))
            <div class="border-t border-gray-200 dark:border-gray-600 mt-2 pt-2">
                <a href="{{ route('productos.index', request()->except(['stock', 'page'])) }}"
                    class="block text-center text-xs text-red-500 hover:text-red-700">Limpiar filtro</a>
            </div>
        @endif
    </div>
</div>

                        {{-- Filtro Precio --}}
<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" @click.away="open = false" type="button"
        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
        @if (request('precio_min') || request('precio_max'))
            <span class="text-blue-600 dark:text-blue-400">Precio</span>
        @else
            Precio
        @endif
        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
            fill="currentColor">
            <path fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd" />
        </svg>
    </button>

    <div x-show="open" @click.stop
        class="absolute z-50 mt-2 w-64 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 p-4">

        <div class="mb-2">
            <label class="block text-xs text-gray-700 dark:text-gray-300 mb-1">Mínimo</label>
            <input type="text" name="precio_min" id="precio_min" 
                value="{{ request('precio_min') }}"
                placeholder="Ej: 1.000"
                class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-white">
        </div>

        <div class="mb-3">
            <label class="block text-xs text-gray-700 dark:text-gray-300 mb-1">Máximo</label>
            <input type="text" name="precio_max" id="precio_max" 
                value="{{ request('precio_max') }}"
                placeholder="Ej: 50.000"
                class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-white">
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white text-xs font-bold py-2 rounded hover:bg-blue-700">
            Aplicar
        </button>

        @if (request('precio_min') || request('precio_max'))
            <a href="{{ route('productos.index', request()->except(['precio_min', 'precio_max', 'page'])) }}"
                class="block text-center text-xs text-red-500 hover:text-red-700 mt-2">Limpiar</a>
        @endif
    </div>
</div>

                        {{-- Ordenar --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false" type="button" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Ordenar
                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="open" class="absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 py-1">
                                @php
                                    $opciones = [
                                        'nombre_asc' => 'Nombre (A-Z)',
                                        'nombre_desc' => 'Nombre (Z-A)',
                                        'precio_asc' => 'Precio (menor a mayor)',
                                        'precio_desc' => 'Precio (mayor a menor)',
                                        'stock_asc' => 'Stock (menor a mayor)',
                                        'stock_desc' => 'Stock (mayor a menor)',
                                    ];
                                @endphp
                                @foreach ($opciones as $key => $label)
                                    <a href="{{ route('productos.index', array_merge(request()->except(['ordenar', 'page']), ['ordenar' => $key])) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 {{ request('ordenar') == $key ? 'bg-gray-100 dark:bg-gray-600 font-bold' : '' }}">
                                        {{ $label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                    </form>

                    {{-- 🔹 Contenedor fijo para PDF / Excel / Crear --}}
    <div class="flex items-center gap-2 flex-shrink-0">

        {{-- Exportar PDF (más compacto, como ya tenías) --}}
        <a href="{{ route('productos.export.pdf', request()->all()) }}"
           class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md
                  font-semibold text-[11px] md:text-xs text-white uppercase tracking-widest
                  hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none
                  focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            PDF
        </a>

        {{-- Exportar Excel --}}
        <a href="{{ route('productos.export.excel', request()->all()) }}"
           class="inline-flex items-center px-3 py-1.5 bg-green-600 border border-transparent rounded-md
                  font-semibold text-[11px] md:text-xs text-white uppercase tracking-widest
                  hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none
                  focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Excel
        </a>

        {{-- Crear producto: icono redondo --}}
        <a href="{{ route('productos.create') }}"
           class="inline-flex items-center justify-center h-8 w-8 md:h-9 md:w-9
                  bg-blue-600 hover:bg-blue-700 text-white rounded-full
                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
           title="Nuevo producto">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
        </a>
    </div>
</div>

                 {{-- Script búsqueda dinámica (AJAX) --}}
<script>
    const busquedaUrl = "{{ route('productos.busqueda') }}";

    function toggleSearchIcons(input) {
        const icons = document.getElementById('search-icons');
        if (input.value.trim() !== '') {
            icons.classList.remove('hidden');
        } else {
            icons.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const input = document.querySelector('input[name="search"]');
        const tablaBody = document.querySelector('table tbody');
        let timeout = null;

        if (input && tablaBody) {
            input.addEventListener('input', function() {
                clearTimeout(timeout);
                const valor = this.value.trim();

                timeout = setTimeout(() => {
                    if (valor === '') {
                        window.location.reload();
                        return;
                    }

                    tablaBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-gray-400">Buscando...</td></tr>`;

                    fetch(busquedaUrl + "?search=" + encodeURIComponent(valor))
                        .then(res => res.json())
                        .then(data => {
                            tablaBody.innerHTML = '';
                            if (data.length === 0) {
                                tablaBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-gray-500">No se encontraron productos</td></tr>`;
                                return;
                            }

                            data.forEach((p, i) => {
                                const catName = p.categoria_relacion ? p.categoria_relacion.nombre : 'Sin Categoría';

                                const categoriaBadge = (() => {
                                    let typeClass = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                    if (catName === 'Electrónica') {
                                        typeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
                                    } else if (catName === 'Alimentos') {
                                        typeClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
                                    } else if (catName === 'Ropa') {
                                        typeClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
                                    }

                                    return `
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${typeClass}">
                                            ${catName}
                                        </span>
                                    `;
                                })();

                                const cantidad = p.cantidad ?? 0;
                                let stockTypeClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
                                let stockText = cantidad;

                                if (cantidad == 0) {
                                    stockTypeClass = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
                                    stockText = 'Agotado';
                                } else if (cantidad <= 5) {
                                    stockTypeClass = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
                                } else if (cantidad <= 10) {
                                    stockTypeClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
                                }

                                const stockBadge = `
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${stockTypeClass}">
                                        ${stockText}
                                    </span>
                                `;

                                tablaBody.innerHTML += `
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duración-200">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            ${p.id}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            ${p.nombre}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            ${categoriaBadge}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            ${stockBadge}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-white">
                                            Gs. ${Number(p.precio).toLocaleString('es-PY')}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-400">
                                            —
                                        </td>
                                    </tr>`;
                            });
                        })
                        .catch(err => console.error('Error en búsqueda:', err));
                }, 300);
            });
        }
    });
</script>

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

                {{-- Tabla de Productos --}}
                @if ($productos->isEmpty())
                    <p class="p-4 text-center text-gray-500 dark:text-gray-400">
                        No hay productos registrados.
                    </p>
                @else
                    <x-table :headers="['ID', 'Nombre', 'Categoría', 'Cantidad', 'Precio', 'Acciones']">
                        @foreach ($productos as $producto)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200" x-data="{ open: false }">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    {{ $producto->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    {{ $producto->nombre }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $catName = $producto->categoriaRelacion->nombre ?? 'Sin Categoría';
                                        $badgeType = match($catName) {
                                            'Electrónica' => 'info',
                                            'Alimentos' => 'success',
                                            'Ropa' => 'warning',
                                            default => 'gray'
                                        };
                                    @endphp
                                    <x-badge :type="$badgeType">{{ $catName }}</x-badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
    @php
        $stockType = 'success';
        $stockText = $producto->cantidad;

        if ($producto->cantidad == 0) {
            $stockType = 'danger';
            $stockText = 'Agotado';
        } elseif ($producto->cantidad <= 5) {
            $stockType = 'danger';
            $stockText = $producto->cantidad;
        } elseif ($producto->cantidad <= 10) {
            $stockType = 'warning';
            $stockText = $producto->cantidad;
        }
    @endphp

    <x-badge :type="$stockType">
        {{ $stockText }}
    </x-badge>
</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-white">
                                    Gs. {{ number_format($producto->precio, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    <div class="flex justify-center gap-3">
                                        <a href="{{ route('productos.edit', $producto->id) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Editar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <button @click="open = true" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Eliminar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-4 0a1 1 0 00-1 1v1h6V4a1 1 0 00-1-1m-4 0h4" />
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Modal Confirmación --}}
                                    <div x-show="open" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                            <div x-show="open" @click="open = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                            <div x-show="open" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                    <div class="sm:flex sm:items-start">
                                                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                            </svg>
                                                        </div>
                                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">Eliminar Producto</h3>
                                                            <div class="mt-2">
                                                                <p class="text-sm text-gray-500 dark:text-gray-400">¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                    <form action="{{ route('productos.destroy', $producto->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">Eliminar</button>
                                                    </form>
                                                    <button @click="open = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </x-table>

                    {{-- Paginación --}}
                    <div class="mt-4">
                        {{ $productos->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>