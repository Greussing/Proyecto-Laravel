<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Listado de Ventas') }}
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
                <div class="flex flex-wrap items-center justify-between gap-4 mb-4 w-full">
    {{-- Filtros de búsqueda → GET hacia ventas.index --}}
    <form method="GET"
          action="{{ route('ventas.index') }}"
          class="flex flex-wrap md:flex-nowrap gap-2 items-center flex-1 min-w-0">

        {{-- Buscar por Cliente --}}
        <div class="relative">
            {{-- Botón lupa --}}
            <button type="submit"
                    class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
                </svg>
            </button>

            {{-- Input búsqueda (un poco más pequeño) --}}
            <input type="text"
                   name="search"
                   id="searchVentas"
                   placeholder="Buscar por Cliente"
                   value="{{ request('search') }}"
                   class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300
                          focus:border-indigo-500 dark:focus:border-indigo-600
                          focus:ring-indigo-500 dark:focus:ring-indigo-600
                          rounded-md shadow-sm pl-9 pr-8 py-2 w-52 md:w-60 text-sm"
                   oninput="toggleSearchVentas(this)">

            {{-- Botón limpiar --}}
            <div id="searchVentas-icons"
                 class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-2 {{ request('search') ? '' : 'hidden' }}">
                <a href="{{ route('ventas.index', request()->except(['search', 'page'])) }}"
                   class="text-red-500 hover:text-red-700 font-bold">×</a>
            </div>
        </div>

        <script>
            function toggleSearchVentas(input) {
                const icons = document.getElementById('searchVentas-icons');
                if (input.value.trim() !== '') {
                    icons.classList.remove('hidden');
                } else {
                    icons.classList.add('hidden');
                }
            }
        </script>

        {{-- Filtro Fechas --}}
<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" @click.away="open = false" type="button"
            class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-[11px] md:text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
        @if (request('fecha_desde') || request('fecha_hasta'))
            <span class="text-blue-600 dark:text-blue-400">Fechas</span>
        @else
            Fechas
        @endif
        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
             fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                  clip-rule="evenodd" />
            </svg>
    </button>

    <div x-show="open" @click.stop
         class="absolute z-50 mt-2 w-64 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 p-4">

        <div class="mb-2">
            <label class="block text-xs text-gray-700 dark:text-gray-300 mb-1">Desde</label>
            <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                   class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-white">
        </div>

        <div class="mb-3">
            <label class="block text-xs text-gray-700 dark:text-gray-300 mb-1">Hasta</label>
            <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                   class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-white">
        </div>

        <button type="submit"
                class="w-full bg-blue-600 text-white text-xs font-bold py-2 rounded hover:bg-blue-700">
            Aplicar
        </button>

        @if (request('fecha_desde') || request('fecha_hasta'))
            <a href="{{ route('ventas.index', request()->except(['fecha_desde', 'fecha_hasta', 'page'])) }}"
                class="block text-center text-xs text-red-500 hover:text-red-700 mt-2">Limpiar</a>
        @endif
    </div>
</div>

        {{-- Filtro Método Pago --}}
<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" @click.away="open = false" type="button"
            class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-[11px] md:text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
        @if (request('metodo_pago'))
            <span class="text-blue-600 dark:text-blue-400">Pago: {{ request('metodo_pago') }}</span>
        @else
            Pago
        @endif
        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
             fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1-414z"
                  clip-rule="evenodd" />
        </svg>
    </button>

    <div x-show="open"
         class="absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 p-2">

        @foreach (['Efectivo', 'Tarjeta', 'Transferencia'] as $metodo)
            <label class="flex items-center p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded cursor-pointer">
                <input type="radio" name="metodo_pago" value="{{ $metodo }}"
                       {{ request('metodo_pago') === $metodo ? 'checked' : '' }}
                       onchange="this.form.submit()"
                       class="rounded-full border-gray-300 text-indigo-600 shadow-sm focus:ring">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">{{ $metodo }}</span>
            </label>
        @endforeach

        @if (request('metodo_pago'))
            <div class="border-t border-gray-200 dark:border-gray-600 mt-2 pt-2">
                <a href="{{ route('ventas.index', request()->except(['metodo_pago', 'page'])) }}"
                   class="block text-center text-xs text-red-500 hover:text-red-700">Limpiar</a>
            </div>
        @endif

    </div>
</div>

        {{-- Filtro Estado --}}
<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" @click.away="open = false" type="button"
            class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-[11px] md:text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
        @if (request('estado'))
            <span class="text-blue-600 dark:text-blue-400">Estado: {{ request('estado') }}</span>
        @else
            Estado
        @endif
        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
             fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                  clip-rule="evenodd" />
        </svg>
    </button>

    <div x-show="open"
         class="absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 p-2">

        @foreach (['Pagado', 'Pendiente', 'Anulado'] as $estado)
            <label class="flex items-center p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded cursor-pointer">
                <input type="radio" name="estado" value="{{ $estado }}"
                       {{ request('estado') === $estado ? 'checked' : '' }}
                       onchange="this.form.submit()"
                       class="rounded-full border-gray-300 text-indigo-600 shadow-sm focus:ring">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">{{ $estado }}</span>
            </label>
        @endforeach

        @if (request('estado'))
            <div class="border-t border-gray-200 dark:border-gray-600 mt-2 pt-2">
                <a href="{{ route('ventas.index', request()->except(['estado', 'page'])) }}"
                   class="block text-center text-xs text-red-500 hover:text-red-700">Limpiar</a>
            </div>
        @endif

    </div>
</div>

        {{-- Filtro Total --}}
<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" @click.away="open = false" type="button"
            class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-[11px] md:text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
        @if (request('total_min') || request('total_max'))
            <span class="text-blue-600 dark:text-blue-400">Total</span>
        @else
            Total
        @endif
        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
             fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                  clip-rule="evenodd" />
        </svg>
    </button>

    <div x-show="open" @click.stop
     class="absolute z-50 mt-2 w-64 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 p-4">
        <div class="mb-2">
            <label class="block text-xs text-gray-700 dark:text-gray-300 mb-1">Mínimo</label>
            <input type="text" name="total_min" id="total_min"
                   value="{{ request('total_min') }}"
                   placeholder="Ej: 10.000"
                   class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-white">
        </div>

        <div class="mb-3">
            <label class="block text-xs text-gray-700 dark:text-gray-300 mb-1">Máximo</label>
            <input type="text" name="total_max" id="total_max"
                   value="{{ request('total_max') }}"
                   placeholder="Ej: 500.000"
                   class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-white">
        </div>

        <button type="submit"
                class="w-full bg-blue-600 text-white text-xs font-bold py-2 rounded hover:bg-blue-700">
            Aplicar
        </button>

        @if (request('total_min') || request('total_max'))
            <a href="{{ route('ventas.index', request()->except(['total_min', 'total_max', 'page'])) }}"
               class="block text-center text-xs text-red-500 hover:text-red-700 mt-2">Limpiar</a>
        @endif
    </div>
</div>

        {{-- Ordenar --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" @click.away="open = false" type="button"
                    class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-[11px] md:text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                Ordenar
                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                     fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                          clip-rule="evenodd" />
                </svg>
            </button>
            <div x-show="open"
                 class="absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 py-1">
                @php
                    $opciones = [
                        'fecha_desc' => 'Fecha (Reciente)',
                        'fecha_asc'  => 'Fecha (Antigua)',
                        'total_desc' => 'Total (Mayor)',
                        'total_asc'  => 'Total (Menor)',
                    ];
                @endphp
                @foreach ($opciones as $key => $label)
                    <a href="{{ route('ventas.index', array_merge(request()->except(['ordenar', 'page']), ['ordenar' => $key])) }}"
                       class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 {{ request('ordenar') == $key ? 'bg-gray-100 dark:bg-gray-600 font-bold' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

    </form>

    {{-- 🔹 Contenedor fijo para PDF / Excel / Crear --}}
    <div class="flex items-center gap-2 flex-shrink-0">
        {{-- Exportar PDF --}}
        <a href="{{ route('ventas.export.pdf', request()->all()) }}"
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
        <a href="{{ route('ventas.export.excel', request()->all()) }}"
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

        {{-- Crear venta --}}
        <a href="{{ route('ventas.create') }}"
           class="inline-flex items-center justify-center h-8 w-8 md:h-9 md:w-9
                  bg-blue-600 hover:bg-blue-700 text-white rounded-full
                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
           title="Nueva venta">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 4v16m8-8H4" />
            </svg>
        </a>
    </div>
</div>

                {{-- Script AJAX para búsqueda dinámica en Ventas --}}
                        <script>
                            const ventasBusquedaUrl = "{{ route('ventas.busqueda') }}";

                            document.addEventListener('DOMContentLoaded', () => {
                                const input = document.getElementById('searchVentas');
                                const tabla = document.querySelector('table tbody');
                                if (!input || !tabla) return;

                                // Helper para badges (igual que en Movimientos)
                                const renderBadge = (type, text) => {
                                    const colors = {
                                        success: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                        danger:  'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                        warning: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                        info:    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                        gray:    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                    };
                                    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colors[type] || colors.gray}">
                                                ${text}
                                            </span>`;
                                };

                                let timeout = null;

                                input.addEventListener('input', function () {
                                    clearTimeout(timeout);
                                    const valor = this.value.trim();

                                    timeout = setTimeout(() => {

                                        // Si se borra el texto → volver a index normal
                                        if (valor === '') {
                                            window.location.href = "{{ route('ventas.index') }}";
                                            return;
                                        }

                                        // Mensaje de "Buscando..."
                                        tabla.innerHTML = `
                                            <tr>
                                                <td colspan="10" class="text-center py-4 text-gray-400">
                                                    Buscando...
                                                </td>
                                            </tr>`;

                                        // Mantener otros filtros de la URL si los hubiera
                                        const params = new URLSearchParams(window.location.search);
                                        params.set('search', valor);

                                        fetch(ventasBusquedaUrl + "?" + params.toString())
                                            .then(res => res.json())
                                            .then(data => {
                                                tabla.innerHTML = '';

                                                if (data.length === 0) {
                                                    tabla.innerHTML = `
                                                        <tr>
                                                            <td colspan="10" class="text-center py-4 text-gray-500">
                                                                No se encontraron ventas
                                                            </td>
                                                        </tr>`;
                                                    return;
                                                }

                                                data.forEach((v) => {
                                                    // Cliente
                                                    const cliente = v.cliente_relacion
                                                        ? v.cliente_relacion.nombre
                                                        : '—';

                                                    // Producto → primer detalle
                                                    const producto = v.detalles && v.detalles.length > 0 && v.detalles[0].producto
                                                        ? v.detalles[0].producto.nombre
                                                        : '—';

                                                    // Cantidad → primer detalle
                                                    const cantidad = v.detalles && v.detalles.length > 0 && typeof v.detalles[0].cantidad !== 'undefined'
                                                        ? v.detalles[0].cantidad
                                                        : 0;

                                                    // Fecha
                                                    const fecha = v.fecha
                                                        ? new Date(v.fecha).toLocaleDateString("es-PY")
                                                        : "—";

                                                    // Badge método de pago
                                                    let metodoBadge = 'gray';
                                                    if (v.metodo_pago === 'Efectivo') {
                                                        metodoBadge = 'success';
                                                    } else if (v.metodo_pago === 'Tarjeta' || v.metodo_pago === 'Transferencia') {
                                                        metodoBadge = 'info';
                                                    }

                                                    // Badge estado
                                                    let estadoBadge = 'gray';
                                                    if (v.estado === 'Pagado') {
                                                        estadoBadge = 'success';
                                                    } else if (v.estado === 'Pendiente') {
                                                        estadoBadge = 'warning';
                                                    } else if (v.estado === 'Anulado') {
                                                        estadoBadge = 'danger';
                                                    }

                                                    // Badge cantidad (misma lógica que en Blade)
                                                    const cantBadgeType =
                                                        cantidad == 0 ? 'danger' :
                                                        cantidad <= 5 ? 'danger' :
                                                        cantidad <= 10 ? 'warning' :
                                                        'success';

                                                    tabla.innerHTML += `
                                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duración-200">
                                                            
                                                            <!-- ID -->
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                                ${v.id}
                                                            </td>

                                                            <!-- Fecha -->
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                                ${fecha}
                                                            </td>

                                                            <!-- Cliente -->
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                                ${cliente}
                                                            </td>

                                                            <!-- Producto -->
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                                ${producto}
                                                            </td>

                                                            <!-- Cantidad con badge -->
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                                ${renderBadge(cantBadgeType, cantidad)}
                                                            </td>

                                                            <!-- Precio Unitario -->
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                                ${
                                                                    v.detalles && v.detalles.length > 0 && v.detalles[0].precio_unitario
                                                                        ? "Gs. " + Number(v.detalles[0].precio_unitario).toLocaleString("es-PY")
                                                                        : "—"
                                                                }
                                                            </td>

                                                            <!-- Total -->
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900 dark:text-white">
                                                                ${
                                                                    v.total
                                                                        ? "Gs. " + Number(v.total).toLocaleString("es-PY")
                                                                        : "Gs. 0"
                                                                }
                                                            </td>

                                                            <!-- Método de pago -->
                                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                                ${renderBadge(metodoBadge, v.metodo_pago || '—')}
                                                            </td>

                                                            <!-- Estado -->
                                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                                ${renderBadge(estadoBadge, v.estado || '—')}
                                                            </td>

                                                            <!-- Acciones (vacío en búsqueda AJAX) -->
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-400">
                                                                —
                                                            </td>
                                                        </tr>
                                                    `;
                                                });
                                            })
                                            .catch(err => console.error("Error en búsqueda:", err));
                                    }, 300);
                                });
                            });
                        </script>

{{-- Script para formatear números (inputs de precio con separador de miles) --}}
<script>
    function formatNumber(value) {
        if (!value) return '';
        return value.toString()
            .replace(/\D/g, '')                // quitar todo lo que no sea número
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // agregar puntos
    }

    function applyFormat(input) {
        input.value = formatNumber(input.value);

        input.addEventListener('input', function () {
            let cursorPos = this.selectionStart;
            let originalLength = this.value.length;

            this.value = formatNumber(this.value);

            let newLength = this.value.length;
            this.selectionEnd = cursorPos + (newLength - originalLength);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const totalMin = document.getElementById('total_min');
        const totalMax = document.getElementById('total_max');

        if (totalMin) applyFormat(totalMin);
        if (totalMax) applyFormat(totalMax);
    });
</script>

                {{-- Tabla de Ventas --}}
@if ($ventas->isEmpty())
    <p class="p-4 text-center text-gray-500 dark:text-gray-400">
        No hay ventas registradas.
    </p>
@else
    <div class="overflow-x-auto">
        <x-table :headers="['ID', 'Fecha', 'Cliente', 'Producto', 'Cant.', 'Precio', 'Total', 'Método', 'Estado', 'Acciones']">
            @foreach ($ventas as $venta)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 text-xs md:text-sm"
                    x-data="{ open: false }">

                    {{-- ID --}}
                    <td class="px-3 py-3 text-center">
                        {{ $venta->id }}
                    </td>

                    {{-- Fecha (sin nowrap) --}}
                    <td class="px-3 py-3 text-center">
                        {{ $venta->fecha ? $venta->fecha->format('d/m/Y') : '-' }}
                    </td>

                    {{-- Cliente (sin min-w, con wrap) --}}
                    <td class="px-3 py-3 text-center break-words max-w-[140px]">
                        {{ $venta->clienteRelacion->nombre ?? '-' }}
                    </td>

                    {{-- Producto (sin min-w, con wrap) --}}
                    <td class="px-3 py-3 text-center break-words max-w-[140px]">
                        {{ $venta->productoRelacion->nombre ?? '-' }}
                    </td>

                    {{-- Cantidad --}}
                    <td class="px-3 py-3 text-center">
                        @php
                            $stockType = 'success';
                            if ($venta->cantidad_productos == 0) $stockType = 'danger';
                            elseif ($venta->cantidad_productos <= 5) $stockType = 'danger';
                            elseif ($venta->cantidad_productos <= 10) $stockType = 'warning';
                        @endphp
                        <x-badge :type="$stockType">
                            {{ $venta->cantidad_productos }}
                        </x-badge>
                    </td>

                    {{-- Precio Unitario (sin nowrap) --}}
                    <td class="px-3 py-3 text-center">
                        @if ($venta->detalles->first())
                            Gs. {{ number_format($venta->detalles->first()->precio_unitario, 0, ',', '.') }}
                        @else
                            —
                        @endif
                    </td>

                    {{-- Total (sin nowrap, pero a la derecha) --}}
                    <td class="px-3 py-3 text-right font-bold text-gray-900 dark:text-white">
                        Gs. {{ number_format($venta->total, 0, ',', '.') }}
                    </td>

                    {{-- Método --}}
                    <td class="px-3 py-3 text-center">
                        @php
                            $metodoType = match($venta->metodo_pago) {
                                'Efectivo' => 'success',
                                'Tarjeta', 'Transferencia' => 'info',
                                default => 'gray'
                            };
                        @endphp
                        <x-badge :type="$metodoType">
                            {{ $venta->metodo_pago }}
                        </x-badge>
                    </td>

                    {{-- Estado --}}
                    <td class="px-3 py-3 text-center">
                        @php
                            $estadoType = match($venta->estado) {
                                'Pagado' => 'success',
                                'Pendiente' => 'warning',
                                'Anulado' => 'danger',
                                default => 'gray'
                            };
                        @endphp
                        <x-badge :type="$estadoType">
                            {{ $venta->estado }}
                        </x-badge>
                    </td>
                                     {{-- Acciones --}}
                    <td class="px-3 py-3 text-center">
                        <div class="flex justify-center gap-3">
                            {{-- Editar --}}
                            <a href="{{ route('ventas.edit', $venta->id) }}"
                               class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                               title="Editar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>

                            {{-- Devolución --}}
                            @if ($venta->estado === 'Pagado')
                                <a href="{{ route('ventas.devolucion.form', $venta->id) }}"
                                   class="text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300"
                                   title="Devolución">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                </a>
                            @endif

                            {{-- Eliminar --}}
                            <button @click="open = true"
                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                    title="Eliminar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-4 0a1 1 0 00-1 1v1h6V4a1 1 0 00-1-1m-4 0h4" />
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
                                                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">Eliminar Venta</h3>
                                                                <div class="mt-2">
                                                                    <p class="text-sm text-gray-500 dark:text-gray-400">¿Estás seguro de que deseas eliminar esta venta? Esta acción no se puede deshacer.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                        <form action="{{ route('ventas.destroy', $venta->id) }}" method="POST">
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
                    </div>

                    {{-- Paginación --}}
                    <div class="mt-4">
                        {{ $ventas->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>