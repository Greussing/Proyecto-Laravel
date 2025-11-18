{{-- 
|--------------------------------------------------------------------------
| Formulario de creación / edición de producto (create.blade.php / edit.blade.php)
|--------------------------------------------------------------------------
| Este formulario permite crear o actualizar un producto.
| Conecta con:
|   - ProductoController@store   → para crear
|   - ProductoController@update  → para actualizar
| Usa:
|   - $producto   → objeto producto (solo en edición)
|   - $categorias → listado de categorías
--}}

{{-- Nombre del producto --}}
<div class="mb-4">
    <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
    <input type="text" name="nombre" id="nombre"
           value="{{ old('nombre', $producto->nombre ?? '') }}"
           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200">
    @error('nombre')
        <p class="text-red-600 text-sm">{{ $message }}</p>
    @enderror
</div>

{{-- Cantidad / Stock --}}
<div class="mb-4">
    <label for="cantidad" class="block text-sm font-medium text-gray-700">Cantidad</label>
    <input type="number" name="cantidad" id="cantidad" min="0"
           value="{{ old('cantidad', $producto->cantidad ?? '') }}"
           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200">
    @error('cantidad')
        <p class="text-red-600 text-sm">{{ $message }}</p>
    @enderror
</div>

{{-- Precio --}}
<div class="mb-4">
    <label for="precio" class="block text-sm font-medium text-gray-700">Precio</label>

    <div class="relative mt-1">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-700 font-semibold">Gs.</span>
        <input type="text" name="precio" id="precio"
               value="{{ old('precio', isset($producto) ? number_format($producto->precio, 0, ',', '.') : '') }}"
               class="pl-12 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200">
    </div>

    @error('precio')
        <p class="text-red-600 text-sm">{{ $message }}</p>
    @enderror
</div>

{{-- Script para formatear precio con separador de miles --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const precioInput = document.getElementById('precio');
        if (!precioInput) return;

        function formatNumber(value) {
            return value
                .replace(/\D/g, '')          // solo números
                .replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // separador de miles
        }

        // Formatear valor inicial si viene con datos
        if (precioInput.value) {
            precioInput.value = formatNumber(precioInput.value);
        }

        // Mientras el usuario escribe
        precioInput.addEventListener('input', function () {
            let cursorPos = this.selectionStart;
            let originalLength = this.value.length;
            this.value = formatNumber(this.value);
            let newLength = this.value.length;
            this.selectionEnd = cursorPos + (newLength - originalLength);
        });

        // Antes de enviar → dejar solo número puro
        if (precioInput.form) {
            precioInput.form.addEventListener('submit', function () {
                precioInput.value = precioInput.value.replace(/\D/g, '');
            });
        }
    });
</script>

{{-- Selección de Categoría --}}
<div class="mb-4">
    <label for="categoria" class="block text-sm font-medium text-gray-700">Categoría</label>

    @php
        $categoriaSeleccionada = old('categoria', $producto->categoriaRelacion->id ?? null);
        $categoriaActual = $categoriaSeleccionada
            ? ($categorias->firstWhere('id', $categoriaSeleccionada)->nombre ?? 'Seleccione una categoría')
            : 'Seleccione una categoría';
    @endphp

    <details class="relative border rounded px-2 py-1 mt-1 w-full">
        <summary class="cursor-pointer select-none">
            {{ $categoriaActual }}
        </summary>
        <div class="absolute bg-white border rounded shadow-md mt-1 w-full z-10 max-h-60 overflow-y-auto">
            <ul>
                @foreach ($categorias as $cat)
                    @if (!$categoriaSeleccionada || $cat->id != $categoriaSeleccionada)
                        <li>
                            <a href="#"
                               onclick="event.preventDefault();
                                   document.getElementById('categoria').value='{{ $cat->id }}';
                                   this.closest('details').removeAttribute('open');
                                   this.closest('details').querySelector('summary').textContent='{{ $cat->nombre }}';"
                               class="block px-3 py-2 hover:bg-gray-100 rounded">
                                {{ $cat->nombre }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </details>

    {{-- Campo oculto que se envía en el formulario --}}
    <input type="hidden" name="categoria" id="categoria" value="{{ $categoriaSeleccionada }}">

    @error('categoria')
        <p class="text-red-600 text-sm">{{ $message }}</p>
    @enderror
</div>

{{-- Fecha de vencimiento --}}
<div class="mb-3">
    <label class="block text-sm font-medium text-gray-700">Fecha de vencimiento</label>
    <input type="date" name="fecha_vencimiento"
           class="border rounded px-3 py-2 w-full"
           value="{{ old('fecha_vencimiento', isset($producto) && $producto->fecha_vencimiento ? $producto->fecha_vencimiento->format('Y-m-d') : '') }}">
    @error('fecha_vencimiento')
        <p class="text-red-600 text-sm">{{ $message }}</p>
    @enderror
</div>

{{-- Lote (opcional) --}}
<div class="mb-3">
    <label class="block text-sm font-medium text-gray-700">Lote</label>
    <input type="text" name="lote"
           class="border rounded px-3 py-2 w-full"
           placeholder="Ej: LOTE A15"
           value="{{ old('lote', $producto->lote ?? '') }}">
    @error('lote')
        <p class="text-red-600 text-sm">{{ $message }}</p>
    @enderror
</div>

{{-- Botones de acción --}}
<div class="flex justify-end space-x-2 mt-4">
    <a href="{{ route('productos.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
        Atrás
    </a>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Guardar
    </button>
</div>