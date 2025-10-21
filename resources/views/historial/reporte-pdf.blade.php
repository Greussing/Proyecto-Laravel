<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Historial de Movimientos</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }

        th {
            background: #eee;
        }
    </style>
</head>

<body>
    <h2 style="text-align:center;">Historial de Movimientos de Productos</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th>Acción</th>
                <th>Descripción</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($historiales as $h)
                <tr>
                    <td>{{ $h->id }}</td>
                    <td>{{ $h->producto->nombre ?? '—' }}</td>
                    <td>{{ ucfirst($h->accion) }}</td>
                    <td>{{ $h->descripcion }}</td>
                    <td>{{ $h->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
