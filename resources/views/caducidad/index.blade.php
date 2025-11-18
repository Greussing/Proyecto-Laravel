<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-white">
            Control de Caducidad de Productos
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow rounded-lg p-6">

                {{-- ENCABEZADO PRINCIPAL --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">
                            Monitoreo de vencimientos y estado sanitario del inventario
                        </h3>

                        <p class="text-sm text-gray-500">
                            Fecha de referencia: {{ now()->format('d/m/Y') }}
                        </p>
                    </div>

                    {{-- Botones PDF / Excel --}}
                    <div class="flex flex-wrap gap-2">

                        <a href="{{ route('caducidad.export.pdf') }}"
                           class="inline-flex items-center px-3 py-1.5 text-sm rounded bg-red-600 text-white hover:bg-red-700">
                            🧾 PDF
                        </a>

                        <a href="{{ route('caducidad.export.excel') }}"
                           class="inline-flex items-center px-3 py-1.5 text-sm rounded bg-green-600 text-white hover:bg-green-700">
                            📊 Excel
                        </a>
                    </div>
                </div>

                {{-- RESUMEN GENERAL --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4 text-sm">
                    
                    {{-- Próximos a vencer --}}
                    <div class="bg-gray-50 border rounded p-3">
                        <div class="text-xs text-gray-500">Próximos a vencer (≤ 30 días)</div>
                        <div class="text-lg font-semibold text-yellow-600">
                            {{ $proximos->count() }}
                        </div>
                    </div>

                    {{-- Vencidos --}}
                    <div class="bg-gray-50 border rounded p-3">
                        <div class="text-xs text-gray-500">Productos vencidos</div>
                        <div class="text-lg font-semibold text-red-600">
                            {{ $vencidos->count() }}
                        </div>
                    </div>

                    {{-- Revisión --}}
                    <div class="bg-gray-50 border rounded p-3">
                        <div class="text-xs text-gray-500">En revisión (31–60 días)</div>
                        <div class="text-lg font-semibold text-blue-600">
                            {{ $revision->count() }}
                        </div>
                    </div>
                </div>

                {{-- ===================== --}}
                {{--      SECCIÓN 1        --}}
                {{-- ===================== --}}
                <h3 class="text-md font-semibold text-yellow-700 border-l-4 border-yellow-500 pl-2 mb-2">
                    🟧 Próximos a vencer (≤ 30 días)
                </h3>

                @if ($proximos->isEmpty())
                    <p class="text-sm text-gray-500 mb-4">No hay productos próximos a vencer.</p>
                @else
                    <div class="overflow-x-auto mb-6">
                        <table class="min-w-full border border-yellow-300 text-sm">
                            <thead class="bg-yellow-50">
                                <tr class="text-xs uppercase tracking-wide text-yellow-700">
                                    <th class="px-2 py-2 border text-left">Producto</th>
                                    <th class="px-2 py-2 border text-left">Categoría</th>
                                    <th class="px-2 py-2 border text-center">Lote</th>
                                    <th class="px-2 py-2 border text-center">Vencimiento</th>
                                    <th class="px-2 py-2 border text-center">Días restantes</th>
                                    <th class="px-2 py-2 border text-center">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($proximos as $p)
                                    @php
                                        $badge = $p->dias_restantes <= 7 
                                            ? 'bg-red-100 text-red-700'
                                            : 'bg-yellow-100 text-yellow-700';
                                    @endphp

                                    <tr class="hover:bg-yellow-50">
                                        <td class="px-2 py-1.5 border">{{ $p->nombre }}</td>
                                        <td class="px-2 py-1.5 border">
                                            {{ $p->categoriaRelacion->nombre ?? '—' }}
                                        </td>
                                        <td class="px-2 py-1.5 border text-center">{{ $p->lote ?? '—' }}</td>

                                        <td class="px-2 py-1.5 border text-center">
                                            {{ optional($p->fecha_vencimiento)->format('d/m/Y') }}
                                        </td>

                                        <td class="px-2 py-1.5 border text-center">
                                            <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $badge }}">
                                                {{ $p->dias_restantes }}
                                            </span>
                                        </td>

                                        <td class="px-2 py-1.5 border text-center">
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
                <h3 class="text-md font-semibold text-red-700 border-l-4 border-red-500 pl-2 mb-2">
                    🟥 Productos vencidos
                </h3>

                @if ($vencidos->isEmpty())
                    <p class="text-sm text-gray-500 mb-4">No hay productos vencidos registrados.</p>
                @else
                    <div class="overflow-x-auto mb-6">
                        <table class="min-w-full border border-red-300 text-sm">
                            <thead class="bg-red-50">
                                <tr class="text-xs uppercase tracking-wide text-red-700">
                                    <th class="px-2 py-2 border text-left">Producto</th>
                                    <th class="px-2 py-2 border text-left">Categoría</th>
                                    <th class="px-2 py-2 border text-center">Lote</th>
                                    <th class="px-2 py-2 border text-center">Vencimiento</th>
                                    <th class="px-2 py-2 border text-center">Días vencido</th>
                                    <th class="px-2 py-2 border text-center">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vencidos as $p)
                                    <tr class="hover:bg-red-50">
                                        <td class="px-2 py-1.5 border">{{ $p->nombre }}</td>
                                        <td class="px-2 py-1.5 border">
                                            {{ $p->categoriaRelacion->nombre ?? '—' }}
                                        </td>
                                        <td class="px-2 py-1.5 border text-center">{{ $p->lote ?? '—' }}</td>

                                        <td class="px-2 py-1.5 border text-center">
                                            {{ optional($p->fecha_vencimiento)->format('d/m/Y') }}
                                        </td>

                                        <td class="px-2 py-1.5 border text-center text-red-700 font-semibold">
                                            {{ abs($p->dias_restantes) }}
                                        </td>

                                        <td class="px-2 py-1.5 border text-center">
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
                <h3 class="text-md font-semibold text-blue-700 border-l-4 border-blue-500 pl-2 mb-2">
                    🟦 En revisión (31–60 días)
                </h3>

                @if ($revision->isEmpty())
                    <p class="text-sm text-gray-500">No hay productos en la ventana de revisión.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-blue-300 text-sm">
                            <thead class="bg-blue-50">
                                <tr class="text-xs uppercase tracking-wide text-blue-700">
                                    <th class="px-2 py-2 border text-left">Producto</th>
                                    <th class="px-2 py-2 border text-left">Categoría</th>
                                    <th class="px-2 py-2 border text-center">Lote</th>
                                    <th class="px-2 py-2 border text-center">Vencimiento</th>
                                    <th class="px-2 py-2 border text-center">Días restantes</th>
                                    <th class="px-2 py-2 border text-center">Stock</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($revision as $p)
                                    <tr class="hover:bg-blue-50">
                                        <td class="px-2 py-1.5 border">{{ $p->nombre }}</td>
                                        <td class="px-2 py-1.5 border">
                                            {{ $p->categoriaRelacion->nombre ?? '—' }}
                                        </td>
                                        <td class="px-2 py-1.5 border text-center">{{ $p->lote ?? '—' }}</td>

                                        <td class="px-2 py-1.5 border text-center">
                                            {{ optional($p->fecha_vencimiento)->format('d/m/Y') }}
                                        </td>

                                        <td class="px-2 py-1.5 border text-center text-blue-700 font-semibold">
                                            {{ $p->dias_restantes }}
                                        </td>

                                        <td class="px-2 py-1.5 border text-center">
                                            {{ $p->cantidad }}
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