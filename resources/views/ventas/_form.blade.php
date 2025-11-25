<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Selección de Cliente --}}
    <div>
        <x-input-label for="cliente" :value="__('Cliente')" />
        @php
            $clienteSeleccionado = old('cliente', $venta->clienteRelacion->id ?? null);
            $clienteActual = $clienteSeleccionado
                ? $clientes->firstWhere('id', $clienteSeleccionado)->nombre ?? 'Seleccione un cliente'
                : 'Seleccione un cliente';
        @endphp

        <div x-data="{ open: false, selected: '{{ $clienteActual }}', value: '{{ $clienteSeleccionado }}' }" class="relative mt-1">
            <button type="button" @click="open = !open" @click.away="open = false" class="relative w-full cursor-default rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 py-2 pl-3 pr-10 text-left shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm text-gray-700 dark:text-gray-300">
                <span class="block truncate" x-text="selected"></span>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </span>
            </button>

            <ul x-show="open" class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white dark:bg-gray-900 py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm" style="display: none;">
                @foreach ($clientes as $cli)
                    <li class="text-gray-900 dark:text-gray-200 relative cursor-default select-none py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white"
                        @click="selected = '{{ $cli->nombre }}'; value = '{{ $cli->id }}'; open = false; document.getElementById('cliente').value = '{{ $cli->id }}'">
                        <span class="block truncate font-normal">
                            {{ $cli->nombre }}
                        </span>
                    </li>
                @endforeach
            </ul>
            
            <input type="hidden" name="cliente" id="cliente" :value="value">
        </div>
        <x-input-error :messages="$errors->get('cliente')" class="mt-2" />
    </div>

    {{-- Selección de Producto --}}
    <div>
        <x-input-label for="producto" :value="__('Producto')" />
        @php
            $productoSeleccionado = old('producto', $venta->productoRelacion->id ?? null);
            $productoActual = $productoSeleccionado
                ? $productos->firstWhere('id', $productoSeleccionado)->nombre ?? 'Seleccione un producto'
                : 'Seleccione un producto';
            $precioProducto = $productoSeleccionado ? $productos->firstWhere('id', $productoSeleccionado)->precio ?? 0 : 0;
        @endphp

        <div x-data="{ open: false, selected: '{{ $productoActual }}', value: '{{ $productoSeleccionado }}' }" class="relative mt-1">
            <button type="button" @click="open = !open" @click.away="open = false" class="relative w-full cursor-default rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 py-2 pl-3 pr-10 text-left shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm text-gray-700 dark:text-gray-300">
                <span class="block truncate" x-text="selected"></span>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </span>
            </button>

            <ul x-show="open" class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white dark:bg-gray-900 py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm" style="display: none;">
                @foreach ($productos as $prod)
                    <li class="text-gray-900 dark:text-gray-200 relative cursor-default select-none py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white"
                        @click="selected = '{{ $prod->nombre }}'; 
                                value = '{{ $prod->id }}'; 
                                open = false; 
                                document.getElementById('producto').value = '{{ $prod->id }}';
                                document.getElementById('precio_unitario').value = '{{ number_format($prod->precio, 0, ',', '.') }}';
                                actualizarTotal();">
                        <span class="block truncate font-normal">
                            {{ $prod->nombre }}
                        </span>
                    </li>
                @endforeach
            </ul>
            
            <input type="hidden" name="producto" id="producto" :value="value">
        </div>
        <x-input-error :messages="$errors->get('producto')" class="mt-2" />
    </div>

    {{-- Cantidad --}}
    <div>
        <x-input-label for="cantidad" :value="__('Cantidad')" />
        <x-text-input id="cantidad" class="block mt-1 w-full" type="number" name="cantidad" min="1" :value="old('cantidad', 1)" oninput="actualizarTotal()" required />
        <x-input-error :messages="$errors->get('cantidad')" class="mt-2" />
    </div>

    {{-- Precio Unitario --}}
    <div>
        <x-input-label for="precio_unitario" :value="__('Precio Unitario')" />
        <div class="relative mt-1">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-semibold">Gs.</span>
            <x-text-input id="precio_unitario" class="block w-full pl-12" type="text" name="precio_unitario" 
                :value="old('precio_unitario', isset($venta) ? number_format($venta->precio_unitario ?? $precioProducto, 0, ',', '.') : '')" 
                oninput="actualizarTotal()" required />
        </div>
        <x-input-error :messages="$errors->get('precio_unitario')" class="mt-2" />
    </div>

    {{-- Total --}}
    <div>
        <x-input-label for="total" :value="__('Total')" />
        <div class="relative mt-1">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-semibold">Gs.</span>
            <x-text-input id="total" class="block w-full pl-12 bg-gray-100 dark:bg-gray-700 cursor-not-allowed" type="text" name="total" readonly
                :value="old('total', isset($venta) ? number_format($venta->total, 0, ',', '.') : '')" />
        </div>
    </div>

    {{-- Método de Pago --}}
    <div>
        <x-input-label for="metodo_pago" :value="__('Método de Pago')" />
        @php
            $metodoSeleccionado = old('metodo_pago', $venta->metodo_pago ?? null);
            $metodoActual = $metodoSeleccionado ?: 'Seleccione un método';
            $metodos = ['Efectivo', 'Tarjeta', 'Transferencia'];
        @endphp

        <div x-data="{ open: false, selected: '{{ $metodoActual }}', value: '{{ $metodoSeleccionado }}' }" class="relative mt-1">
            <button type="button" @click="open = !open" @click.away="open = false" class="relative w-full cursor-default rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 py-2 pl-3 pr-10 text-left shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm text-gray-700 dark:text-gray-300">
                <span class="block truncate" x-text="selected"></span>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </span>
            </button>

            <ul x-show="open" class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white dark:bg-gray-900 py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm" style="display: none;">
                @foreach ($metodos as $met)
                    <li class="text-gray-900 dark:text-gray-200 relative cursor-default select-none py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white"
                        @click="selected = '{{ $met }}'; value = '{{ $met }}'; open = false; document.getElementById('metodo_pago').value = '{{ $met }}'">
                        <span class="block truncate font-normal">
                            {{ $met }}
                        </span>
                    </li>
                @endforeach
            </ul>
            
            <input type="hidden" name="metodo_pago" id="metodo_pago" :value="value">
        </div>
        <x-input-error :messages="$errors->get('metodo_pago')" class="mt-2" />
    </div>

    {{-- Estado --}}
    <div>
        <x-input-label for="estado" :value="__('Estado')" />
        @php
            $estadoSeleccionado = old('estado', $venta->estado ?? null);
            $estadoActual = $estadoSeleccionado ?: 'Seleccione un estado';
            $estados = ['Pendiente', 'Pagado', 'Anulado'];
        @endphp

        <div x-data="{ open: false, selected: '{{ $estadoActual }}', value: '{{ $estadoSeleccionado }}' }" class="relative mt-1">
            <button type="button" @click="open = !open" @click.away="open = false" class="relative w-full cursor-default rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 py-2 pl-3 pr-10 text-left shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm text-gray-700 dark:text-gray-300">
                <span class="block truncate" x-text="selected"></span>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </span>
            </button>

            <ul x-show="open" class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white dark:bg-gray-900 py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm" style="display: none;">
                @foreach ($estados as $est)
                    <li class="text-gray-900 dark:text-gray-200 relative cursor-default select-none py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white"
                        @click="selected = '{{ $est }}'; value = '{{ $est }}'; open = false; document.getElementById('estado').value = '{{ $est }}'">
                        <span class="block truncate font-normal">
                            {{ $est }}
                        </span>
                    </li>
                @endforeach
            </ul>
            
            <input type="hidden" name="estado" id="estado" :value="value">
        </div>
        <x-input-error :messages="$errors->get('estado')" class="mt-2" />
    </div>

    {{-- Fecha --}}
    <div>
        <x-input-label for="fecha" :value="__('Fecha')" />
        <x-text-input id="fecha" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700 cursor-not-allowed" type="date" name="fecha" 
            :value="old('fecha', isset($venta) ? $venta->fecha : date('Y-m-d'))" readonly required />
        <x-input-error :messages="$errors->get('fecha')" class="mt-2" />
    </div>

</div>

{{-- Botones --}}
<div class="flex justify-end space-x-4 mt-6">
    <a href="{{ route('ventas.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
        {{ __('Cancelar') }}
    </a>
    <x-primary-button>
        {{ __('Guardar Venta') }}
    </x-primary-button>
</div>

{{-- Scripts --}}
<script>
    function limpiarNumero(valor) {
        return valor.replace(/\D/g, '');
    }

    function formatearMiles(valor) {
        return valor.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function actualizarTotal() {
        const cantidad = parseInt(document.getElementById('cantidad').value || 0);
        const precioStr = limpiarNumero(document.getElementById('precio_unitario').value || '0');
        const precio = parseInt(precioStr || 0);
        const total = cantidad * precio;
        document.getElementById('total').value = formatearMiles(total.toString());
    }

    document.addEventListener('DOMContentLoaded', function() {
        const producto = document.getElementById('producto');
        const precio = document.getElementById('precio_unitario');
        const cantidad = document.getElementById('cantidad');
        const total = document.getElementById('total');
        const fecha = document.getElementById('fecha');

        // 🧮 Calcular total automáticamente si ya existen datos (modo edición)
        if (producto.value && precio.value && cantidad.value) {
            actualizarTotal();
        }

        // 📅 Si el campo de fecha está vacío, poner fecha actual (modo creación)
        if (fecha && !fecha.value) {
            const hoy = new Date().toISOString().split('T')[0];
            fecha.value = hoy;
        }
        
        // 🔢 Recalcular al cambiar cantidad o precio manualmente
        cantidad.addEventListener('input', actualizarTotal);
        precio.addEventListener('input', actualizarTotal);

        // 🧹 Limpiar formato antes de enviar el formulario
        const form = document.querySelector('form');
        form.addEventListener('submit', function() {
            precio.value = limpiarNumero(precio.value);
            total.value = limpiarNumero(total.value);
        });
    });
</script>
