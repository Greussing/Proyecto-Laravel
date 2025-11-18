<x-app-layout>
    {{-- Encabezado de la página --}}
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-white">
            Listado de Ventas
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

                {{-- 🔹 Filtros + Botón crear venta --}}
                <div class="mb-4 flex justify-between items-center flex-wrap gap-2 w-full">

                    {{-- Filtros de búsqueda → GET hacia ventas.index --}}
<form method="GET" action="{{ route('ventas.index') }}" class="flex flex-wrap gap-2">

    {{-- Buscar por Cliente --}}
    <div class="relative">

        <!-- Botón lupa -->
        <button type="submit"
            class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-blue-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
            </svg>
        </button>

        <!-- Input búsqueda -->
        <input type="text" name="search" id="searchVentas"
            placeholder="Buscar por Cliente"
            value="{{ request('search') }}"
            class="border rounded pl-9 pr-14 py-1 w-60 md:w-72 focus:ring-2 focus:ring-indigo-500 outline-none"
            oninput="toggleSearchVentas(this)">

        <!-- Botón limpiar -->
        <div id="searchVentas-icons"
            class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-2 {{ request('search') ? '' : 'hidden' }}">
            <a href="{{ route('ventas.index', request()->except(['search', 'page'])) }}"
                class="text-red-500 hover:text-red-700 font-bold">×</a>
        </div>
    </div>

{{-- Script mostrar/ocultar ícono limpiar --}}
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

{{-- Script AJAX para búsqueda dinámica --}}
<script>
    const ventasBusquedaUrl = "{{ route('ventas.busqueda') }}";

    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('searchVentas');
        const tabla = document.querySelector('table tbody');
        let timeout = null;

        input.addEventListener('input', function () {
            clearTimeout(timeout);
            const valor = this.value.trim();

            timeout = setTimeout(() => {

                if (valor === '') {
                    window.location.href = "{{ route('ventas.index') }}";
                    return;
                }

                tabla.innerHTML = `
                    <tr><td colspan="10" class="text-center py-4 text-gray-400">
                        Buscando...
                    </td></tr>`;

                fetch(ventasBusquedaUrl + "?search=" + encodeURIComponent(valor))
                    .then(res => res.json())
                    .then(data => {
                        tabla.innerHTML = '';

                        if (data.length === 0) {
                            tabla.innerHTML = `
                                <tr><td colspan="10" class="text-center py-4 text-gray-500">
                                    No se encontraron ventas
                                </td></tr>`;
                            return;
                        }

                        data.forEach((v) => {

                            // Cliente
                            const cliente = v.cliente_relacion
                                ? v.cliente_relacion.nombre
                                : 'N/A';

                            // Producto → primer detalle
                            const producto = v.detalles && v.detalles.length > 0
                                ? v.detalles[0].producto.nombre
                                : '—';

                            // Cantidad
                            const cantidad = v.detalles && v.detalles.length > 0
                                ? v.detalles[0].cantidad
                                : '—';

                            tabla.innerHTML += `
<tr class="border-b hover:bg-gray-50">

    <!-- ID -->
    <td class="px-4 py-2 text-center font-medium text-gray-700">
        #${v.id}
    </td>

    <!-- Cliente -->
    <td class="px-4 py-2">
        ${cliente}
    </td>

    <!-- Producto -->
    <td class="px-4 py-2">
        ${producto}
    </td>

    <!-- Cantidad -->
    <td class="px-4 py-2 text-center font-semibold">
        ${cantidad}
    </td>

    <!-- Precio Unitario -->
    <td class="px-4 py-2 text-right">
        ${
            v.detalles && v.detalles.length > 0
                ? "Gs. " + Number(v.detalles[0].precio_unitario).toLocaleString("es-PY")
                : "—"
        }
    </td>

    <!-- Total -->
    <td class="px-4 py-2 text-right font-bold text-blue-700">
        Gs. ${Number(v.total).toLocaleString("es-PY")}
    </td>

    <!-- Método de pago -->
    <td class="px-4 py-2 text-center">
        ${v.metodo_pago ?? "—"}
    </td>

    <!-- Estado -->
    <td class="px-4 py-2 text-center">
        ${v.estado ?? "—"}
    </td>

    <!-- Fecha -->
    <td class="px-4 py-2 text-center text-gray-700">
        ${
            v.fecha
                ? new Date(v.fecha).toLocaleDateString("es-PY")
                : "—"
        }
    </td>

    <!-- Acciones (vacío en búsqueda AJAX) -->
    <td class="px-4 py-2 text-center text-gray-400">
        —
    </td>

</tr>`;
                        });
                    })
                    .catch(err => console.error("Error en búsqueda:", err));
            }, 300);
        });
    });
</script>

                        {{-- Filtro por Fechas (desde / hasta) --}}
                        <details class="relative border rounded px-2 py-1">
                            <summary
                                class="cursor-pointer select-none summary-arrow {{ request('fecha_desde') || request('fecha_hasta') ? 'text-blue-600 font-bold' : '' }}">
                                @if (request('fecha_desde') || request('fecha_hasta'))
                                    Fechas:
                                    {{ request('fecha_desde') ? \Carbon\Carbon::parse(request('fecha_desde'))->format('d/m/Y') : 'Inicio' }}
                                    –
                                    {{ request('fecha_hasta') ? \Carbon\Carbon::parse(request('fecha_hasta'))->format('d/m/Y') : 'Hoy' }}
                                    <a href="{{ route('ventas.index', request()->except(['fecha_desde', 'fecha_hasta', 'page'])) }}"
                                        class="ml-2 text-red-500 font-bold hover:text-red-700">✕</a>
                                @else
                                    Fechas
                                @endif
                            </summary>
                            {{-- Inputs de fechas --}}
                            <div class="absolute bg-white border rounded shadow-md mt-1 z-10 p-2 w-56">
                                <label class="block text-sm text-gray-700">Desde</label>
                                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                                    class="w-full border rounded px-2 py-1 text-sm mb-2 focus:ring-1 focus:ring-indigo-500">

                                <label class="block text-sm text-gray-700">Hasta</label>
                                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                                    class="w-full border rounded px-2 py-1 text-sm focus:ring-1 focus:ring-indigo-500">

                                <button type="submit"
                                    class="mt-2 bg-blue-600 text-white py-1 rounded hover:bg-blue-700 text-sm w-full">
                                    Aplicar
                                </button>
                            </div>
                        </details>

                        {{-- Filtro por Método de pago --}}
                        @php
                            $metodosSeleccionados = (array) request('metodo_pago');
                        @endphp

                        <details class="relative border rounded px-2 py-1">
                            <summary
                                class="cursor-pointer select-none summary-arrow {{ $metodosSeleccionados ? 'text-blue-600 font-bold' : '' }}">
                                @if ($metodosSeleccionados)
                                    Método:
                                    {{ implode(', ', $metodosSeleccionados) }}
                                    <a href="{{ route('ventas.index', request()->except(['metodo_pago', 'page'])) }}"
                                        class="ml-2 text-red-500 font-bold hover:text-red-700">✕</a>
                                @else
                                    Método de pago
                                @endif
                            </summary>

                            <div class="absolute bg-white border rounded shadow-md mt-1 z-10 p-2 w-56">
                                @foreach (['Efectivo', 'Tarjeta', 'Transferencia'] as $metodo)
                                    <label class="flex items-center">
                                        {{-- Cambiado a radio y sin [] en el name --}}
                                        <input type="radio" name="metodo_pago" value="{{ $metodo }}"
                                            {{ in_array($metodo, $metodosSeleccionados) ? 'checked' : '' }}
                                            onchange="this.form.submit()">
                                        <span class="ml-2">{{ $metodo }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </details>

                        {{-- Filtro por Estado --}}
                        @php
                            $estadosSeleccionados = (array) request('estado');
                        @endphp

                        <details class="relative border rounded px-2 py-1">
                            <summary
                                class="cursor-pointer select-none summary-arrow {{ $estadosSeleccionados ? 'text-blue-600 font-bold' : '' }}">
                                @if ($estadosSeleccionados)
                                    Estado:
                                    {{ implode(', ', $estadosSeleccionados) }}
                                    <a href="{{ route('ventas.index', request()->except(['estado', 'page'])) }}"
                                        class="ml-2 text-red-500 font-bold hover:text-red-700">✕</a>
                                @else
                                    Estado
                                @endif
                            </summary>

                            <div class="absolute bg-white border rounded shadow-md mt-1 z-10 p-2 w-56">
                                @foreach (['Pagado', 'Pendiente', 'Anulado'] as $est)
                                    <label class="flex items-center">
                                        {{-- Cambiado a radio y sin [] en el name --}}
                                        <input type="radio" name="estado" value="{{ $est }}"
                                            {{ in_array($est, $estadosSeleccionados) ? 'checked' : '' }}
                                            onchange="this.form.submit()">
                                        <span class="ml-2">{{ $est }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </details>

                        {{-- Filtro por Total (mín y máx) --}}
                        <details class="relative border rounded px-2 py-1">
                            <summary
                                class="cursor-pointer select-none summary-arrow {{ request('total_min') || request('total_max') ? 'text-blue-600 font-bold' : '' }}">
                                @if (request('total_min') || request('total_max'))
                                    Total:
                                    {{ request('total_min')
                                        ? 'Gs. ' . number_format((int) str_replace(['.', ','], '', request('total_min')), 0, ',', '.')
                                        : '0' }}
                                    –
                                    {{ request('total_max')
                                        ? 'Gs. ' . number_format((int) str_replace(['.', ','], '', request('total_max')), 0, ',', '.')
                                        : '∞' }}
                                    <a href="{{ route('ventas.index', request()->except(['total_min', 'total_max', 'page'])) }}"
                                        class="ml-2 text-red-500 font-bold hover:text-red-700">✕</a>
                                @else
                                    Total
                                @endif
                            </summary>

                            {{-- Inputs de total --}}
                            <div class="absolute bg-white border rounded shadow-md mt-1 z-10 p-2 w-56">

                                <label class="block text-sm text-gray-700">Mínimo</label>
                                <div class="flex items-center border rounded px-2 py-1 w-full mb-2">
                                    <span class="text-gray-600 mr-1">Gs.</span>
                                    <input type="text" name="total_min" id="total_min"
                                        value="{{ request('total_min') ? number_format((int) str_replace(['.', ','], '', request('total_min')), 0, ',', '.') : '' }}"
                                        placeholder="Ej: 50.000"
                                        class="formatear-numero flex-1 text-sm border-0 focus:ring-0 p-0 outline-none">
                                </div>

                                <label class="block text-sm text-gray-700">Máximo</label>
                                <div class="flex items-center border rounded px-2 py-1 w-full">
                                    <span class="text-gray-600 mr-1">Gs.</span>
                                    <input type="text" name="total_max" id="total_max"
                                        value="{{ request('total_max') ? number_format((int) str_replace(['.', ','], '', request('total_max')), 0, ',', '.') : '' }}"
                                        placeholder="Ej: 500.000"
                                        class="formatear-numero flex-1 text-sm border-0 focus:ring-0 p-0 outline-none">
                                </div>

                                <button type="submit"
                                    class="mt-2 bg-blue-600 text-white py-1 rounded hover:bg-blue-700 text-sm w-full">
                                    Aplicar
                                </button>
                            </div>
                        </details>

                        {{-- ORDENAR --}}
                        @php
                            $ordenes = [
                                'fecha_desc' => 'Fecha (recientes)',
                                'fecha_asc' => 'Fecha (antiguas)',
                                'total_desc' => 'Total (mayor)',
                                'total_asc' => 'Total (menor)',
                            ];
                            $ordenActual = request('ordenar') ? $ordenes[request('ordenar')] ?? 'Ordenar' : 'Ordenar';
                        @endphp

                        <details class="relative border rounded px-2 py-1">
                            <summary
                                class="cursor-pointer select-none summary-arrow {{ request('ordenar') ? 'text-blue-600 font-bold' : '' }}">
                                {{ $ordenActual }}
                                @if (request('ordenar'))
                                    <a href="{{ route('ventas.index', request()->except(['ordenar', 'page'])) }}"
                                        class="ml-2 text-red-500 font-bold">✕</a>
                                @endif
                            </summary>

                            <div class="absolute bg-white border rounded shadow-md mt-1 w-52 z-10">
                                <ul>
                                    @foreach ($ordenes as $valor => $texto)
                                        <li>
                                            <a href="{{ route('ventas.index', array_merge(request()->except(['ordenar', 'page']), ['ordenar' => $valor])) }}"
                                                class="block px-3 py-1 hover:bg-gray-100">
                                                {{ $texto }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </details>

                        {{-- Análisis de Productos --}}
                        <details class="relative border rounded px-2 py-1">
                            <summary class="cursor-pointer select-none summary-arrow">
                                Análisis Productos
                            </summary>

                            <div class="absolute bg-white border rounded shadow-md mt-1 w-56 z-10">
                                <ul class="text-sm">
                                    <li>
                                        <a href="{{ route('analisis.index') }}"
                                            class="block px-3 py-2 hover:bg-gray-100">
                                            📋 Ver Análisis
                                        </a>
                                    </li>

                                    <hr class="my-1">

                                    <li>
                                        <a href="{{ route('analisis.pdf') }}"
                                            class="block px-3 py-2 hover:bg-gray-100">
                                            🧾 Descargar en PDF (PDF)
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ route('analisis.excel') }}"
                                            class="block px-3 py-2 hover:bg-gray-100">
                                            📊 Exportar a Excel (Excel)
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </details>

                        {{-- HISTORIAL DE STOCK --}}
                        <details class="relative border rounded px-2 py-1">
                            <summary class="cursor-pointer select-none summary-arrow">
                                Gestión Stock
                            </summary>

                            <div class="absolute bg-white border rounded shadow-md mt-1 w-64 z-10">
                                <ul class="text-sm">
                                    <li>
                                        <a href="{{ route('movimientos.index') }}"
                                            class="block px-3 py-2 hover:bg-gray-100">
                                            📋 Ver movimientos de stock
                                        </a>
                                    </li>

                                    <hr class="my-1">

                                    <li>
                                        <a href="{{ route('movimientos.export.pdf') }}"
                                            class="block px-3 py-2 hover:bg-gray-100">
                                            🧾 Exportar movimientos (PDF)
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('movimientos.export.excel') }}"
                                            class="block px-3 py-2 hover:bg-gray-100">
                                            📊 Exportar movimientos (Excel)
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </details>


                        {{-- 🔹 Botón crear venta --}}
                        <a href="{{ route('ventas.create') }}" class="text-gray-500 hover:text-green-600 transition"
                            title="Nueva Venta">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                        </a>

                        {{-- Script para formatear números con puntos --}}
                        <script>
                            document.addEventListener('input', function(e) {
                                if (e.target.classList.contains('formatear-numero')) {
                                    let value = e.target.value.replace(/\./g, ''); // Quita puntos
                                    value = value.replace(/\D/g, ''); // Solo números

                                    if (value) {
                                        value = Number(value).toLocaleString('es-ES'); // Pone puntos automáticamente
                                    }

                                    e.target.value = value;
                                }
                            });
                        </script>

                    </form> {{-- 👈 aquí cerramos el form de filtros --}}

                </div> {{-- 👈 aquí cerrás el div "mb-4 flex justify-between ..." --}}

                {{-- Si no hay ventas --}}
                @if ($ventas->isEmpty())
                    <p class="p-4 text-center text-gray-500">
                        No hay ventas registradas.
                    </p>
                @else
                    {{-- Tabla principal --}}
                    <table class="min-w-full border border-gray-200 rounded">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 border text-center">ID</th>
                                <th class="px-4 py-2 border">Cliente</th>
                                <th class="px-4 py-2 border">Producto</th>
                                <th class="px-4 py-2 border text-center">Cantidad</th>
                                <th class="px-4 py-2 border text-right">Precio Unitario</th>
                                <th class="px-4 py-2 border text-right">Total</th>
                                <th class="px-4 py-2 border text-center">Método de pago</th>
                                <th class="px-4 py-2 border text-center">Estado</th>
                                <th class="px-4 py-2 border text-center">Fecha</th>
                                <th class="px-4 py-2 border text-center">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($ventas as $venta)
                                {{-- Igual que productos: hover suave / fondo rojo si está anulado --}}
                                <tr class="hover:bg-gray-50 {{ $venta->estado === 'Anulado' ? 'hover:bg-gray-50' : '' }}"
                                    x-data="{ open: false }">

                                    {{-- ID --}}
                                    <td class="px-4 py-2 border text-gray-700 font-medium text-center">
                                        {{ $venta->id }}
                                    </td>

                                    {{-- Cliente --}}
                                    <td class="px-4 py-2 border">
                                        {{ $venta->clienteRelacion->nombre ?? '—' }}
                                    </td>

                                    {{-- Producto --}}
                                    <td class="px-4 py-2 border">
                                        {{ $venta->productoRelacion->nombre ?? '—' }}
                                    </td>

                                    {{-- Cantidad (badge como en productos) --}}
                                    <td class="px-4 py-2 border text-center font-semibold text-gray-800">
                                        @php
                                            $cant = $venta->cantidad_productos;

                                            if ($cant == 0) {
                                                $texto = 'Agotado';
                                                $clase = 'bg-red-100 text-red-700';
                                            } elseif ($cant <= 5) {
                                                $texto = $cant;
                                                $clase = 'bg-red-100 text-red-700';
                                            } elseif ($cant <= 10) {
                                                $texto = $cant;
                                                $clase = 'bg-yellow-100 text-yellow-700';
                                            } else {
                                                $texto = $cant;
                                                $clase = 'bg-green-100 text-green-700';
                                            }
                                        @endphp

                                        <span class="px-2 py-1 rounded text-sm font-medium {{ $clase }}">
                                            {{ $texto }}
                                        </span>
                                    </td>

                                    {{-- Precio Unitario --}}
                                    <td class="px-4 py-2 border text-right">
                                        @if ($venta->detalles->first())
                                            Gs.
                                            {{ number_format($venta->detalles->first()->precio_unitario, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    {{-- Total --}}
                                    <td class="px-4 py-2 border text-right font-bold text-blue-700">
                                        Gs. {{ number_format($venta->total, 0, ',', '.') }}
                                    </td>

                                    {{-- Método de pago --}}
                                    <td class="px-4 py-2 border text-center">
                                        <span
                                            class="px-2 py-1 rounded text-sm font-medium
                            @if ($venta->metodo_pago == 'Efectivo') bg-green-100 text-green-700
                            @elseif ($venta->metodo_pago == 'Tarjeta') bg-blue-100 text-blue-700
                            @elseif ($venta->metodo_pago == 'Transferencia') bg-purple-100 text-purple-700
                            @else bg-gray-100 text-gray-600 @endif">
                                            {{ $venta->metodo_pago ?? '—' }}
                                        </span>
                                    </td>

                                    {{-- Estado --}}
                                    <td class="px-4 py-2 border text-center">
                                        @php
                                            $estado = ucfirst($venta->estado ?? 'Desconocido');
                                            $clase = match (strtolower($venta->estado ?? '')) {
                                                'pagado' => 'bg-green-100 text-green-700',
                                                'pendiente' => 'bg-yellow-100 text-yellow-700',
                                                'anulado' => 'bg-red-100 text-red-700',
                                                default => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp

                                        <span class="px-2 py-1 rounded text-sm font-medium {{ $clase }}">
                                            {{ $estado }}
                                        </span>
                                    </td>

                                    {{-- Fecha --}}
                                    <td class="px-4 py-2 border text-sm text-gray-600 text-center">
                                        {{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y') }}
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="px-4 py-2 border text-center">
                                        <div class="flex flex-col items-center gap-2">

                                            {{-- Editar --}}
                                            <a href="{{ route('ventas.edit', $venta->id) }}"
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

                                            {{-- Devolución --}}
                                            @if ($venta->estado === 'Pagado')
                                                <a href="{{ route('ventas.devolucion.form', $venta->id) }}"
                                                    class="text-yellow-500 hover:text-yellow-600 transition"
                                                    title="Registrar devolución">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                        stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M4 4v6h6M20 20v-6h-6M5 19a9 9 0 0014-7V9M19 5a9 9 0 00-14 7v3" />
                                                    </svg>
                                                </a>
                                            @endif

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

                                        {{-- Modal de confirmación (DENTRO del x-data de la fila) --}}
                                        <div x-show="open" x-cloak
                                            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                                            <div class="bg-white rounded-lg shadow-lg p-6 w-96">
                                                <h2 class="text-lg font-semibold text-gray-800">
                                                    ⚠️ Confirmar eliminación
                                                </h2>
                                                <p class="mt-2 text-sm text-gray-600">
                                                    ¿Seguro que quieres eliminar esta venta? Esta acción no se puede
                                                    deshacer.
                                                </p>
                                                <div class="mt-4 flex justify-end gap-3">
                                                    <button @click="open = false"
                                                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">
                                                        ❌ Cancelar
                                                    </button>
                                                    <form action="{{ route('ventas.destroy', $venta->id) }}"
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

                {{-- 🔹 Resumen + paginación --}}
@php
    // Detectamos si $ventas es paginador o colección simple (verTodo)
    if ($ventas instanceof \Illuminate\Pagination\LengthAwarePaginator) {
        $pageCantidadVentas = $ventas->count();          // cuántas se muestran en esta página
        $pageTotalVentas    = $ventas->sum('total');      // suma de los "total" de esta página
        $mostrandoDesde     = $ventas->firstItem();
        $mostrandoHasta     = $ventas->lastItem();
        $totalResultados    = $ventas->total();
    } else {
        // verTodo: colección simple
        $pageCantidadVentas = $ventas->count();          
        $pageTotalVentas    = $ventas->sum('total');      
        $totalResultados    = $ventas->count();

        if ($totalResultados > 0) {
            $mostrandoDesde = 1;
            $mostrandoHasta = $totalResultados;
        } else {
            $mostrandoDesde = 0;
            $mostrandoHasta = 0;
        }
    }

    // Copiar parámetros usando una sola vez (para botones)
    $q = request()->query();
@endphp

<div
    class="mt-4 p-3 bg-gray-50 rounded-lg shadow-sm flex justify-between items-start text-sm text-gray-700">

    {{-- Columna izquierda --}}
    <div class="flex flex-col gap-1">
        <div>
            @if ($totalResultados > 0)
                Mostrando
                <span class="font-bold">{{ $mostrandoDesde }}</span>
                a
                <span class="font-bold">{{ $mostrandoHasta }}</span>
                de
                <span class="font-bold">{{ $totalResultados }}</span>
                resultados
            @else
                Mostrando <span class="font-bold">0</span> resultados
            @endif
        </div>

        <div class="flex items-center gap-1">
            📦 <span>Ventas totales mostradas:
                <span class="font-bold text-gray-800">{{ $pageCantidadVentas }}</span>
            </span>
        </div>

        <div class="flex items-center gap-1">
            💰 <span>Valor total mostrado:
                <span class="font-bold text-blue-700">
                    Gs. {{ number_format($pageTotalVentas, 0, ',', '.') }}
                </span>
            </span>
        </div>
    </div>

    {{-- Columna derecha: paginación / ver todo --}}
    <div class="flex items-center">

        {{-- PAGINACIÓN — solo si NO estamos en verTodo --}}
        @if ($ventas instanceof \Illuminate\Pagination\LengthAwarePaginator)
            @php
                // Evita que Laravel incluya verTodo dentro del paginador
                $paginationQuery = $q;
                unset($paginationQuery['verTodo']);
            @endphp

            {{ $ventas->appends($paginationQuery)->links() }}
        @endif

        {{-- BOTÓN VER PAGINADO (si SÍ está activo) --}}
        @if (request()->has('verTodo'))
            @php
                unset($q['verTodo']);
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