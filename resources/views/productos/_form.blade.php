{{-- 
    Formulario de creación / edición de producto
--}}

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Nombre del producto --}}
    <div class="col-span-2">
        <x-input-label for="nombre" :value="__('Nombre')" />
        <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre', $producto->nombre ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
    </div>

    {{-- Cantidad / Stock --}}
    <div>
        <x-input-label for="cantidad" :value="__('Cantidad')" />
        <x-text-input id="cantidad" class="block mt-1 w-full" type="number" name="cantidad" :value="old('cantidad', $producto->cantidad ?? '')" min="0" required />
        <x-input-error :messages="$errors->get('cantidad')" class="mt-2" />
    </div>

    {{-- Precio --}}
    <div>
        <x-input-label for="precio" :value="__('Precio (Gs.)')" />
        <div class="relative mt-1">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-semibold">Gs.</span>
            <x-text-input id="precio" class="block w-full pl-12" type="text" name="precio" :value="old('precio', isset($producto) ? number_format($producto->precio, 0, ',', '.') : '')" required />
        </div>
        <x-input-error :messages="$errors->get('precio')" class="mt-2" />
    </div>

    {{-- Selección de Categoría --}}
    <div>
        <x-input-label for="categoria" :value="__('Categoría')" />
        
        @php
            $categoriaSeleccionada = old('categoria', $producto->categoriaRelacion->id ?? null);
            $categoriaActual = $categoriaSeleccionada
                ? ($categorias->firstWhere('id', $categoriaSeleccionada)->nombre ?? 'Seleccione una categoría')
                : 'Seleccione una categoría';
        @endphp

        <div x-data="{ open: false, selected: '{{ $categoriaActual }}', value: '{{ $categoriaSeleccionada }}' }" class="relative mt-1">
            <button type="button" @click="open = !open" @click.away="open = false" class="relative w-full cursor-default rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 py-2 pl-3 pr-10 text-left shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm text-gray-700 dark:text-gray-300">
                <span class="block truncate" x-text="selected"></span>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </span>
            </button>

            <ul x-show="open" class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white dark:bg-gray-900 py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm" style="display: none;">
                @foreach ($categorias as $cat)
                    <li class="text-gray-900 dark:text-gray-200 relative cursor-default select-none py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white cursor-pointer"
                        @click="selected = '{{ $cat->nombre }}'; value = '{{ $cat->id }}'; open = false">
                        <span class="block truncate font-normal">
                            {{ $cat->nombre }}
                        </span>
                    </li>
                @endforeach
            </ul>
            
            <input type="hidden" name="categoria" id="categoria_input" :value="value">
        </div>
        <x-input-error :messages="$errors->get('categoria')" class="mt-2" />
    </div>

    {{-- Fecha de vencimiento --}}
    <div>
        <x-input-label for="fecha_vencimiento" :value="__('Fecha de vencimiento')" />
        <x-text-input id="fecha_vencimiento" class="block mt-1 w-full" type="date" name="fecha_vencimiento" :value="old('fecha_vencimiento', isset($producto) && $producto->fecha_vencimiento ? $producto->fecha_vencimiento->format('Y-m-d') : '')" />
        <x-input-error :messages="$errors->get('fecha_vencimiento')" class="mt-2" />
    </div>

    {{-- Lote (opcional) --}}
    <div class="col-span-2">
        <x-input-label for="lote" :value="__('Lote (Opcional)')" />
        <x-text-input id="lote" class="block mt-1 w-full" type="text" name="lote" :value="old('lote', $producto->lote ?? '')" placeholder="Ej: LOTE A15" />
        <x-input-error :messages="$errors->get('lote')" class="mt-2" />
    </div>
</div>

{{-- Botones de acción --}}
<div class="flex justify-end space-x-4 mt-6">
    <a href="{{ route('productos.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
        {{ __('Cancelar') }}
    </a>
    <x-primary-button>
        {{ __('Guardar Producto') }}
    </x-primary-button>
</div>

{{-- Script para formatear precio --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const precioInput = document.getElementById('precio');
        if (!precioInput) return;

        function formatNumber(value) {
            return value
                .replace(/\D/g, '')
                .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        precioInput.addEventListener('input', function () {
            let cursorPos = this.selectionStart;
            let originalLength = this.value.length;
            this.value = formatNumber(this.value);
            let newLength = this.value.length;
            this.selectionEnd = cursorPos + (newLength - originalLength);
        });

        if (precioInput.form) {
            precioInput.form.addEventListener('submit', function () {
                precioInput.value = precioInput.value.replace(/\D/g, '');
            });
        }
    });
</script>