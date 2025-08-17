@csrf

@if(isset($producto))
    @method('PUT')
@endif

<div class="mb-4">
    <label for="nombre" class="block text-gray-700 font-semibold">Nombre:</label>
    <input type="text" name="nombre" id="nombre"
           value="{{ old('nombre', $producto->nombre ?? '') }}"
           class="w-full border-gray-300 rounded mt-1 @error('nombre') border-red-500 @enderror" required>
    @error('nombre')
        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label for="cantidad" class="block text-gray-700 font-semibold">Cantidad:</label>
    <input type="number" name="cantidad" id="cantidad"
           value="{{ old('cantidad', $producto->cantidad ?? '') }}"
           class="w-full border-gray-300 rounded mt-1 @error('cantidad') border-red-500 @enderror" required>
    @error('cantidad')
        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label for="precio" class="block text-gray-700 font-semibold">Precio:</label>
    <input type="number" step="0.01" name="precio" id="precio"
           value="{{ old('precio', $producto->precio ?? '') }}"
           class="w-full border-gray-300 rounded mt-1 @error('precio') border-red-500 @enderror" required>
    @error('precio')
        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label for="categoria" class="block text-gray-700 font-semibold">Categoría:</label>
    <input type="text" name="categoria" id="categoria"
           value="{{ old('categoria', $producto->categoria ?? '') }}"
           class="w-full border-gray-300 rounded mt-1 @error('categoria') border-red-500 @enderror">
    @error('categoria')
        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="flex justify-end">
    <button type="button" onclick="history.back()" class="text-gray-600 hover:underline mr-4">Cancelar</button>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        {{ isset($producto) ? 'Guardar Cambios' : 'Guardar' }}
    </button>
</div>