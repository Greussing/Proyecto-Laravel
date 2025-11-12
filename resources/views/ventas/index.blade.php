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

                {{-- Botón crear venta --}}
                <div class="mb-4 flex justify-between items-center">
                    <a href="{{ route('ventas.create') }}"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                        + Nueva Venta
                    </a>
                </div>

                {{-- Si no hay ventas --}}
                @if ($ventas->isEmpty())
                    <p class="text-gray-600">No hay ventas registradas.</p>
                @else
                    {{-- Tabla principal --}}
                    <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                        <thead class="bg-gray-100 text-gray-800">
                            <tr>
                                <th class="px-4 py-2 border">ID</th>
                                <th class="px-4 py-2 border">Cliente</th>
                                <th class="px-4 py-2 border">Producto</th>
                                <th class="px-4 py-2 border">Cantidad</th>
                                <th class="px-4 py-2 border">Precio Unitario</th>
                                <th class="px-4 py-2 border">Total</th>
                                <th class="px-4 py-2 border">Método de Pago</th>
                                <th class="px-4 py-2 border">Estado</th>
                                <th class="px-4 py-2 border">Fecha</th>
                                <th class="px-4 py-2 border">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($ventas as $venta)
                                <tr class="{{ $venta->estado === 'Anulado' ? 'bg-red-100' : 'hover:bg-gray-50' }}">
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

                                    {{-- Cantidad --}}
                                    <td class="px-4 py-2 border text-center font-semibold text-gray-800">
                                        {{ $venta->cantidad_productos }}
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
                                            class="px-2 py-1 rounded text-sm
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
                                    <td class="px-4 py-2 border flex items-center justify-center gap-3"
                                        x-data="{ open: false }">

                                        {{-- Editar --}}
                                        <a href="{{ route('ventas.edit', $venta->id) }}"
                                            class="text-gray-500 hover:text-blue-600 transition" title="Editar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16.862 4.487l1.651 1.651a2 2 0 010 2.828l-8.486 8.486a2 2 0 01-.878.505l-3.722.931a.5.5 0 01-.606-.606l.93-3.722a2 2 0 01.506-.878l8.485-8.486a2 2 0 012.828 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5" />
                                            </svg>
                                        </a>

                                        {{-- Eliminar --}}
                                        <button @click="open = true" class="text-red-600 hover:text-red-800 transition"
                                            title="Eliminar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-4 0a1 1 0 00-1 1v1h6V4a1 1 0 00-1-1m-4 0h4" />
                                            </svg>
                                        </button>

                                        {{-- Modal de confirmación --}}
                                        <div x-show="open" x-cloak
                                            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-96">
                                                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                                    ⚠️ Confirmar eliminación
                                                </h2>
                                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
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

                    {{-- 🔹 Resumen + paginación --}}
                    <div
                        class="mt-4 p-3 bg-gray-50 rounded-lg shadow-sm flex justify-between items-start text-sm text-gray-700">

                        {{-- Columna izquierda --}}
                        <div class="flex flex-col gap-1">
                            <div>
                                Mostrando
                                <span class="font-bold">{{ $ventas->firstItem() }}</span>
                                a
                                <span class="font-bold">{{ $ventas->lastItem() }}</span>
                                de
                                <span class="font-bold">{{ $ventas->total() }}</span>
                                resultados
                            </div>

                            <div class="flex items-center gap-1">
                                📦 <span>Venta total mostradas:
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

                        {{-- Columna derecha: paginación --}}
                        <div class="flex items-center">
                            {{ $ventas->links() }}

                            {{-- Botón Ver paginado --}}
                            @if (request()->has('verTodo'))
                                @php
                                    $q = request()->query();
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
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
