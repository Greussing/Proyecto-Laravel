<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-white">
            Historial de Movimientos
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow rounded-lg p-6">

                {{-- BUSCADOR --}}
                <form method="GET" action="{{ route('historial.index') }}" class="mb-4 flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Buscar por descripción o acción"
                        class="border rounded px-3 py-1 w-60 focus:ring-2 focus:ring-indigo-500">

                    <button type="submit" class="bg-indigo-600 text-white px-4 py-1 rounded hover:bg-indigo-700">
                        Buscar
                    </button>
                </form>

                {{-- TABLA --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full border">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border px-4 py-2">ID</th>
                                <th class="border px-4 py-2">Producto</th>
                                <th class="border px-4 py-2">Acción</th>
                                <th class="border px-4 py-2">Descripción</th>
                                <th class="border px-4 py-2">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historiales as $item)
                                <tr>
                                    <td class="border px-4 py-2">{{ $item->id }}</td>
                                    <td class="border px-4 py-2">{{ $item->producto->nombre ?? 'N/A' }}</td>
                                    <td class="border px-4 py-2">{{ $item->accion }}</td>
                                    <td class="border px-4 py-2">{{ $item->descripcion }}</td>
                                    <td class="border px-4 py-2">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-gray-500">
                                        No hay registros en el historial.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINACIÓN --}}
                <div class="mt-4">
                    {{ $historiales->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
