{{-- resources/views/analisis-productos/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-white">
            Análisis de Productos
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow rounded-lg p-6">

                {{-- ENCABEZADO --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">
                            Análisis de rotación e ingresos por producto
                        </h3>
                        <p class="text-sm text-gray-500">
                            Período analizado: {{ $desde->format('d/m/Y') }} – {{ $hasta->format('d/m/Y') }}
                            ({{ $diasPeriodo }} días)
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        {{-- Selector rápido de período --}}
                        <form method="GET" action="{{ route('analisis.index') }}" class="flex items-center gap-2">
                            <label class="text-sm text-gray-600">Período:</label>
                            <select name="dias" onchange="this.form.submit()"
                                    class="text-sm border rounded px-2 py-1">
                                @foreach ([7, 15, 30, 60, 90] as $op)
                                    <option value="{{ $op }}" {{ $op == $diasPeriodo ? 'selected' : '' }}>
                                        Últimos {{ $op }} días
                                    </option>
                                @endforeach
                            </select>
                        </form>

                        {{-- Botones PDF / Excel --}}
                        <a href="{{ route('analisis.pdf', ['dias' => $diasPeriodo]) }}"
                           class="inline-flex items-center px-3 py-1.5 text-sm rounded bg-red-600 text-white hover:bg-red-700">
                            🧾 PDF
                        </a>

                        <a href="{{ route('analisis.excel', ['dias' => $diasPeriodo]) }}"
                           class="inline-flex items-center px-3 py-1.5 text-sm rounded bg-green-600 text-white hover:bg-green-700">
                            📊 Excel
                        </a>
                    </div>
                </div>

                {{-- RESUMEN GENERAL --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4 text-sm">
                    <div class="bg-gray-50 border rounded p-3">
                        <div class="text-xs text-gray-500">Ingreso total en el período</div>
                        <div class="text-lg font-semibold">
                            Gs. {{ number_format($ingresoTotalGlobal, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="bg-gray-50 border rounded p-3">
                        <div class="text-xs text-gray-500">Productos con ventas</div>
                        <div class="text-lg font-semibold">
                            {{ $stats->where('vendido', '>', 0)->count() }} /
                            {{ $stats->count() }}
                        </div>
                    </div>

                    <div class="bg-gray-50 border rounded p-3">
                        <div class="text-xs text-gray-500">Producto más rentable</div>
                        @php
                            $top = $stats->sortByDesc('ingreso_total')->first();
                        @endphp
                        <div class="text-sm font-semibold">
                            {{ $top && $top->ingreso_total > 0 ? $top->producto : '—' }}
                        </div>
                    </div>
                </div>

                {{-- TABLA PRINCIPAL --}}
                @if ($stats->isEmpty())
                    <p class="text-center text-gray-500 py-6">
                        No hay datos de ventas en el período seleccionado.
                    </p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-200 text-sm">
                            <thead class="bg-gray-100">
                                <tr class="text-xs uppercase tracking-wide text-gray-600">
                                    <th class="px-2 py-2 border text-left">Producto</th>
                                    <th class="px-2 py-2 border text-center">Vendido (u)</th>
                                    <th class="px-2 py-2 border text-right">Ingreso total</th>
                                    <th class="px-2 py-2 border text-right">% Ingresos</th>
                                    <th class="px-2 py-2 border text-center">Stock actual</th>
                                    <th class="px-2 py-2 border text-center">Rotación</th>
                                    <th class="px-2 py-2 border text-center">Días sin venta</th>
                                    <th class="px-2 py-2 border text-center">Última venta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stats as $row)
                                    @php
                                        // Hover según rotación
                                        $rowHover = match ($row->rotacion) {
                                            'Alta'  => 'hover:bg-green-50',
                                            'Media' => 'hover:bg-yellow-50',
                                            'Baja'  => 'hover:bg-orange-50',
                                            'Nula'  => 'hover:bg-red-50',
                                            default => 'hover:bg-gray-50',
                                        };

                                        // Badge de "Vendido (u)"
                                        if ($row->vendido == 0) {
                                            $vendidoClass = 'bg-red-100 text-red-700';
                                        } elseif ($row->vendido <= 5) {
                                            $vendidoClass = 'bg-yellow-100 text-yellow-700';
                                        } elseif ($row->vendido <= 20) {
                                            $vendidoClass = 'bg-blue-100 text-blue-700';
                                        } else {
                                            $vendidoClass = 'bg-green-100 text-green-700';
                                        }

                                        // Badge de "Stock actual"
                                        if ($row->stock_actual == 0) {
                                            $stockClass = 'bg-red-100 text-red-700';
                                            $stockText  = 'Agotado';
                                        } elseif ($row->stock_actual <= 5) {
                                            $stockClass = 'bg-red-100 text-red-700';
                                            $stockText  = $row->stock_actual;
                                        } elseif ($row->stock_actual <= 10) {
                                            $stockClass = 'bg-yellow-100 text-yellow-700';
                                            $stockText  = $row->stock_actual;
                                        } else {
                                            $stockClass = 'bg-green-100 text-green-700';
                                            $stockText  = $row->stock_actual;
                                        }

                                        // Badge de "Rotación"
                                        $rotClass = match ($row->rotacion) {
                                            'Alta'  => 'bg-green-100 text-green-700',
                                            'Media' => 'bg-yellow-100 text-yellow-700',
                                            'Baja'  => 'bg-orange-100 text-orange-700',
                                            'Nula'  => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp

                                    <tr class="{{ $rowHover }}">
                                        {{-- Producto --}}
                                        <td class="px-2 py-1.5 border">
                                            <div class="text-sm font-semibold text-gray-800">
                                                {{ $row->producto }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                Participa con {{ number_format($row->porcentaje_ingresos, 2, ',', '.') }}% de los ingresos
                                            </div>
                                        </td>

                                        {{-- Vendido (u) con badge --}}
                                        <td class="px-2 py-1.5 border text-center">
                                            <span class="px-2 py-1 rounded text-xs font-medium {{ $vendidoClass }}">
                                                {{ $row->vendido }}
                                            </span>
                                        </td>

                                        {{-- Ingreso total --}}
                                        <td class="px-2 py-1.5 border text-right font-semibold text-blue-700">
                                            Gs. {{ number_format($row->ingreso_total, 0, ',', '.') }}
                                        </td>

                                        {{-- % Ingresos --}}
                                        <td class="px-2 py-1.5 border text-right text-sm">
                                            {{ number_format($row->porcentaje_ingresos, 2, ',', '.') }} %
                                        </td>

                                        {{-- Stock actual con badge tipo inventario --}}
                                        <td class="px-2 py-1.5 border text-center">
                                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $stockClass }}">
                                                {{ $stockText }}
                                            </span>
                                        </td>

                                        {{-- Rotación --}}
                                        <td class="px-2 py-1.5 border text-center">
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $rotClass }}">
                                                {{ $row->rotacion }}
                                            </span>
                                        </td>

                                        {{-- Días sin venta --}}
                                        <td class="px-2 py-1.5 border text-center text-sm">
                                            {{ $row->dias_sin_venta !== null ? $row->dias_sin_venta . ' días' : '—' }}
                                        </td>

                                        {{-- Última venta --}}
                                        <td class="px-2 py-1.5 border text-center text-xs text-gray-600">
                                            {{ $row->ultima_venta ? $row->ultima_venta->format('d/m/Y H:i') : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>