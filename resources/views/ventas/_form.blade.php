@csrf

{{-- SELECCIÓN DE CLIENTE --}}
<div class="mb-4">
    <label for="cliente" class="block text-sm font-medium text-gray-700">Cliente</label>
    @php
        $clienteSeleccionado = old('cliente', $venta->clienteRelacion->id ?? null);
        $clienteActual = $clienteSeleccionado
            ? $clientes->firstWhere('id', $clienteSeleccionado)->nombre ?? 'Seleccione un cliente'
            : 'Seleccione un cliente';
    @endphp

    <details class="relative border rounded px-2 py-1 mt-1 w-full" id="selectorCliente">
        <summary class="cursor-pointer select-none">
            {{ $clienteActual }}
        </summary>
        <div class="absolute bg-white border rounded shadow-md mt-1 w-full z-10 max-h-60 overflow-y-auto">
            <ul>
                @foreach ($clientes as $cli)
                    @if (!$clienteSeleccionado || $cli->id != $clienteSeleccionado)
                        <li>
                            <a href="#"
                                onclick="event.preventDefault();
                                    document.getElementById('cliente').value='{{ $cli->id }}';
                                    this.closest('details').removeAttribute('open');
                                    this.closest('details').querySelector('summary').textContent='{{ $cli->nombre }}';"
                                class="block px-3 py-2 hover:bg-gray-100 rounded">
                                {{ $cli->nombre }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </details>

    <input type="hidden" name="cliente" id="cliente" value="{{ $clienteSeleccionado }}">
    @error('cliente')
        <p class="text-red-600 text-sm">{{ $message }}</p>
    @enderror
</div>

{{-- SELECCIÓN DE PRODUCTO --}}
<div class="mb-4">
    <label for="producto" class="block text-sm font-medium text-gray-700">Producto</label>
    @php
        $productoSeleccionado = old('producto', $venta->productoRelacion->id ?? null);
        $productoActual = $productoSeleccionado
            ? $productos->firstWhere('id', $productoSeleccionado)->nombre ?? 'Seleccione un producto'
            : 'Seleccione un producto';
        $precioProducto = $productoSeleccionado ? $productos->firstWhere('id', $productoSeleccionado)->precio ?? 0 : 0;
    @endphp

    <details class="relative border rounded px-2 py-1 mt-1 w-full" id="selectorProducto">
        <summary class="cursor-pointer select-none">
            {{ $productoActual }}
        </summary>
        <div class="absolute bg-white border rounded shadow-md mt-1 w-full z-10 max-h-60 overflow-y-auto">
            <ul>
                @foreach ($productos as $prod)
                    @if (!$productoSeleccionado || $prod->id != $productoSeleccionado)
                        <li>
                            <a href="#"
                                onclick="event.preventDefault();
                                    document.getElementById('producto').value='{{ $prod->id }}';
                                    document.getElementById('precio_unitario').value='{{ number_format($prod->precio, 0, ',', '.') }}';
                                    actualizarTotal();
                                    this.closest('details').removeAttribute('open');
                                    this.closest('details').querySelector('summary').textContent='{{ $prod->nombre }}';"
                                class="block px-3 py-2 hover:bg-gray-100 rounded">
                                {{ $prod->nombre }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </details>

    <input type="hidden" name="producto" id="producto" value="{{ $productoSeleccionado }}">
    @error('producto')
        <p class="text-red-600 text-sm">{{ $message }}</p>
    @enderror
</div>

{{-- CANTIDAD --}}
<div class="mb-4">
    <label for="cantidad" class="block text-sm font-medium text-gray-700">Cantidad</label>
    <input type="number" name="cantidad" id="cantidad" min="1" value="{{ old('cantidad', 1) }}"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"
        oninput="actualizarTotal()">
    @error('cantidad')
        <p class="text-red-600 text-sm">{{ $message }}</p>
    @enderror
</div>

{{-- PRECIO UNITARIO --}}
<div class="mb-4">
    <label for="precio_unitario" class="block text-sm font-medium text-gray-700">Precio unitario</label>
    <div class="relative mt-1">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-700 font-semibold">Gs.</span>
        <input type="text" name="precio_unitario" id="precio_unitario"
            value="{{ old('precio_unitario', isset($venta) ? number_format($venta->precio_unitario ?? $precioProducto, 0, ',', '.') : '') }}"
            class="pl-12 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"
            oninput="actualizarTotal()">
    </div>
    @error('precio_unitario')
        <p class="text-red-600 text-sm">{{ $message }}</p>
    @enderror
</div>

{{-- TOTAL --}}
<div class="mb-4">
    <label for="total" class="block text-sm font-medium text-gray-700">Total</label>
    <div class="relative mt-1">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-700 font-semibold">Gs.</span>
        <input type="text" name="total" id="total" readonly
            value="{{ old('total', isset($venta) ? number_format($venta->total, 0, ',', '.') : '') }}"
            class="pl-12 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 focus:ring focus:ring-blue-200">
    </div>
</div>

{{-- MÉTODO DE PAGO --}}
<div class="mb-4">
    <label for="metodo_pago" class="block text-sm font-medium text-gray-700">Método de pago</label>
    @php
        $metodoSeleccionado = old('metodo_pago', $venta->metodo_pago ?? null);
        $metodoActual = $metodoSeleccionado ?: 'Seleccione un método';
        $metodos = ['Efectivo', 'Tarjeta', 'Transferencia'];
    @endphp

    <details class="relative border rounded px-2 py-1 mt-1 w-full">
        <summary class="cursor-pointer select-none">
            {{ $metodoActual }}
        </summary>
        <div class="absolute bg-white border rounded shadow-md mt-1 w-full z-10 max-h-60 overflow-y-auto">
            <ul>
                @foreach ($metodos as $met)
                    @if (!$metodoSeleccionado || $met != $metodoSeleccionado)
                        <li>
                            <a href="#"
                                onclick="event.preventDefault();
                                    document.getElementById('metodo_pago').value='{{ $met }}';
                                    this.closest('details').removeAttribute('open');
                                    this.closest('details').querySelector('summary').textContent='{{ $met }}';"
                                class="block px-3 py-2 hover:bg-gray-100 rounded">
                                {{ $met }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </details>

    {{-- Campo oculto que se envía en el formulario --}}
    <input type="hidden" name="metodo_pago" id="metodo_pago" value="{{ $metodoSeleccionado }}">

    @error('metodo_pago')
        <p class="text-red-600 text-sm">{{ $message }}</p>
    @enderror
</div>

{{-- ESTADO --}}
<div class="mb-4">
    <label for="estado" class="block text-sm font-medium text-gray-700">Estado</label>
    @php
        $estadoSeleccionado = old('estado', $venta->estado ?? null);
        $estadoActual = $estadoSeleccionado ?: 'Seleccione un estado';
        $estados = ['Pendiente', 'Pagado', 'Anulado'];
    @endphp

    <details class="relative border rounded px-2 py-1 mt-1 w-full">
        <summary class="cursor-pointer select-none">
            {{ $estadoActual }}
        </summary>
        <div class="absolute bg-white border rounded shadow-md mt-1 w-full z-10 max-h-60 overflow-y-auto">
            <ul>
                @foreach ($estados as $est)
                    @if ($est != $estadoSeleccionado)
                        <li>
                            <a href="#"
                                onclick="event.preventDefault();
                                    document.getElementById('estado').value='{{ $est }}';
                                    this.closest('details').removeAttribute('open');
                                    this.closest('details').querySelector('summary').textContent='{{ $est }}';"
                                class="block px-3 py-2 hover:bg-gray-100 rounded">
                                {{ $est }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </details>

    {{-- Campo oculto que se envía en el formulario --}}
    <input type="hidden" name="estado" id="estado" value="{{ $estadoSeleccionado }}">

    @error('estado')
        <p class="text-red-600 text-sm">{{ $message }}</p>
    @enderror
</div>

{{-- FECHA --}}
<div class="mb-4">
    <label for="fecha" class="block text-sm font-medium text-gray-700">Fecha</label>
    <input type="date" name="fecha" id="fecha"
        value="{{ old('fecha', isset($venta) ? $venta->fecha : date('Y-m-d')) }}"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200">
    @error('fecha')
        <p class="text-red-600 text-sm">{{ $message }}</p>
    @enderror
</div>

{{-- BOTONES --}}
<div class="flex justify-end space-x-2">
    <a href="{{ route('ventas.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
        Atrás
    </a>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Guardar
    </button>
</div>

{{-- SCRIPTS --}}
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

        // 📆 Mostrar calendario al hacer clic en cualquier parte del campo
        if (fecha) {
            fecha.addEventListener('focus', function() {
                this.showPicker && this.showPicker();
            });
        }

        // 💲 Actualizar precio y total cuando cambia el producto
        producto.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const precioData = selected.getAttribute('data-precio');
            precio.value = formatearMiles(precioData || '0');
            actualizarTotal();
        });

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
