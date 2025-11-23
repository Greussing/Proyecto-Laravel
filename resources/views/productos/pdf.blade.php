<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Productos</title>
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
    <h1>Listado de Productos</h1>
    <h2>Inventario actual</h2>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 35px;">ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th class="text-center" style="width: 70px;">Cantidad</th>
                <th class="text-right" style="width: 90px;">Precio</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($productos as $p)
                <tr>
                    <td class="text-center">#{{ $p->id }}</td>
                    <td>{{ $p->nombre }}</td>
                    <td>{{ $p->categoriaRelacion->nombre ?? 'Sin categoría' }}</td>
                    <td class="text-center">{{ $p->cantidad }}</td>
                    <td class="text-right">
                        Gs. {{ number_format($p->precio, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">
                        No hay productos registrados.
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