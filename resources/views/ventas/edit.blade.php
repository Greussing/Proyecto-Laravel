<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Editar Venta
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">

                {{-- Mensajes de error globales --}}
                @if ($errors->any())
                    <div class="mb-4">
                        <ul class="text-red-500 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Formulario de edición --}}
                <form action="{{ route('ventas.update', $venta->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Mantener página actual --}}
                    <input type="hidden" name="page" value="{{ request('page', 1) }}">

                    @include('ventas._form', [
                        'venta' => $venta,
                        'clientes' => $clientes,
                        'productos' => $productos,
                    ])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
