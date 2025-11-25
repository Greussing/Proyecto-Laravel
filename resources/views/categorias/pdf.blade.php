<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categorías - Listado</title>
    <style>
        * {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
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
            padding: 5px 6px;
        }
        th {
            background-color: #f0f0f0;
            text-align: left;
            font-weight: bold;
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
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-gray {
            background-color: #e9ecef;
            color: #495057;
        }
    </style>
</head>
<body>
    <h1>Listado de Categorías</h1>
    <h2>Reporte completo con métricas</h2>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 40px;">ID</th>
                <th>Nombre</th>
                <th class="text-center" style="width: 70px;">Productos</th>
                <th class="text-right" style="width: 110px;">Valor Inventario</th>
                <th class="text-center" style="width: 80px;">Stock Crítico</th>
                <th class="text-center" style="width: 60px;">Estado</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($categorias as $cat)
                @php
                    // Calcular métricas
                    $valorInventario = $cat->productos->sum(function ($p) {
                        return $p->precio * $p->cantidad;
                    });
                    
                    $stockCritico = $cat->productos->filter(function ($p) {
                        return $p->cantidad <= 5;
                    })->count();
                    
                    $estado = $cat->productos_count > 0 ? 'Activa' : 'Vacía';
                    $estadoBadge = $cat->productos_count > 0 ? 'badge-success' : 'badge-gray';
                @endphp
                <tr>
                    <td class="text-center">{{ $cat->id }}</td>
                    <td>{{ $cat->nombre }}</td>
                    <td class="text-center">{{ $cat->productos_count }}</td>
                    <td class="text-right">Gs. {{ number_format($valorInventario, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $stockCritico }}</td>
                    <td class="text-center">
                        <span class="badge {{ $estadoBadge }}">{{ $estado }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">
                        No hay categorías registradas.
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
