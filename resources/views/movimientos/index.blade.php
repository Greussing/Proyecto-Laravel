<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-white">
            Movimientos de Stock
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow rounded-lg p-6">

                {{-- TOOLBAR: FILTROS + ACCIONES --}}
                <div class="mb-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                        {{-- FILTROS (izquierda) --}}
                        <form method="GET" action="{{ route('movimientos.index') }}"
                              class="flex flex-wrap items-center gap-3">

                            {{-- BUSCADOR --}}
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
    <input type="text" name="search" id="searchMovimientos"
        placeholder="Buscar por Producto"
        value="{{ request('search') }}"
        class="border rounded pl-9 pr-10 py-1 w-60 md:w-72 focus:ring-2 focus:ring-blue-500 outline-none"
        oninput="toggleSearchMovimientos(this)">

    <!-- Botón limpiar -->
    <div id="searchMovimientos-icons"
        class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-2 {{ request('search') ? '' : 'hidden' }}">
        <a href="{{ route('movimientos.index', request()->except(['search', 'page'])) }}"
            class="text-red-500 hover:text-red-700 font-bold">×</a>
    </div>
</div>

{{-- Script AJAX para búsqueda dinámica en MOVIMIENTOS --}}
<script>
    const movimientosBusquedaUrl = "{{ route('movimientos.busqueda') }}";

    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('searchMovimientos');
        const tabla = document.querySelector('table tbody');
        let timeout = null;

        if (!input || !tabla) return;

        input.addEventListener('input', function () {
            clearTimeout(timeout);
            const valor = this.value.trim();

            timeout = setTimeout(() => {

                // Si se borra todo → volver al index normal
                if (valor === '') {
                    window.location.href = "{{ route('movimientos.index') }}";
                    return;
                }

                // Mensaje "Buscando..."
                tabla.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center py-4 text-gray-400">
                            Buscando...
                        </td>
                    </tr>`;

                // Mantener otros filtros (tipo, ordenar, etc.)
                const params = new URLSearchParams(window.location.search);
                params.set('search', valor);

                fetch(movimientosBusquedaUrl + "?" + params.toString())
                    .then(res => res.json())
                    .then(data => {
                        tabla.innerHTML = '';

                        if (data.length === 0) {
                            tabla.innerHTML = `
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-gray-500">
                                        No se encontraron movimientos
                                    </td>
                                </tr>`;
                            return;
                        }

                        data.forEach((m) => {

                            // Cliente
                            const cliente = m.cliente_relacion
                                ? m.cliente_relacion.nombre
                                : 'N/A';

                            // Usuario
                            const usuario = m.usuario
                                ? m.usuario.name
                                : '_';

                            // Producto
                            const producto = m.producto
                                ? m.producto.nombre
                                : 'N/A';

                            // Label del tipo
                            const labelTipos = {
                                venta: 'Venta',
                                devolucion: 'Devolución',
                                anulacion: 'Eliminación',
                                edicion: 'Edición',
                            };

                            // Hover por tipo (misma lógica que en Blade)
                            let rowHover = 'hover:bg-gray-50';
                            switch (m.tipo) {
                                case 'venta':
                                    rowHover = 'hover:bg-green-50';
                                    break;
                                case 'devolucion':
                                    rowHover = 'hover:bg-blue-50';
                                    break;
                                case 'anulacion':
                                    rowHover = 'hover:bg-red-50';
                                    break;
                                case 'edicion':
                                    rowHover = 'hover:bg-yellow-50';
                                    break;
                            }

                            // Badge de tipo
                            let tipoClass = 'bg-gray-100 text-gray-700';
                            switch (m.tipo) {
                                case 'venta':
                                    tipoClass = 'bg-green-100 text-green-700';
                                    break;
                                case 'devolucion':
                                    tipoClass = 'bg-blue-100 text-blue-700';
                                    break;
                                case 'anulacion':
                                    tipoClass = 'bg-red-100 text-red-700';
                                    break;
                                case 'edicion':
                                    tipoClass = 'bg-yellow-100 text-yellow-700';
                                    break;
                            }

                            // Cantidad badge
                            let cantClass = 'bg-gray-100 text-gray-700';
                            if (m.cantidad < 0) {
                                cantClass = 'bg-red-100 text-red-700';
                            } else if (m.cantidad > 0) {
                                cantClass = 'bg-green-100 text-green-700';
                            }

                            // Fecha
                            const fecha = m.created_at
                                ? new Date(m.created_at).toLocaleString('es-PY')
                                : '—';

                            tabla.innerHTML += `
<tr class="${rowHover}">

    <!-- ID -->
    <td class="border px-4 py-2 text-center text-xs text-gray-500">
        #${m.id}
    </td>

    <!-- CLIENTE -->
    <td class="border px-4 py-2">
        <div class="text-sm font-semibold text-gray-800">
            ${cliente}
        </div>
    </td>

    <!-- USUARIO -->
    <td class="border px-4 py-2">
        <div class="text-sm text-gray-800">
            ${usuario}
        </div>
    </td>

    <!-- PRODUCTO -->
    <td class="border px-4 py-2 text-gray-800 text-sm">
        ${producto}
    </td>

    <!-- TIPO -->
    <td class="border px-4 py-2 text-center">
        <span class="px-2 py-1 rounded-full text-xs font-semibold ${tipoClass}">
            ${labelTipos[m.tipo] ?? (m.tipo ? m.tipo.charAt(0).toUpperCase() + m.tipo.slice(1) : '—')}
        </span>
    </td>

    <!-- CANTIDAD -->
    <td class="border px-4 py-2 text-center">
        <span class="px-2 py-1 rounded text-xs font-semibold ${cantClass}">
            ${m.cantidad ?? 0}
        </span>
    </td>

    <!-- STOCK ANTES -->
    <td class="border px-4 py-2 text-center text-sm">
        ${m.stock_antes ?? '—'}
    </td>

    <!-- STOCK DESPUÉS -->
    <td class="border px-4 py-2 text-center text-sm font-bold">
        ${m.stock_despues ?? '—'}
    </td>

    <!-- DETALLE -->
    <td class="border px-4 py-2 text-sm">
        ${m.detalle ?? '—'}
    </td>

    <!-- FECHA -->
    <td class="border px-4 py-2 text-center text-sm text-gray-600">
        ${fecha}
    </td>

</tr>`;
                        });
                    })
                    .catch(err => console.error("Error en búsqueda movimientos:", err));
            }, 300);
        });
    });
</script>

                            {{-- FILTRO POR TIPO (una sola opción a la vez) --}}
                            @php
                                $tipos = [
                                    'venta'      => 'Venta',
                                    'devolucion' => 'Devolución',
                                    'anulacion'  => 'Eliminación',
                                    'edicion'    => 'Edición',
                                ];

                                // ahora esperamos un string tipo=venta (no tipo[]=)
                                $tipoSeleccionado = request('tipo');
                            @endphp

                            <details class="relative border rounded px-2 py-1">
                                <summary
                                    class="cursor-pointer select-none summary-arrow {{ $tipoSeleccionado ? 'text-blue-600 font-bold' : '' }}">
                                    @if ($tipoSeleccionado && isset($tipos[$tipoSeleccionado]))
                                        Tipo: {{ $tipos[$tipoSeleccionado] }}
                                        <a href="{{ route('movimientos.index', request()->except(['tipo', 'page'])) }}"
                                            class="ml-2 text-red-500 font-bold hover:text-red-700">✕</a>
                                    @else
                                        Tipo
                                    @endif
                                </summary>

                                <div class="absolute bg-white border rounded shadow-md mt-1 p-2 w-52 z-10 space-y-1">
                                    @foreach ($tipos as $clave => $label)
                                        <label class="flex items-center">
                                            {{-- RADIO: solo una opción --}}
                                            <input type="radio" name="tipo" value="{{ $clave }}"
                                                {{ $tipoSeleccionado === $clave ? 'checked' : '' }}
                                                onchange="this.form.submit()">
                                            <span class="ml-2">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </details>

                            {{-- ORDENAR --}}
                            @php
                                $ordenes = [
                                    'fecha_desc'    => 'Fecha (recientes)',
                                    'fecha_asc'     => 'Fecha (antiguas)',
                                    'cantidad_desc' => 'Cantidad (mayor)',
                                    'cantidad_asc'  => 'Cantidad (menor)',
                                ];
                                $ordenActual = request('ordenar') ? $ordenes[request('ordenar')] ?? 'Ordenar' : 'Ordenar';
                            @endphp

                            <details class="relative border rounded px-2 py-1">
                                <summary
                                    class="cursor-pointer select-none summary-arrow {{ request('ordenar') ? 'text-blue-600 font-bold' : '' }}">
                                    {{ $ordenActual }}
                                    @if (request('ordenar'))
                                        <a href="{{ route('movimientos.index', request()->except(['ordenar', 'page'])) }}"
                                            class="ml-2 text-red-500 font-bold hover:text-red-700">✕</a>
                                    @endif
                                </summary>
                                <div class="absolute bg-white border rounded shadow-md mt-1 w-52 z-10">
                                    <ul>
                                        @foreach ($ordenes as $valor => $texto)
                                            <li>
                                                <a href="{{ route('movimientos.index', array_merge(request()->except(['ordenar', 'page']), ['ordenar' => $valor])) }}"
                                                    class="block px-3 py-1 hover:bg-gray-100">
                                                    {{ $texto }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </details>
                        </form>

                        {{-- ACCIONES (derecha) --}}
                        <div class="flex flex-col items-end gap-1">
                            <div class="text-sm text-gray-500">
                                Total de movimientos: {{ $movimientos->total() }}
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('movimientos.export.pdf', request()->all()) }}"
                                    class="inline-flex items-center px-3 py-1.5 text-sm rounded bg-red-600 text-white hover:bg-red-700">
                                    🧾 Exportar PDF
                                </a>

                                <a href="{{ route('movimientos.export.excel', request()->all()) }}"
                                    class="inline-flex items-center px-3 py-1.5 text-sm rounded bg-green-600 text-white hover:bg-green-700">
                                    📊 Exportar Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $labelTipos = [
                        'venta'      => 'Venta',
                        'devolucion' => 'Devolución',
                        'anulacion'  => 'Eliminación',
                        'edicion'    => 'Edición',
                    ];
                @endphp

                {{-- TABLA --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 rounded text-sm">
                        <thead class="bg-gray-100">
                            <tr class="text-xs uppercase tracking-wide text-gray-600">
                                <th class="px-4 py-2 border text-center">ID</th>
                                <th class="px-4 py-2 border">Cliente</th>
                                <th class="px-4 py-2 border">Usuario</th>
                                <th class="px-4 py-2 border">Producto</th>
                                <th class="px-4 py-2 border text-center">Tipo</th>
                                <th class="px-4 py-2 border text-center">Cantidad</th>
                                <th class="px-4 py-2 border text-center">Stock Antes</th>
                                <th class="px-4 py-2 border text-center">Stock Después</th>
                                <th class="px-4 py-2 border">Detalle</th>
                                <th class="px-4 py-2 border text-center">Fecha</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($movimientos as $m)
                                @php
                                    // Hover color por tipo
                                    $rowHover = match ($m->tipo) {
                                        'venta'      => 'hover:bg-green-50',
                                        'devolucion' => 'hover:bg-blue-50',
                                        'anulacion'  => 'hover:bg-red-50',
                                        'edicion'    => 'hover:bg-yellow-50',
                                        default      => 'hover:bg-gray-50',
                                    };

                                    // Badge de tipo
                                    $tipoClass = match ($m->tipo) {
                                        'venta'      => 'bg-green-100 text-green-700',
                                        'devolucion' => 'bg-blue-100 text-blue-700',
                                        'anulacion'  => 'bg-red-100 text-red-700',
                                        'edicion'    => 'bg-yellow-100 text-yellow-700',
                                        default      => 'bg-gray-100 text-gray-700',
                                    };

                                    // Cantidad badge
                                    $cantClass = $m->cantidad < 0
                                        ? 'bg-red-100 text-red-700'
                                        : ($m->cantidad > 0
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-gray-100 text-gray-700');
                                @endphp

                                <tr class="{{ $rowHover }}">

                                    {{-- ID --}}
                                    <td class="border px-4 py-2 text-center text-xs text-gray-500">
                                        #{{ $m->id }}
                                    </td>

                                    {{-- CLIENTE --}}
                                    <td class="border px-4 py-2">
                                        <div class="text-sm font-semibold text-gray-800">
                                            {{ $m->clienteRelacion->nombre ?? 'N/A' }}
                                        </div>
                                    </td>

                                    {{-- USUARIO --}}
                                    <td class="border px-4 py-2">
                                        <div class="text-sm text-gray-800">
                                            {{ $m->usuario->name ?? '_' }}
                                        </div>
                                    </td>

                                    {{-- PRODUCTO --}}
                                    <td class="border px-4 py-2 text-gray-800 text-sm">
                                        {{ $m->producto->nombre ?? 'N/A' }}
                                    </td>

                                    {{-- TIPO --}}
                                    <td class="border px-4 py-2 text-center">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $tipoClass }}">
                                            {{ $labelTipos[$m->tipo] ?? ucfirst($m->tipo) }}
                                        </span>
                                    </td>

                                    {{-- CANTIDAD --}}
                                    <td class="border px-4 py-2 text-center">
                                        <span class="px-2 py-1 rounded text-xs font-semibold {{ $cantClass }}">
                                            {{ $m->cantidad }}
                                        </span>
                                    </td>

                                    {{-- STOCK ANTES --}}
                                    <td class="border px-4 py-2 text-center text-sm">
                                        {{ $m->stock_antes }}
                                    </td>

                                    {{-- STOCK DESPUÉS --}}
                                    <td class="border px-4 py-2 text-center text-sm font-bold">
                                        {{ $m->stock_despues }}
                                    </td>

                                    {{-- DETALLE --}}
                                    <td class="border px-4 py-2 text-sm">
                                        {{ $m->detalle }}
                                    </td>

                                    {{-- FECHA --}}
                                    <td class="border px-4 py-2 text-center text-sm text-gray-600">
                                        {{ $m->created_at->format('d/m/Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- PAGINACIÓN + VER PAGINADO --}}
                <div class="mt-4 flex items-center">
                    {{ $movimientos->links() }}

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
        </div>
    </div>
</x-app-layout>