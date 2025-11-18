<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Análisis de Productos</title>
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
        .text-right {
            text-align: right;
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

<h1>Análisis de Productos</h1>
<h2>Período: {{ $desde->format('d/m/Y') }} – {{ $hasta->format('d/m/Y') }} ({{ $diasPeriodo }} días)</h2>

<p>
    <strong>Ingreso total en el período:</strong>
    Gs. {{ number_format($ingresoTotalGlobal, 0, ',', '.') }}
</p>

<table>
    <thead>
        <tr>
            <th>Producto</th>
            <th class="text-right">Vendido (u)</th>
            <th class="text-right">Ingreso total</th>
            <th class="text-right">% Ingresos</th>
            <th class="text-right">Stock actual</th>
            <th class="text-center">Rotación</th>
            <th class="text-center">Días sin venta</th>
            <th class="text-center">Última venta</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($stats as $row)
            <tr>
                <td>{{ $row->producto }}</td>
                <td class="text-right">{{ $row->vendido }}</td>
                <td class="text-right">
                    Gs. {{ number_format($row->ingreso_total, 0, ',', '.') }}
                </td>
                <td class="text-right">
                    {{ number_format($row->porcentaje_ingresos, 2, ',', '.') }} %
                </td>
                <td class="text-right">{{ $row->stock_actual }}</td>
                <td class="text-center">{{ $row->rotacion }}</td>
                <td class="text-center">
                    {{ $row->dias_sin_venta !== null ? $row->dias_sin_venta . ' días' : '—' }}
                </td>
                <td class="text-center">
                    {{ $row->ultima_venta ? $row->ultima_venta->format('d/m/Y H:i') : '—' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">
                    No hay datos de ventas en el período seleccionado.
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