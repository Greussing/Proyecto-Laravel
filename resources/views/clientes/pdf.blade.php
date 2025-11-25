<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Clientes</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .header { text-align: center; margin-bottom: 20px; }
        .badge { padding: 2px 5px; border-radius: 4px; font-size: 10px; color: white; }
        .badge-success { background-color: #10B981; }
        .badge-gray { background-color: #6B7280; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Reporte de Clientes</h2>
        <p>Fecha: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Contacto</th>
                <th>Total Gastado</th>
                <th>Última Compra</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->id }}</td>
                    <td>{{ $cliente->nombre }}</td>
                    <td>
                        {{ $cliente->email }}<br>
                        {{ $cliente->telefono }}
                    </td>
                    <td>Gs. {{ number_format($cliente->ventas_sum_total ?? 0, 0, ',', '.') }}</td>
                    <td>
                        {{ $cliente->ventas_max_fecha ? \Carbon\Carbon::parse($cliente->ventas_max_fecha)->format('d/m/Y') : '—' }}
                    </td>
                    <td>
                        @php
                            $activo = $cliente->ventas_max_fecha && \Carbon\Carbon::parse($cliente->ventas_max_fecha)->diffInDays(now()) <= 90;
                        @endphp
                        <span class="badge {{ $activo ? 'badge-success' : 'badge-gray' }}">
                            {{ $activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
