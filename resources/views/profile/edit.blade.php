<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Estadísticas --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg text-center">
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Miembro Desde</div>
                    <div class="mt-2 text-xl font-bold text-gray-900 dark:text-white">
                        {{ $user->created_at->format('d/m/Y') }}
                    </div>
                </div>
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg text-center">
                    <div class="text-xs font-semibold text-blue-500 dark:text-blue-400 uppercase">Ventas Realizadas</div>
                    <div class="mt-2 text-xl font-bold text-blue-700 dark:text-blue-200">
                        {{ $ventasRealizadas }}
                    </div>
                </div>
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg text-center">
                    <div class="text-xs font-semibold text-green-500 dark:text-green-400 uppercase">Total Vendido</div>
                    <div class="mt-2 text-xl font-bold text-green-700 dark:text-green-200">
                        Gs. {{ number_format($totalVendido, 0, ',', '.') }}
                    </div>
                </div>
            </div>
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
