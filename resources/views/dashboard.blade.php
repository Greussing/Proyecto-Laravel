<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <x-stat-card title="Total Productos" :value="$totalProductos" color="blue">
            <x-slot name="icon">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card title="Ventas Hoy" value="Gs. {{ number_format($ventasHoy, 0, ',', '.') }}" color="green">
            <x-slot name="icon">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card title="Movimientos Hoy" :value="$movimientosHoy" color="purple">
            <x-slot name="icon">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card title="Stock Crítico" :value="$productosCriticos" color="red">
            <x-slot name="icon">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </x-slot>
        </x-stat-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card>
            <x-slot name="header">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Últimas Ventas</h3>
            </x-slot>
            
            @if($ultimasVentas->isEmpty())
                <p class="text-gray-500 dark:text-gray-400">No hay ventas recientes.</p>
            @else
                <x-table :headers="['ID', 'Cliente', 'Total', 'Estado', 'Fecha']">
                    @foreach($ultimasVentas as $venta)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="py-3 px-4 text-sm">{{ $venta->id }}</td>
                            <td class="py-3 px-4 text-sm">{{ $venta->clienteRelacion->nombre ?? 'N/A' }}</td>
                            <td class="py-3 px-4 text-sm font-semibold">Gs. {{ number_format($venta->total, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-sm">
                                <x-badge :type="$venta->estado == 'Pagado' ? 'success' : ($venta->estado == 'Pendiente' ? 'warning' : 'danger')">
                                    {{ $venta->estado }}
                                </x-badge>
                            </td>
                            <td class="py-3 px-4 text-sm">{{ $venta->created_at->format('d/m H:i') }}</td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </x-card>

        <x-card>
            <x-slot name="header">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Últimos Movimientos</h3>
            </x-slot>

            @if($ultimosMovimientos->isEmpty())
                <p class="text-gray-500 dark:text-gray-400">No hay movimientos recientes.</p>
            @else
                <x-table :headers="['Producto', 'Tipo', 'Cant.', 'Usuario', 'Fecha']">
                    @foreach($ultimosMovimientos as $movimiento)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="py-3 px-4 text-sm">{{ $movimiento->producto->nombre ?? 'N/A' }}</td>
                            <td class="py-3 px-4 text-sm">
                                <x-badge :type="$movimiento->tipo == 'entrada' ? 'success' : 'danger'">
                                    {{ ucfirst($movimiento->tipo) }}
                                </x-badge>
                            </td>
                            <td class="py-3 px-4 text-sm font-semibold">{{ $movimiento->cantidad }}</td>
                            <td class="py-3 px-4 text-sm">{{ $movimiento->user->name ?? 'Sistema' }}</td>
                            <td class="py-3 px-4 text-sm">{{ $movimiento->created_at->format('d/m H:i') }}</td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </x-card>
    </div>
</x-app-layout>
