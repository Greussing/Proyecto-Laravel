<x-app-layout>
    {{-- Encabezado --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🔁 Devolución de producto – Venta #{{ $venta->id }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">

                {{-- Info general de la venta --}}
                <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p><strong>Cliente:</strong>
                            {{ optional($venta->clienteRelacion)->nombre ?? 'Sin cliente' }}
                        </p>
                        <p><strong>Vendedor:</strong>
                            {{ optional($venta->usuarioRelacion)->name ?? 'N/D' }}
                        </p>
                    </div>
                    <div>
                        <p><strong>Fecha:</strong>
                            {{ $venta->fecha?->format('d/m/Y H:i') ?? 'N/D' }}
                        </p>
                        <p>
                            <strong>Estado:</strong>
                            <span
                                class="px-2 py-1 rounded text-xs font-medium 
                                @if ($venta->estado === 'Pagado') bg-green-100 text-green-700
                                @elseif($venta->estado === 'Pendiente') bg-yellow-100 text-yellow-700
                                @else bg-red-100 text-red-700 @endif">
                                {{ $venta->estado }}
                            </span>
                        </p>
                        <p><strong>Total:</strong>
                            {{ number_format($venta->total, 0, ',', '.') }} Gs.
                        </p>
                    </div>
                </div>

                @php
                    $detalle = $venta->detalles->first();
                @endphp

                @if (!$detalle)
                    <div class="p-4 mb-4 bg-red-100 text-red-700 rounded">
                        Esta venta no tiene detalles, no se puede registrar devolución.
                    </div>

                    <a href="{{ route('ventas.index') }}"
                        class="inline-flex items-center px-4 py-2 border rounded text-sm text-gray-700 hover:bg-gray-100">
                        ⬅ Volver
                    </a>
                @else
                    {{-- Info del producto --}}
                    <div class="mb-4 border rounded-lg p-4 bg-gray-50">
                        <h3 class="font-semibold mb-2 text-gray-800">
                            🧾 Producto de la venta
                        </h3>
                        <p class="text-sm"><strong>Producto:</strong>
                            {{ $detalle->producto->nombre ?? 'N/D' }}
                        </p>
                        <p class="text-sm">
                            <strong>Cantidad vendida:</strong> {{ $detalle->cantidad }}
                        </p>
                    </div>

                    {{-- Errores --}}
                    @if ($errors->any())
                        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                            <ul class="list-disc list-inside text-xs">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Formulario --}}
                    <form action="{{ route('ventas.devolucion.store', $venta->id) }}" method="POST" class="space-y-5">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium mb-1">
                                Cantidad a devolver
                            </label>
                            <input type="number" name="cantidad_devolver"
                                class="w-full border rounded px-3 py-2 text-sm" min="1"
                                max="{{ $detalle->cantidad }}"
                                value="{{ old('cantidad_devolver', $detalle->cantidad) }}" required>
                            <p class="text-xs text-gray-500 mt-1">
                                Máximo permitido: {{ $detalle->cantidad }} unidad(es).
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">
                                Detalle / motivo (opcional)
                            </label>
                            <textarea name="detalle" rows="3" class="w-full border rounded px-3 py-2 text-sm"
                                placeholder="Ej: producto defectuoso, cambio, error de carga...">{{ old('detalle') }}</textarea>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('ventas.index') }}"
                                class="px-4 py-2 border rounded text-sm text-gray-700 hover:bg-gray-100">
                                ❌ Cancelar
                            </a>

                            <button type="submit"
                                class="px-4 py-2border rounded text-sm text-gray-700 hover:bg-gray-100">
                                🔁 Confirmar devolución
                            </button>
                        </div>

                    </form>

                @endif

            </div>
        </div>
    </div>
</x-app-layout>
