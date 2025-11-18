<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-white">
            Historial de Movimientos
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow rounded-lg p-6">

                {{-- TOOLBAR: BUSCADOR + FILTROS + ACCIONES --}}
<div class="mb-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        {{-- FILTROS / BUSCADOR (izquierda) --}}
        <form method="GET" action="{{ route('historial.index') }}"
              class="flex flex-wrap items-center gap-3">

            {{-- 🔍 BUSCADOR (solo por producto) --}}
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
                <input type="text" name="search" id="searchHistorial"
                       placeholder="Buscar por Producto"
                       value="{{ request('search') }}"
                       class="border rounded pl-9 pr-10 py-1 w-60 md:w-72 focus:ring-2 focus:ring-indigo-500 outline-none"
                       oninput="toggleSearchHistorial(this)">

                <!-- Botón limpiar -->
                <div id="searchHistorial-icons"
                     class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-2 {{ request('search') ? '' : 'hidden' }}">
                    <a href="{{ route('historial.index', request()->except(['search', 'page'])) }}"
                       class="text-red-500 hover:text-red-700 font-bold">×</a>
                </div>
            </div>

            <script>
                function toggleSearchHistorial(input) {
                    const icons = document.getElementById('searchHistorial-icons');
                    if (input.value.trim() !== '') {
                        icons.classList.remove('hidden');
                    } else {
                        icons.classList.add('hidden');
                    }
                }
            </script>

            {{-- 🎯 FILTRO POR ACCIÓN (crear / editar / eliminar) --}}
            @php
                $acciones = [
                    'crear'    => 'Creación',
                    'editar'   => 'Edición',
                    'eliminar' => 'Eliminación',
                ];
                $accionSeleccionada = request('accion');
            @endphp

            <details class="relative border rounded px-2 py-1">
                <summary
                    class="cursor-pointer select-none summary-arrow {{ $accionSeleccionada ? 'text-blue-600 font-bold' : '' }}">
                    @if ($accionSeleccionada && isset($acciones[$accionSeleccionada]))
                        Acción: {{ $acciones[$accionSeleccionada] }}
                        <a href="{{ route('historial.index', request()->except(['accion', 'page'])) }}"
                           class="ml-1 text-red-500 font-bold hover:text-red-700">✕</a>
                    @else
                        Acción
                    @endif
                </summary>

                <div class="absolute bg-white border rounded shadow-md mt-1 w-52 z-10 p-2 space-y-1">
                    @foreach ($acciones as $clave => $label)
                        <label class="flex items-center">
                            {{-- RADIO: solo una acción a la vez --}}
                            <input type="radio" name="accion" value="{{ $clave }}"
                                   {{ $accionSeleccionada === $clave ? 'checked' : '' }}
                                   onchange="this.form.submit()">
                            <span class="ml-2 text-sm">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </details>

           {{-- 📎 ORDENAR (solo fechas) --}}
@php
    $ordenes = [
        'fecha_desc' => 'Fecha (recientes)',
        'fecha_asc'  => 'Fecha (antiguas)',
    ];

    $ordenActual = request('ordenar')
        ? ($ordenes[request('ordenar')] ?? 'Ordenar')
        : 'Ordenar';
@endphp

<details class="relative border rounded px-2 py-1">
    <summary
        class="cursor-pointer select-none summary-arrow {{ request('ordenar') ? 'text-blue-600 font-bold' : '' }}">
        {{ $ordenActual }}

        @if (request('ordenar'))
            <a href="{{ route('historial.index', request()->except(['ordenar', 'page'])) }}"
               class="ml-2 text-red-500 font-bold hover:text-red-700">✕</a>
        @endif
    </summary>

    <div class="absolute bg-white border rounded shadow-md mt-1 w-56 z-10">
        <ul>
            @foreach ($ordenes as $valor => $texto)
                <li>
                    <a href="{{ route('historial.index', array_merge(request()->except(['ordenar', 'page']), ['ordenar' => $valor])) }}"
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
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('historial.export.pdf', request()->all()) }}"
                   class="inline-flex items-center px-3 py-1.5 text-sm rounded bg-red-600 text-white hover:bg-red-700">
                    🧾 Exportar PDF
                </a>

                <a href="{{ route('historial.export.excel', request()->all()) }}"
                   class="inline-flex items-center px-3 py-1.5 text-sm rounded bg-green-600 text-white hover:bg-green-700">
                    📊 Exportar Excel
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Script AJAX para búsqueda dinámica en HISTORIAL --}}
<script>
    const historialBusquedaUrl = "{{ route('historial.busqueda') }}";

    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('searchHistorial');
        const tabla = document.querySelector('table tbody');
        let timeout = null;

        input.addEventListener('input', function () {
            clearTimeout(timeout);
            const valor = this.value.trim();

            timeout = setTimeout(() => {

                // Si se borra todo → volver al index normal
                if (valor === '') {
                    window.location.href = "{{ route('historial.index') }}";
                    return;
                }

                // Mensaje "Buscando..."
                tabla.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center py-4 text-gray-400">
                            Buscando...
                        </td>
                    </tr>`;

                fetch(historialBusquedaUrl + "?search=" + encodeURIComponent(valor))
                    .then(res => res.json())
                    .then(data => {
                        tabla.innerHTML = '';

                        if (data.length === 0) {
                            tabla.innerHTML = `
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-gray-500">
                                        No se encontraron registros
                                    </td>
                                </tr>`;
                            return;
                        }

                        data.forEach((h) => {

                            // Producto
                            const producto = h.producto
                                ? h.producto.nombre
                                : 'N/A';

                            // Acción → badge + colores
                            let accionLabel = 'Otro';
                            let accionClass = 'bg-gray-100 text-gray-700';

                            switch (h.accion) {
                                case 'crear':
                                    accionLabel = 'Creación';
                                    accionClass = 'bg-green-100 text-green-700';
                                    break;
                                case 'editar':
                                    accionLabel = 'Edición';
                                    accionClass = 'bg-yellow-100 text-yellow-700';
                                    break;
                                case 'eliminar':
                                    accionLabel = 'Eliminación';
                                    accionClass = 'bg-red-100 text-red-700';
                                    break;
                            }

                            // Fecha
                            const fecha = h.created_at
                                ? new Date(h.created_at).toLocaleString('es-PY')
                                : '—';

                            tabla.innerHTML += `
<tr class="hover:bg-gray-50">

    <!-- ID -->
    <td class="border px-4 py-2 text-center text-xs text-gray-500">
        #${h.id}
    </td>

    <!-- Producto -->
    <td class="border px-4 py-2">
        <div class="text-sm font-semibold text-gray-800">
            ${producto}
        </div>
    </td>

    <!-- Acción -->
    <td class="border px-4 py-2 text-center">
        <span class="px-2 py-1 rounded-full text-xs font-semibold ${accionClass}">
            ${accionLabel}
        </span>
    </td>

    <!-- Descripción -->
    <td class="border px-4 py-2 text-sm">
        ${h.descripcion ?? '—'}
    </td>

    <!-- Fecha -->
    <td class="border px-4 py-2 text-center text-sm text-gray-600">
        ${fecha}
    </td>

</tr>`;
                        });
                    })
                    .catch(err => console.error("Error en búsqueda historial:", err));
            }, 300);
        });
    });
</script>

                {{-- TABLA --}}
<div class="overflow-x-auto">
    <table class="min-w-full border border-gray-200 rounded text-sm">
        <thead class="bg-gray-100">
            <tr class="text-xs uppercase tracking-wide text-gray-600">
                <th class="border px-4 py-2 text-center">ID</th>
                <th class="border px-4 py-2">Producto</th>
                <th class="border px-4 py-2 text-center">Acción</th>
                <th class="border px-4 py-2 text-center">Usuario</th>
                <th class="border px-4 py-2">Descripción</th>
                <th class="border px-4 py-2 text-center">Fecha</th>
            </tr>
        </thead>

        <tbody>
            @forelse($historiales as $item)
                @php
                    // Hover por acción
                    $rowHover = match ($item->accion) {
                        'crear'    => 'hover:bg-green-50',
                        'editar'   => 'hover:bg-yellow-50',
                        'eliminar' => 'hover:bg-red-50',
                        default    => 'hover:bg-gray-50',
                    };

                    // Badge de acción
                    $accionClass = match ($item->accion) {
                        'crear'    => 'bg-green-100 text-green-700',
                        'editar'   => 'bg-yellow-100 text-yellow-700',
                        'eliminar' => 'bg-red-100 text-red-700',
                        default    => 'bg-gray-100 text-gray-700',
                    };

                    $accionLabel = $labelAcciones[$item->accion] ?? ucfirst($item->accion);
                @endphp

                <tr class="{{ $rowHover }}">

                    {{-- ID --}}
                    <td class="border px-4 py-2 text-center text-xs text-gray-500">
                        #{{ $item->id }}
                    </td>

                    {{-- PRODUCTO --}}
                    <td class="border px-4 py-2">
                        <div class="text-sm font-semibold text-gray-800">
                            @if ($item->producto)
                                {{ $item->producto->nombre }}
                                @if ($item->producto->deleted_at)
                                    <span class="text-xs text-red-500">(eliminado)</span>
                                @endif
                            @else
                                <span class="text-red-500">N/A</span>
                            @endif
                        </div>
                    </td>

                    {{-- ACCIÓN --}}
                    <td class="border px-4 py-2 text-center">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $accionClass }}">
                            {{ $accionLabel }}
                        </span>
                    </td>

                    {{-- USUARIO --}}
<td class="border px-4 py-2 text-center text-sm text-gray-800">
    {{ $item->usuario->name ?? 'Admin' }}
</td>

                    {{-- DESCRIPCIÓN --}}
                    <td class="border px-4 py-2 text-sm">
                        {{ $item->descripcion }}
                    </td>

                    {{-- FECHA --}}
                    <td class="border px-4 py-2 text-center text-sm text-gray-600">
                        {{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '—' }}
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-gray-500">
                        No hay registros en el historial.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

                {{-- Resumen + paginación --}}
<div
    class="mt-4 p-3 bg-gray-50 rounded-lg shadow-sm flex justify-between items-start text-sm text-gray-700">

    {{-- Columna izquierda: info de la página actual --}}
    <div class="flex flex-col gap-1">
        <div>
            Mostrando
            <span class="font-bold">{{ $historiales->firstItem() }}</span>
            a
            <span class="font-bold">{{ $historiales->lastItem() }}</span>
            de
            <span class="font-bold">{{ $historiales->total() }}</span>
            registros
        </div>

        <div class="flex items-center gap-1">
            📜
            <span>
                Registros en esta vista:
                <span class="font-bold">{{ $historiales->count() }}</span>
            </span>
        </div>
    </div>

    {{-- Columna derecha: paginación / ver todo / ver paginado --}}
    <div class="flex items-center">

        @if (!$verTodo)
            {{-- Links de paginación normales --}}
            {{ $historiales->links() }}

            {{-- Botón "Ver todo" --}}
            @php
                $q = request()->query();
                $q['verTodo'] = 1;
                $urlVerTodo = request()->url() . '?' . http_build_query($q);
            @endphp

        @else
            {{-- En modo "ver todo" solo botón "Ver paginado" --}}
            @php
                $q = request()->query();
                unset($q['verTodo']);
                $urlSinVerTodo = request()->url() . (empty($q) ? '' : '?' . http_build_query($q));
            @endphp

            <a href="{{ $urlSinVerTodo }}"
               class="relative inline-flex items-center px-3 py-2 text-sm font-medium 
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
        </div>
    </div>
</x-app-layout>