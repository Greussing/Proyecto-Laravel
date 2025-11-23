<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Ventas</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        body { margin: 20px; }
        h1 { font-size: 18px; margin-bottom: 5px; }
        h2 { font-size: 13px; margin-top: 0; margin-bottom: 15px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cccccc; padding: 4px 6px; }
        th { background-color: #f0f0f0; text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .small { font-size: 9px; color: #777; }
    </style>
</head>
<body>
    <h1>Listado de Ventas</h1>
    <h2>Detalle de ventas filtradas</h2>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 35px;">ID</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th class="text-right" style="width: 90px;">Total</th>
                <th class="text-center" style="width: 80px;">Método</th>
                <th class="text-center" style="width: 80px;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ventas as $v)
                <tr>
                    <td class="text-center">#{{ $v->id }}</td>
                    <td>{{ $v->fecha ? $v->fecha->format('d/m/Y') : '-' }}</td>
                    <td>{{ $v->clienteRelacion->nombre ?? '-' }}</td>
                    <td class="text-right">
                        Gs. {{ number_format($v->total, 0, ',', '.') }}
                    </td>
                    <td class="text-center">{{ $v->metodo_pago }}</td>
                    <td class="text-center">{{ $v->estado }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">
                        No hay ventas registradas con los filtros aplicados.
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