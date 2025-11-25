<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Gestión de Clientes') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Mensajes --}}
            @if (session('success'))
                <div class="mb-4 text-green-700 bg-green-100 dark:bg-green-900 dark:text-green-300 p-4 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 text-red-700 bg-red-100 dark:bg-red-900 dark:text-red-300 p-4 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <x-card>
                {{-- Resumen con Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    {{-- Total Clientes --}}
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                        <div class="text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase">
                            Total Clientes
                        </div>
                        <div class="mt-2 text-2xl font-bold text-blue-700 dark:text-blue-200">
                            {{ $totalClientes }}
                        </div>
                    </div>

                    {{-- Nuevos Clientes --}}
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4">
                        <div class="text-xs font-semibold text-green-700 dark:text-green-300 uppercase">
                            Nuevos (Este Mes)
                        </div>
                        <div class="mt-2 text-2xl font-bold text-green-700 dark:text-green-200">
                            {{ $nuevosClientes }}
                        </div>
                    </div>

                    {{-- Mejor Cliente --}}
                    <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-lg p-4">
                        <div class="text-xs font-semibold text-purple-700 dark:text-purple-300 uppercase">
                            Mejor Cliente
                        </div>
                        <div class="mt-2 text-lg font-semibold text-purple-800 dark:text-purple-100 truncate">
                            @if($mejorCliente)
                                {{ $mejorCliente->nombre }}
                                <div class="text-xs text-purple-600 dark:text-purple-400 font-normal">
                                    Gs. {{ number_format($mejorCliente->ventas_sum_total, 0, ',', '.') }}
                                </div>
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Filtros + Botones --}}
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-3 w-full">
                    
                    {{-- Filtros de búsqueda --}}
                    <form method="GET" action="{{ route('clientes.index') }}" class="flex flex-wrap md:flex-nowrap gap-2 items-center flex-1 min-w-0">
                        
                        {{-- Buscar --}}
                        <div class="relative">
                            <button type="submit" class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
                                </svg>
                            </button>

                            <input type="text" name="search" id="searchClientes" placeholder="Buscar Cliente..." value="{{ request('search') }}"
                                   class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm pl-9 pr-8 py-2 w-52 md:w-60 lg:w-72"
                                   oninput="toggleSearchIcons(this)">

                            <div id="search-icons" class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-2 {{ request('search') ? '' : 'hidden' }}">
                                <a href="{{ route('clientes.index', request()->except(['search', 'page'])) }}" class="text-red-500 hover:text-red-700 font-bold">×</a>
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
                            <div x-show="open" class="absolute z-50 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 py-1" style="display: none;">
                                @php
                                    $opciones = [
                                        'recientes' => 'Más Recientes',
                                        'antiguos' => 'Más Antiguos',
                                        'mayor_gasto' => 'Mayor Gasto',
                                        'menor_gasto' => 'Menor Gasto',
                                    ];
                                @endphp
                                @foreach($opciones as $key => $label)
                                    <a href="{{ route('clientes.index', array_merge(request()->except(['ordenar', 'page']), ['ordenar' => $key])) }}" 
                                       class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 {{ request('ordenar') == $key ? 'bg-gray-100 dark:bg-gray-600 font-bold' : '' }}">
                                        {{ $label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </form>

                    {{-- Botones de Exportación y Crear --}}
                    <div class="flex items-center gap-2 flex-shrink-0">
                        {{-- Exportar PDF --}}
                        <a href="{{ route('clientes.export.pdf', request()->all()) }}" class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md font-semibold text-[11px] md:text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            PDF
                        </a>

                        {{-- Exportar Excel --}}
                        <a href="{{ route('clientes.export.excel', request()->all()) }}" class="inline-flex items-center px-3 py-1.5 bg-green-600 border border-transparent rounded-md font-semibold text-[11px] md:text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Excel
                        </a>

                        {{-- Crear Cliente --}}
                        <a href="{{ route('clientes.create') }}" class="inline-flex items-center justify-center h-8 w-8 md:h-9 md:w-9 bg-blue-600 hover:bg-blue-700 text-white rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition" title="Nuevo Cliente">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Script búsqueda dinámica (AJAX) --}}
                <script>
                    const busquedaUrl = "{{ route('clientes.busqueda') }}";

                    function toggleSearchIcons(input) {
                        const icons = document.getElementById('search-icons');
                        if (input.value.trim() !== '') {
                            icons.classList.remove('hidden');
                        } else {
                            icons.classList.add('hidden');
                        }
                    }

                    document.addEventListener('DOMContentLoaded', () => {
                        const input = document.getElementById('searchClientes');
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
                                                tablaBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-gray-500">No se encontraron clientes</td></tr>`;
                                                return;
                                            }

                                            data.forEach((cliente) => {
                                                // Calcular estado
                                                let estado = 'Inactivo';
                                                let estadoBadge = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                                
                                                if (cliente.ventas_max_fecha) {
                                                    const ultimaCompra = new Date(cliente.ventas_max_fecha);
                                                    const hoy = new Date();
                                                    const diffTime = Math.abs(hoy - ultimaCompra);
                                                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
                                                    
                                                    if (diffDays <= 90) {
                                                        estado = 'Activo';
                                                        estadoBadge = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
                                                    }
                                                }

                                                const ultimaCompraStr = cliente.ventas_max_fecha 
                                                    ? new Date(cliente.ventas_max_fecha).toLocaleDateString('es-PY') 
                                                    : '—';

                                                tablaBody.innerHTML += `
                                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">${cliente.id}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-900 dark:text-white">${cliente.nombre}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                            <div class="text-xs text-gray-500">${cliente.email || ''}</div>
                                                            <div>${cliente.telefono || ''}</div>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-white">
                                                            Gs. ${Number(cliente.ventas_sum_total || 0).toLocaleString('es-PY')}
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                            ${ultimaCompraStr}
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${estadoBadge}">
                                                                ${estado}
                                                            </span>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                            <div class="flex justify-center gap-3">
                                                                <a href="/clientes/${cliente.id}/edit" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Editar">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                    </svg>
                                                                </a>
                                                            </div>
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

                {{-- Tabla de clientes --}}
                @if ($clientes->isEmpty())
                    <p class="p-4 text-center text-gray-500 dark:text-gray-400">
                        No hay clientes registrados.
                    </p>
                @else
                    <x-table :headers="['ID', 'Nombre', 'Contacto', 'Total Gastado', 'Última Compra', 'Estado', 'Acciones']">
                        @foreach ($clientes as $cliente)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200" x-data="{ open: false }">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">{{ $cliente->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-900 dark:text-white">{{ $cliente->nombre }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    @if($cliente->email)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $cliente->email }}</div>
                                    @endif
                                    <div>{{ $cliente->telefono ?? '—' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-white">
                                    Gs. {{ number_format($cliente->ventas_sum_total ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    {{ $cliente->ventas_max_fecha ? \Carbon\Carbon::parse($cliente->ventas_max_fecha)->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    @php
                                        $activo = false;
                                        if ($cliente->ventas_max_fecha) {
                                            $ultimaCompra = \Carbon\Carbon::parse($cliente->ventas_max_fecha);
                                            if ($ultimaCompra->diffInDays(now()) <= 90) {
                                                $activo = true;
                                            }
                                        }
                                    @endphp
                                    <x-badge :type="$activo ? 'success' : 'gray'">
                                        {{ $activo ? 'Activo' : 'Inactivo' }}
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    <div class="flex justify-center gap-3">
                                        {{-- Editar --}}
                                        <a href="{{ route('clientes.edit', $cliente->id) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Editar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        {{-- Eliminar --}}
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
                                                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">Eliminar Cliente</h3>
                                                            <div class="mt-2">
                                                                <p class="text-sm text-gray-500 dark:text-gray-400">¿Estás seguro de que deseas eliminar al cliente "{{ $cliente->nombre }}"? Esta acción no se puede deshacer.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                    <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST">
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
                        {{ $clientes->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>
