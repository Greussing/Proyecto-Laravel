<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Editar Cliente') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                <form method="POST" action="{{ route('clientes.update', $cliente->id) }}">
                    @csrf
                    @method('PUT')

                    {{-- Nombre --}}
                    <div class="mb-4">
                        <x-input-label for="nombre" :value="__('Nombre del Cliente')" />
                        <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre', $cliente->nombre)" required autofocus />
                        <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
                    </div>

                    {{-- Email --}}
                    <div class="mb-4">
                        <x-input-label for="email" :value="__('Email (opcional)')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $cliente->email)" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    {{-- Teléfono --}}
                    <div class="mb-4">
                        <x-input-label for="telefono" :value="__('Teléfono (opcional)')" />
                        <x-text-input id="telefono" class="block mt-1 w-full" type="text" name="telefono" :value="old('telefono', $cliente->telefono)" />
                        <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
                    </div>

                    {{-- Dirección --}}
                    <div class="mb-4">
                        <x-input-label for="direccion" :value="__('Dirección (opcional)')" />
                        <textarea id="direccion" name="direccion" rows="3"
                                  class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full">{{ old('direccion', $cliente->direccion) }}</textarea>
                        <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
                    </div>

                    {{-- Botones --}}
                    <div class="flex items-center justify-end gap-4 mt-6">
                        <a href="{{ route('clientes.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Cancelar
                        </a>
                        <x-primary-button>
                            Actualizar Cliente
                        </x-primary-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
