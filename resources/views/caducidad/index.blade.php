<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Control de Caducidad de Productos') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <x-card>
                {{-- ENCABEZADO PRINCIPAL --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                            Monitoreo de vencimientos y estado sanitario del inventario
                        </h3>

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Fecha de referencia: {{ now()->format('d/m/Y') }}
                        </p>
                    </div>

                    {{-- Botones PDF / Excel --}}
                    <div class="flex flex-wrap gap-2">
                        {{-- Exportar PDF --}}
                        <a href="{{ route('caducidad.export.pdf', request()->all()) }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            PDF
                        </a>
                        {{-- Exportar Excel --}}
                        <a href="{{ route('caducidad.export.excel', request()->all()) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Excel
                        </a>
                    </div>
                </div>

                {{-- RESUMEN GENERAL --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 text-sm">
                    
                    {{-- Próximos a vencer --}}
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
                        <div class="text-xs font-semibold text-yellow-700 dark:text-yellow-300 uppercase">
                            Próximos a vencer (≤ 30 días)
                        </div>
                        <div class="mt-2 text-2xl font-bold text-yellow-600 dark:text-yellow-300">
                            {{ $proximos->count() }}
                        </div>
                    </div>

                    {{-- Vencidos --}}
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-4">
                        <div class="text-xs font-semibold text-red-700 dark:text-red-300 uppercase">
                            Productos vencidos
                        </div>
                        <div class="mt-2 text-2xl font-bold text-red-600 dark:text-red-300">
                            {{ $vencidos->count() }}
                        </div>
                    </div>

                    {{-- Revisión --}}
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                        <div class="text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase">
                            En revisión (31–60 días)
                        </div>
                        <div class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-300">
                            {{ $revision->count() }}
                        </div>
                    </div>
                </div>

                {{-- ===================== --}}
                {{--      SECCIÓN 1        --}}
                {{-- ===================== --}}
                <h3 class="text-md font-semibold text-yellow-700 dark:text-yellow-300 border-l-4 border-yellow-500 pl-2 mb-2">
                    🟧 Próximos a vencer (≤ 30 días)
                </h3>

                @if ($proximos->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        No hay productos próximos a vencer.
                    </p>
                @else
                    <div class="overflow-x-auto mb-6">
                        <table class="min-w-full border border-yellow-200 dark:border-yellow-700 text-sm">
                            <thead class="bg-yellow-50 dark:bg-yellow-900/40">
                                <tr class="text-xs uppercase tracking-wide text-yellow-700 dark:text-yellow-200">
                                    <th class="px-3 py-2 border border-yellow-200 dark:border-yellow-700 text-left">Producto</th>
                                    <th class="px-3 py-2 border border-yellow-200 dark:border-yellow-700 text-left">Categoría</th>
                                    <th class="px-3 py-2 border border-yellow-200 dark:border-yellow-700 text-center">Lote</th>
                                    <th class="px-3 py-2 border border-yellow-200 dark:border-yellow-700 text-center">Vencimiento</th>
                                    <th class="px-3 py-2 border border-yellow-200 dark:border-yellow-700 text-center">Días restantes</th>
                                    <th class="px-3 py-2 border border-yellow-200 dark:border-yellow-700 text-center">Stock</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900/40">
                                @foreach ($proximos as $p)
                                    @php
                                        $badge = $p->dias_restantes <= 7 
                                            ? 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-200'
                                            : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-200';
                                    @endphp

                                    <tr class="hover:bg-yellow-50 dark:hover:bg-yellow-900/30 transition-colors">
                                        <td class="px-3 py-1.5 border border-yellow-200 dark:border-yellow-700 text-gray-800 dark:text-gray-100">
                                            {{ $p->nombre }}
                                        </td>
                                        <td class="px-3 py-1.5 border border-yellow-200 dark:border-yellow-700 text-gray-700 dark:text-gray-200">
                                            {{ $p->categoriaRelacion->nombre ?? '—' }}
                                        </td>
                                        <td class="px-3 py-1.5 border border-yellow-200 dark:border-yellow-700 text-center text-gray-700 dark:text-gray-200">
                                            {{ $p->lote ?? '—' }}
                                        </td>
                                        <td class="px-3 py-1.5 border border-yellow-200 dark:border-yellow-700 text-center text-gray-700 dark:text-gray-200">
                                            {{ optional($p->fecha_vencimiento)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-3 py-1.5 border border-yellow-200 dark:border-yellow-700 text-center">
                                            <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $badge }}">
                                                {{ $p->dias_restantes }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-1.5 border border-yellow-200 dark:border-yellow-700 text-center text-gray-800 dark:text-gray-100">
                                            {{ $p->cantidad }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- ===================== --}}
                {{--      SECCIÓN 2        --}}
                {{-- ===================== --}}
                <h3 class="text-md font-semibold text-red-700 dark:text-red-300 border-l-4 border-red-500 pl-2 mb-2">
                    🟥 Productos vencidos
                </h3>

                @if ($vencidos->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        No hay productos vencidos registrados.
                    </p>
                @else
                    <div class="overflow-x-auto mb-6">
                        <table class="min-w-full border border-red-200 dark:border-red-700 text-sm">
                            <thead class="bg-red-50 dark:bg-red-900/40">
                                <tr class="text-xs uppercase tracking-wide text-red-700 dark:text-red-200">
                                    <th class="px-3 py-2 border border-red-200 dark:border-red-700 text-left">Producto</th>
                                    <th class="px-3 py-2 border border-red-200 dark:border-red-700 text-left">Categoría</th>
                                    <th class="px-3 py-2 border border-red-200 dark:border-red-700 text-center">Lote</th>
                                    <th class="px-3 py-2 border border-red-200 dark:border-red-700 text-center">Vencimiento</th>
                                    <th class="px-3 py-2 border border-red-200 dark:border-red-700 text-center">Días vencido</th>
                                    <th class="px-3 py-2 border border-red-200 dark:border-red-700 text-center">Stock</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900/40">
                                @foreach ($vencidos as $p)
                                    <tr class="hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors">
                                        <td class="px-3 py-1.5 border border-red-200 dark:border-red-700 text-gray-800 dark:text-gray-100">
                                            {{ $p->nombre }}
                                        </td>
                                        <td class="px-3 py-1.5 border border-red-200 dark:border-red-700 text-gray-700 dark:text-gray-200">
                                            {{ $p->categoriaRelacion->nombre ?? '—' }}
                                        </td>
                                        <td class="px-3 py-1.5 border border-red-200 dark:border-red-700 text-center text-gray-700 dark:text-gray-200">
                                            {{ $p->lote ?? '—' }}
                                        </td>
                                        <td class="px-3 py-1.5 border border-red-200 dark:border-red-700 text-center text-gray-700 dark:text-gray-200">
                                            {{ optional($p->fecha_vencimiento)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-3 py-1.5 border border-red-200 dark:border-red-700 text-center text-red-700 dark:text-red-300 font-semibold">
                                            {{ abs($p->dias_restantes) }}
                                        </td>
                                        <td class="px-3 py-1.5 border border-red-200 dark:border-red-700 text-center text-gray-800 dark:text-gray-100">
                                            {{ $p->cantidad }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- ===================== --}}
                {{--      SECCIÓN 3        --}}
                {{-- ===================== --}}
                <h3 class="text-md font-semibold text-blue-700 dark:text-blue-300 border-l-4 border-blue-500 pl-2 mb-2">
                    🟦 En revisión (31–60 días)
                </h3>

                @if ($revision->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        No hay productos en la ventana de revisión.
                    </p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-blue-200 dark:border-blue-700 text-sm">
                            <thead class="bg-blue-50 dark:bg-blue-900/40">
                                <tr class="text-xs uppercase tracking-wide text-blue-700 dark:text-blue-200">
                                    <th class="px-3 py-2 border border-blue-200 dark:border-blue-700 text-left">Producto</th>
                                    <th class="px-3 py-2 border border-blue-200 dark:border-blue-700 text-left">Categoría</th>
                                    <th class="px-3 py-2 border border-blue-200 dark:border-blue-700 text-center">Lote</th>
                                    <th class="px-3 py-2 border border-blue-200 dark:border-blue-700 text-center">Vencimiento</th>
                                    <th class="px-3 py-2 border border-blue-200 dark:border-blue-700 text-center">Días restantes</th>
                                    <th class="px-3 py-2 border border-blue-200 dark:border-blue-700 text-center">Stock</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white dark:bg-gray-900/40">
                                @foreach ($revision as $p)
                                    <tr class="hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors">
                                        <td class="px-3 py-1.5 border border-blue-200 dark:border-blue-700 text-gray-800 dark:text-gray-100">
                                            {{ $p->nombre }}
                                        </td>
                                        <td class="px-3 py-1.5 border border-blue-200 dark:border-blue-700 text-gray-700 dark:text-gray-200">
                                            {{ $p->categoriaRelacion->nombre ?? '—' }}
                                        </td>
                                        <td class="px-3 py-1.5 border border-blue-200 dark:border-blue-700 text-center text-gray-700 dark:text-gray-200">
                                            {{ $p->lote ?? '—' }}
                                        </td>
                                        <td class="px-3 py-1.5 border border-blue-200 dark:border-blue-700 text-center text-gray-700 dark:text-gray-200">
                                            {{ optional($p->fecha_vencimiento)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-3 py-1.5 border border-blue-200 dark:border-blue-700 text-center text-blue-700 dark:text-blue-300 font-semibold">
                                            {{ $p->dias_restantes }}
                                        </td>
                                        <td class="px-3 py-1.5 border border-blue-200 dark:border-blue-700 text-center text-gray-800 dark:text-gray-100">
                                            {{ $p->cantidad }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                @endif

            </x-card>
        </div>
    </div>
</x-app-layout>