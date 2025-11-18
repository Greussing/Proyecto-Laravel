<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Caducidad de Productos</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }
        h1, h2 {
            margin: 0 0 6px 0;
        }
        h1 {
            font-size: 18px;
        }
        h2 {
            font-size: 14px;
            margin-top: 14px;
        }
        p {
            margin: 2px 0 6px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #444;
            padding: 4px;
            text-align: left;
        }
        th {
            background: #eeeeee;
            font-weight: bold;
        }
        .section-title {
            margin-top: 14px;
            margin-bottom: 4px;
            font-weight: bold;
        }
        .small {
            font-size: 10px;
            color: #555;
        }
    </style>
</head>
<body>

    <h1>Reporte de Caducidad de Productos</h1>
    <p class="small">
        Fecha de generación: {{ now()->format('d/m/Y H:i') }}
    </p>

    {{-- RESUMEN GENERAL --}}
    <p class="small">
        Total próximos a vencer (≤ 30 días): <strong>{{ $proximos->count() }}</strong><br>
        Total productos vencidos: <strong>{{ $vencidos->count() }}</strong><br>
        Total en revisión (31–60 días): <strong>{{ $revision->count() }}</strong>
    </p>

    {{-- SECCIÓN: PRÓXIMOS A VENCER --}}
    <h2>Próximos a vencer (≤ 30 días)</h2>

    @if ($proximos->isEmpty())
        <p>No hay productos próximos a vencer en los próximos 30 días.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Lote</th>
                    <th>Fecha de vencimiento</th>
                    <th>Días restantes</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($proximos as $p)
                    <tr>
                        <td>{{ $p->nombre }}</td>
                        <td>{{ $p->categoriaRelacion->nombre ?? '-' }}</td>
                        <td>{{ $p->lote ?? '—' }}</td>
                        <td>{{ optional($p->fecha_vencimiento)->format('d/m/Y') }}</td>
                        <td>{{ $p->dias_restantes }}</td>
                        <td>{{ $p->cantidad }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- SECCIÓN: VENCIDOS --}}
    <h2>Productos vencidos</h2>

    @if ($vencidos->isEmpty())
        <p>No hay productos vencidos registrados.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Lote</th>
                    <th>Fecha de vencimiento</th>
                    <th>Días vencido</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($vencidos as $p)
                    <tr>
                        <td>{{ $p->nombre }}</td>
                        <td>{{ $p->categoriaRelacion->nombre ?? '-' }}</td>
                        <td>{{ $p->lote ?? '—' }}</td>
                        <td>{{ optional($p->fecha_vencimiento)->format('d/m/Y') }}</td>
                        <td>{{ abs($p->dias_restantes) }}</td>
                        <td>{{ $p->cantidad }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- SECCIÓN: EN REVISIÓN --}}
    <h2>En revisión (31–60 días)</h2>

    @if ($revision->isEmpty())
        <p>No hay productos en ventana de revisión.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Lote</th>
                    <th>Fecha de vencimiento</th>
                    <th>Días restantes</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($revision as $p)
                    <tr>
                        <td>{{ $p->nombre }}</td>
                        <td>{{ $p->categoriaRelacion->nombre ?? '-' }}</td>
                        <td>{{ $p->lote ?? '—' }}</td>
                        <td>{{ optional($p->fecha_vencimiento)->format('d/m/Y') }}</td>
                        <td>{{ $p->dias_restantes }}</td>
                        <td>{{ $p->cantidad }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</body>
</html>