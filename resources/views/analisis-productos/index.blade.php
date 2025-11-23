<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Análisis de Productos
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <x-card>
                {{-- ENCABEZADO --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                            Análisis de rotación e ingresos por producto
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Período analizado: {{ $desde->format('d/m/Y') }} – {{ $hasta->format('d/m/Y') }}
                            ({{ $diasPeriodo }} días)
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2 items-center">
                        {{-- Selector rápido de período --}}
                        <form method="GET" action="{{ route('analisis.index') }}" class="flex items-center gap-2">
                            <label class="text-sm text-gray-600 dark:text-gray-300">Período:</label>
                            <select name="dias" onchange="this.form.submit()"
                                    class="text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 rounded-md px-2 py-1 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ([7, 15, 30, 60, 90] as $op)
                                    <option value="{{ $op }}" {{ $op == $diasPeriodo ? 'selected' : '' }}>
                                        Últimos {{ $op }} días
                                    </option>
                                @endforeach
                            </select>
                        </form>

                        {{-- Exportar PDF --}}
                        <a href="{{ route('analisis.pdf', request()->all()) }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            PDF
                        </a>
                        {{-- Exportar Excel --}}
                        <a href="{{ route('analisis.excel', request()->all()) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Excel
                        </a>
                    </div>
                </div>

                {{-- RESUMEN GENERAL --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 text-sm">
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                        <div class="text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase">
                            Ingreso total en el período
                        </div>
                        <div class="mt-2 text-2xl font-bold text-blue-700 dark:text-blue-200">
                            Gs. {{ number_format($ingresoTotalGlobal, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-lg p-4">
                        <div class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 uppercase">
                            Productos con ventas
                        </div>
                        <div class="mt-2 text-2xl font-bold text-emerald-700 dark:text-emerald-200">
                            {{ $stats->where('vendido', '>', 0)->count() }}
                            <span class="text-base text-gray-500 dark:text-gray-400">
                                / {{ $stats->count() }}
                            </span>
                        </div>
                    </div>

                    <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-lg p-4">
                        <div class="text-xs font-semibold text-purple-700 dark:text-purple-300 uppercase">
                            Producto más rentable
                        </div>
                        @php
                            $top = $stats->sortByDesc('ingreso_total')->first();
                        @endphp
                        <div class="mt-2 text-sm font-semibold text-purple-800 dark:text-purple-100">
                            {{ $top && $top->ingreso_total > 0 ? $top->producto : '—' }}
                        </div>
                    </div>
                </div>

                {{-- TABLA PRINCIPAL --}}
                @if ($stats->isEmpty())
                    <p class="text-center text-gray-500 dark:text-gray-400 py-6">
                        No hay datos de ventas en el período seleccionado.
                    </p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-200 dark:border-gray-700 text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-800/60">
                                <tr class="text-xs uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                    <th class="px-3 py-2 border border-gray-200 dark:border-gray-700 text-left">Producto</th>
                                    <th class="px-3 py-2 border border-gray-200 dark:border-gray-700 text-center">Vendido (u)</th>
                                    <th class="px-3 py-2 border border-gray-200 dark:border-gray-700 text-right">Ingreso total</th>
                                    <th class="px-3 py-2 border border-gray-200 dark:border-gray-700 text-right">% Ingresos</th>
                                    <th class="px-3 py-2 border border-gray-200 dark:border-gray-700 text-center">Stock actual</th>
                                    <th class="px-3 py-2 border border-gray-200 dark:border-gray-700 text-center">Rotación</th>
                                    <th class="px-3 py-2 border border-gray-200 dark:border-gray-700 text-center">Días sin venta</th>
                                    <th class="px-3 py-2 border border-gray-200 dark:border-gray-700 text-center">Última venta</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900/40">
                                @foreach ($stats as $row)
                                    @php
                                        // Hover según rotación
                                        $rowHover = match ($row->rotacion) {
                                            'Alta'  => 'hover:bg-green-50 dark:hover:bg-green-900/20',
                                            'Media' => 'hover:bg-yellow-50 dark:hover:bg-yellow-900/20',
                                            'Baja'  => 'hover:bg-orange-50 dark:hover:bg-orange-900/20',
                                            'Nula'  => 'hover:bg-red-50 dark:hover:bg-red-900/20',
                                            default => 'hover:bg-gray-50 dark:hover:bg-gray-800/60',
                                        };

                                        // Badge de "Vendido (u)"
                                        if ($row->vendido == 0) {
                                            $vendidoClass = 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-200';
                                        } elseif ($row->vendido <= 5) {
                                            $vendidoClass = 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-200';
                                        } elseif ($row->vendido <= 20) {
                                            $vendidoClass = 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-200';
                                        } else {
                                            $vendidoClass = 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-200';
                                        }

                                        // Badge de "Stock actual"
                                        if ($row->stock_actual == 0) {
                                            $stockClass = 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-200';
                                            $stockText  = 'Agotado';
                                        } elseif ($row->stock_actual <= 5) {
                                            $stockClass = 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-200';
                                            $stockText  = $row->stock_actual;
                                        } elseif ($row->stock_actual <= 10) {
                                            $stockClass = 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-200';
                                            $stockText  = $row->stock_actual;
                                        } else {
                                            $stockClass = 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-200';
                                            $stockText  = $row->stock_actual;
                                        }

                                        // Badge de "Rotación"
                                        $rotClass = match ($row->rotacion) {
                                            'Alta'  => 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-200',
                                            'Media' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-200',
                                            'Baja'  => 'bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-200',
                                            'Nula'  => 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-200',
                                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/70 dark:text-gray-200',
                                        };
                                    @endphp

                                    <tr class="{{ $rowHover }} transition-colors">
                                        {{-- Producto --}}
                                        <td class="px-3 py-1.5 border border-gray-200 dark:border-gray-700">
                                            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                                {{ $row->producto }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                Participa con {{ number_format($row->porcentaje_ingresos, 2, ',', '.') }}% de los ingresos
                                            </div>
                                        </td>

                                        {{-- Vendido (u) con badge --}}
                                        <td class="px-3 py-1.5 border border-gray-200 dark:border-gray-700 text-center">
                                            <span class="px-2 py-1 rounded text-xs font-medium {{ $vendidoClass }}">
                                                {{ $row->vendido }}
                                            </span>
                                        </td>

                                        {{-- Ingreso total --}}
                                        <td class="px-3 py-1.5 border border-gray-200 dark:border-gray-700 text-right font-semibold text-blue-700 dark:text-blue-300">
                                            Gs. {{ number_format($row->ingreso_total, 0, ',', '.') }}
                                        </td>

                                        {{-- % Ingresos --}}
                                        <td class="px-3 py-1.5 border border-gray-200 dark:border-gray-700 text-right text-sm text-gray-700 dark:text-gray-200">
                                            {{ number_format($row->porcentaje_ingresos, 2, ',', '.') }} %
                                        </td>

                                        {{-- Stock actual con badge tipo inventario --}}
                                        <td class="px-3 py-1.5 border border-gray-200 dark:border-gray-700 text-center">
                                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $stockClass }}">
                                                {{ $stockText }}
                                            </span>
                                        </td>

                                        {{-- Rotación --}}
                                        <td class="px-3 py-1.5 border border-gray-200 dark:border-gray-700 text-center">
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $rotClass }}">
                                                {{ $row->rotacion }}
                                            </span>
                                        </td>

                                        {{-- Días sin venta --}}
                                        <td class="px-3 py-1.5 border border-gray-200 dark:border-gray-700 text-center text-sm text-gray-700 dark:text-gray-200">
                                            {{ $row->dias_sin_venta !== null ? $row->dias_sin_venta . ' días' : '—' }}
                                        </td>

                                        {{-- Última venta --}}
                                        <td class="px-3 py-1.5 border border-gray-200 dark:border-gray-700 text-center text-xs text-gray-600 dark:text-gray-300">
                                            {{ $row->ultima_venta ? $row->ultima_venta->format('d/m/Y H:i') : '—' }}
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