<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Cambiar Contraseña') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                {{-- Información del usuario --}}
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-md">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Usuario</h3>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        <strong>Nombre:</strong> {{ $user->name }}<br>
                        <strong>Email:</strong> {{ $user->email }}
                    </p>
                </div>

                <form method="POST" action="{{ route('users.update-password', $user->id) }}">
                    @csrf
                    @method('PUT')

                    {{-- Nueva Contraseña --}}
                    <div class="mb-4">
                        <x-input-label for="password" :value="__('Nueva Contraseña')" />
                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autofocus />
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Mínimo 8 caracteres</p>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    {{-- Confirmar Nueva Contraseña --}}
                    <div class="mb-4">
                        <x-input-label for="password_confirmation" :value="__('Confirmar Nueva Contraseña')" />
                        <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    {{-- Advertencia --}}
                    <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md">
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            <strong>⚠️ Advertencia:</strong> Al cambiar la contraseña, el usuario deberá usar la nueva contraseña para iniciar sesión.
                        </p>
                    </div>

                    {{-- Botones --}}
                    <div class="flex items-center justify-end gap-4 mt-6">
                        <a href="{{ route('users.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Cancelar
                        </a>
                        <x-primary-button>
                            Cambiar Contraseña
                        </x-primary-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
