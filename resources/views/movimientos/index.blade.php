<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Movimientos de Stock') }}
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
                {{-- Contenedor filtros + botones exportar --}}
                <div class="flex items-center justify-between mb-6 flex-wrap gap-4 w-full">

                    {{-- Filtros de búsqueda --}}
                    <form method="GET" action="{{ route('movimientos.index') }}" class="flex flex-wrap gap-2 items-center">

                        {{-- Buscar por producto --}}
                        <div class="relative">
                            <button type="submit" class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
                                </svg>
                            </button>

                            <input type="text" name="search" placeholder="Buscar por Producto"
                                value="{{ request('search') }}"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm pl-9 pr-8 py-2 w-60 md:w-72"
                                oninput="toggleSearchIcons(this)">
                            
                            <div id="search-icons" class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-2 {{ request('search') ? '' : 'hidden' }}">
                                <a href="{{ route('movimientos.index', request()->except(['search', 'page'])) }}" class="text-red-500 hover:text-red-700 font-bold">×</a>
                            </div>
                        </div>

                        {{-- Script búsqueda dinámica (AJAX) adaptado para Movimientos --}}
                        <script>
                            const busquedaUrl = "{{ route('movimientos.busqueda') }}";
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

                                if(input && tablaBody) {
                                    input.addEventListener('input', function() {
                                        clearTimeout(timeout);
                                        const valor = this.value.trim();

                                        timeout = setTimeout(() => {
                                            if (valor === '') {
                                                window.location.reload();
                                                return;
                                            }

                                            tablaBody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-gray-400">Buscando...</td></tr>`;

                                            // Mantener otros filtros
                                            const params = new URLSearchParams(window.location.search);
                                            params.set('search', valor);

                                            fetch(busquedaUrl + "?" + params.toString())
                                                .then(res => res.json())
                                                .then(data => {
                                                    tablaBody.innerHTML = '';
                                                    if (data.length === 0) {
                                                        tablaBody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-gray-500">No se encontraron movimientos</td></tr>`;
                                                        return;
                                                    }
                                                    data.forEach((m) => {
                                                        // Helpers para badges y textos
                                                        const labelTipos = {
                                                            venta: 'Venta',
                                                            devolucion: 'Devolución',
                                                            anulacion: 'Eliminación',
                                                            edicion: 'Edición',
                                                        };
                                                        const tipoLabel = labelTipos[m.tipo] ?? (m.tipo ? m.tipo.charAt(0).toUpperCase() + m.tipo.slice(1) : '—');
                                                        
                                                        let tipoBadge = 'gray';
                                                        switch (m.tipo) {
                                                            case 'venta': tipoBadge = 'success'; break;
                                                            case 'devolucion': tipoBadge = 'info'; break;
                                                            case 'anulacion': tipoBadge = 'danger'; break;
                                                            case 'edicion': tipoBadge = 'warning'; break;
                                                        }

                                                        let cantBadge = 'gray';
                                                        if (m.cantidad < 0) cantBadge = 'danger';
                                                        else if (m.cantidad > 0) cantBadge = 'success';

                                                        const fecha = m.created_at ? new Date(m.created_at).toLocaleDateString('es-PY') + ' ' + new Date(m.created_at).toLocaleTimeString('es-PY', {hour: '2-digit', minute:'2-digit'}) : '—';
                                                        const cliente = m.cliente_relacion ? m.cliente_relacion.nombre : 'N/A';
                                                        const usuario = m.usuario ? m.usuario.name : '_';
                                                        const producto = m.producto ? m.producto.nombre : 'N/A';

                                                        // Badge component simulation for JS
                                                        const renderBadge = (type, text) => {
                                                            const colors = {
                                                                success: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                                                danger: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                                                warning: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                                                info: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                                                gray: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                                            };
                                                            return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colors[type] || colors.gray}">${text}</span>`;
                                                        };

                                                        tablaBody.innerHTML += `
                                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 text-xs md:text-sm">
                                                                <td class="px-3 py-3 text-center">${m.id}</td>
                                                                <td class="px-3 py-3 text-center break-words max-w-[140px]">${cliente}</td>
                                                                <td class="px-3 py-3 text-center break-words max-w-[120px]">${usuario}</td>
                                                                <td class="px-3 py-3 text-center break-words max-w-[140px]">${producto}</td>
                                                                <td class="px-3 py-3 text-center">${renderBadge(tipoBadge, tipoLabel)}</td>
                                                                <td class="px-3 py-3 text-center">${renderBadge(cantBadge, m.cantidad)}</td>
                                                                <td class="px-3 py-3 text-center">${m.stock_antes ?? '—'}</td>
                                                                <td class="px-3 py-3 text-center">${m.stock_despues ?? '—'}</td>
                                                                <td class="px-3 py-3 text-left break-words max-w-[200px]">${m.detalle ?? '—'}</td>
                                                                <td class="px-3 py-3 text-center whitespace-nowrap">${fecha}</td>
                                                            </tr>`;
                                                    });
                                                })
                                                .catch(err => console.error('Error en búsqueda:', err));
                                        }, 300);
                                    });
                                }
                            });
                        </script>

                        {{-- Filtro Tipo --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false" type="button" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                @if (request('tipo'))
                                    @php
                                        $tiposLabel = [
                                            'venta' => 'Venta',
                                            'devolucion' => 'Devolución',
                                            'anulacion' => 'Eliminación',
                                            'edicion' => 'Edición'
                                        ];
                                        $currentTipo = $tiposLabel[request('tipo')] ?? request('tipo');
                                    @endphp
                                    <span class="text-blue-600 dark:text-blue-400">Tipo: {{ $currentTipo }}</span>
                                @else
                                    Tipo
                                @endif
                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="open" class="absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 p-2">
                                @php
                                    $tipos = [
                                        'venta'      => 'Venta',
                                        'devolucion' => 'Devolución',
                                        'anulacion'  => 'Eliminación',
                                        'edicion'    => 'Edición',
                                    ];
                                @endphp
                                @foreach ($tipos as $key => $label)
                                    <label class="flex items-center p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded cursor-pointer">
                                        <input type="radio" name="tipo" value="{{ $key }}"
                                            {{ request('tipo') === $key ? 'checked' : '' }}
                                            onchange="this.form.submit()"
                                            class="rounded-full border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">{{ $label }}</span>
                                    </label>
                                @endforeach
                                @if (request('tipo'))
                                    <div class="border-t border-gray-200 dark:border-gray-600 mt-2 pt-2">
                                        <a href="{{ route('movimientos.index', request()->except(['tipo', 'page'])) }}" class="block text-center text-xs text-red-500 hover:text-red-700">Limpiar filtro</a>
                                    </div>
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
                            <div x-show="open" class="absolute z-50 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 py-1">
                                @php
                                    $opciones = [
                                        'fecha_desc'    => 'Fecha (recientes)',
                                        'fecha_asc'     => 'Fecha (antiguas)',
                                        'cantidad_desc' => 'Cantidad (mayor)',
                                        'cantidad_asc'  => 'Cantidad (menor)',
                                    ];
                                @endphp
                                @foreach ($opciones as $key => $label)
                                    <a href="{{ route('movimientos.index', array_merge(request()->except(['ordenar', 'page']), ['ordenar' => $key])) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 {{ request('ordenar') == $key ? 'bg-gray-100 dark:bg-gray-600 font-bold' : '' }}">
                                        {{ $label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                    </form>

                    {{-- Botones Exportar --}}
                    <div class="flex gap-2">
                        <a href="{{ route('movimientos.export.pdf', request()->all()) }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            PDF
                        </a>
                        <a href="{{ route('movimientos.export.excel', request()->all()) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Excel
                        </a>
                    </div>
                </div>

                {{-- Tabla de Movimientos --}}
                @if ($movimientos->isEmpty())
                    <p class="p-4 text-center text-gray-500 dark:text-gray-400">
                        No hay movimientos registrados.
                    </p>
                @else
                    <div class="overflow-x-auto">
                        <x-table :headers="['ID', 'Cliente', 'Usuario', 'Producto', 'Tipo', 'Cantidad', 'Stock Antes', 'Stock Después', 'Detalle', 'Fecha']">
                            @foreach ($movimientos as $m)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 text-xs md:text-sm">
                                    <td class="px-3 py-3 text-center">
                                        {{ $m->id }}
                                    </td>
                                    <td class="px-3 py-3 text-center break-words max-w-[140px]">
                                        {{ $m->clienteRelacion->nombre ?? 'N/A' }}
                                    </td>
                                    <td class="px-3 py-3 text-center break-words max-w-[120px]">
                                        {{ $m->usuario->name ?? '_' }}
                                    </td>
                                    <td class="px-3 py-3 text-center break-words max-w-[140px]">
                                        {{ $m->producto->nombre ?? 'N/A' }}
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        @php
                                            $tipoLabel = match ($m->tipo) {
                                                'venta'      => 'Venta',
                                                'devolucion' => 'Devolución',
                                                'anulacion'  => 'Eliminación',
                                                'edicion'    => 'Edición',
                                                default      => ucfirst($m->tipo),
                                            };
                                            $tipoBadge = match ($m->tipo) {
                                                'venta'      => 'success',
                                                'devolucion' => 'info',
                                                'anulacion'  => 'danger',
                                                'edicion'    => 'warning',
                                                default      => 'gray',
                                            };
                                        @endphp
                                        <x-badge :type="$tipoBadge">{{ $tipoLabel }}</x-badge>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        @php
                                            $cantBadge = 'gray';
                                            if ($m->cantidad < 0) $cantBadge = 'danger';
                                            elseif ($m->cantidad > 0) $cantBadge = 'success';
                                        @endphp
                                        <x-badge :type="$cantBadge">{{ $m->cantidad }}</x-badge>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        {{ $m->stock_antes ?? '—' }}
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        {{ $m->stock_despues ?? '—' }}
                                    </td>
                                    <td class="px-3 py-3 text-left break-words max-w-[200px]">
                                        {{ $m->detalle ?? '—' }}
                                    </td>
                                    <td class="px-3 py-3 text-center whitespace-nowrap">
                                        {{ $m->created_at->format('d/m/Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </x-table>
                    </div>

                    {{-- Paginación --}}
                    <div class="mt-4">
                        {{ $movimientos->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>