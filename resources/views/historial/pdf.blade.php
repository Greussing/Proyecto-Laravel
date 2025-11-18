<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Movimientos</title>
    <style>
        * {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        body {
            margin: 20px;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        h2 {
            font-size: 13px;
            margin-top: 0;
            margin-bottom: 15px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #cccccc;
            padding: 4px 6px;
        }

        th {
            background-color: #f0f0f0;
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .small {
            font-size: 9px;
            color: #777;
        }
    </style>
</head>
<body>
    <h1>Historial de Movimientos</h1>
    <h2>Listado detallado</h2>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 35px;">ID</th>
                <th style="width: 140px;">Producto</th>
                <th style="width: 120px;">Usuario</th>
                <th class="text-center" style="width: 80px;">Acción</th>
                <th>Descripción</th>
                <th class="text-center" style="width: 90px;">Fecha</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($historiales as $h)
                <tr>
                    {{-- ID --}}
                    <td class="text-center">
                        {{ $h->id }}
                    </td>

                    {{-- Producto (incluye eliminados gracias a withTrashed) --}}
                    <td>
                        {{ $h->producto->nombre ?? 'N/A' }}
                    </td>

                    {{-- Usuario --}}
                    <td>
                        {{ $h->usuario->name ?? 'Admin' }}
                    </td>

                    {{-- Acción --}}
                    <td class="text-center">
                        {{ ucfirst($h->accion) }}
                    </td>

                    {{-- Descripción --}}
                    <td>
                        {{ $h->descripcion ?? '—' }}
                    </td>

                    {{-- Fecha --}}
                    <td class="text-center">
                        {{ $h->created_at ? $h->created_at->format('d/m/Y H:i') : '—' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">
                        No hay registros en el historial.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="small">
        Generado el {{ now()->format('d/m/Y H:i') }}.
    </p>
</body>
</html>