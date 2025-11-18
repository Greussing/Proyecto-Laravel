<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Movimientos de Stock</title>
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

        .text-right {
            text-align: right;
        }

        .small {
            font-size: 9px;
            color: #777;
        }
    </style>
</head>
<body>
    <h1>Movimientos de Stock</h1>
    <h2>Listado detallado</h2>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 35px;">ID</th>
                <th>Cliente</th>
                <th>Producto</th>
                <th class="text-center" style="width: 70px;">Tipo</th>
                <th class="text-center" style="width: 60px;">Cantidad</th>
                <th class="text-center" style="width: 70px;">Stock antes</th>
                <th class="text-center" style="width: 80px;">Stock después</th>
                <th>Detalle</th>
                <th class="text-center" style="width: 90px;">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movimientos as $m)
                <tr>
                    <td class="text-center">
                        {{ $m->id }}
                    </td>
                    <td>
                        {{ $m->clienteRelacion->nombre ?? 'N/A' }}
                    </td>
                    <td>
                        {{ $m->producto->nombre ?? 'N/A' }}
                    </td>
                    <td class="text-center">
                        {{ ucfirst($m->tipo) }}
                    </td>
                    <td class="text-center">
                        {{ $m->cantidad }}
                    </td>
                    <td class="text-center">
                        {{ $m->stock_antes }}
                    </td>
                    <td class="text-center">
                        {{ $m->stock_despues }}
                    </td>
                    <td>
                        {{ $m->detalle }}
                    </td>
                    <td class="text-center">
                        {{ $m->created_at->format('d/m/Y H:i') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">
                        No hay movimientos de stock registrados.
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