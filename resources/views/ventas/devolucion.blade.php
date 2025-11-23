<x-app-layout>
    {{-- Encabezado --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Devolución de producto – Venta #') . $venta->id }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-card>

                {{-- Info general de la venta --}}
                <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 pb-4">
                    <div>
                        <p class="mb-1"><strong>Cliente:</strong>
                            {{ optional($venta->clienteRelacion)->nombre ?? 'Sin cliente' }}
                        </p>
                        <p><strong>Vendedor:</strong>
                            {{ optional($venta->usuarioRelacion)->name ?? 'N/D' }}
                        </p>
                    </div>
                    <div>
                        <p class="mb-1"><strong>Fecha:</strong>
                            {{ $venta->fecha?->format('d/m/Y H:i') ?? 'N/D' }}
                        </p>
                        <div class="flex items-center gap-2">
                            <strong>Estado:</strong>
                            @php
                                $estado = ucfirst($venta->estado ?? 'Desconocido');
                                $estadoType = match(strtolower($venta->estado ?? '')) {
                                    'pagado' => 'success',
                                    'pendiente' => 'warning',
                                    'anulado' => 'danger',
                                    default => 'gray'
                                };
                            @endphp
                            <x-badge :type="$estadoType">{{ $estado }}</x-badge>
                        </div>
                        <p class="mt-1"><strong>Total:</strong>
                            Gs. {{ number_format($venta->total, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                @php
                    $detalle = $venta->detalles->first();
                @endphp

                @if (!$detalle)
                    <div class="p-4 mb-4 bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300 rounded">
                        Esta venta no tiene detalles, no se puede registrar devolución.
                    </div>

                    <a href="{{ route('ventas.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        ⬅ Volver
                    </a>
                @else
                    {{-- Info del producto --}}
                    <div class="mb-6 border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50">
                        <h3 class="font-semibold mb-2 text-gray-800 dark:text-gray-200">
                            🧾 Producto de la venta
                        </h3>
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-1"><strong>Producto:</strong>
                            {{ $detalle->producto->nombre ?? 'N/D' }}
                        </p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            <strong>Cantidad vendida:</strong> {{ $detalle->cantidad }}
                        </p>
                    </div>

                    {{-- Errores --}}
                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/50 border-l-4 border-red-500 rounded-r-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                        Error en la devolución
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                        <ul class="list-disc pl-5 space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Formulario --}}
                    <form action="{{ route('ventas.devolucion.store', $venta->id) }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="cantidad_devolver" :value="__('Cantidad a devolver')" />
                                <x-text-input id="cantidad_devolver" class="block mt-1 w-full" type="number" name="cantidad_devolver" 
                                    min="1" max="{{ $detalle->cantidad }}" :value="old('cantidad_devolver', $detalle->cantidad)" required />
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Máximo permitido: {{ $detalle->cantidad }} unidad(es).
                                </p>
                                <x-input-error :messages="$errors->get('cantidad_devolver')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="detalle" :value="__('Detalle / motivo (opcional)')" />
                                <textarea name="detalle" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    placeholder="Ej: producto defectuoso, cambio, error de carga...">{{ old('detalle') }}</textarea>
                                <x-input-error :messages="$errors->get('detalle')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4 mt-6">
                            <a href="{{ route('ventas.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                ❌ Cancelar
                            </a>

                            <x-primary-button>
                                🔁 Confirmar devolución
                            </x-primary-button>
                        </div>

                    </form>

                @endif

            </x-card>
        </div>
    </div>
</x-app-layout>
