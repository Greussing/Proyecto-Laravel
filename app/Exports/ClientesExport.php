<?php

namespace App\Exports;

use App\Models\Cliente;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClientesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $request;

    public function __construct(array $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Cliente::withSum('ventas', 'total')
            ->withMax('ventas', 'fecha');

        if (isset($this->request['search']) && $this->request['search']) {
            $termino = $this->request['search'];
            $query->where(function($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%")
                  ->orWhere('email', 'like', "%{$termino}%")
                  ->orWhere('telefono', 'like', "%{$termino}%");
            });
        }

        if (isset($this->request['ordenar']) && $this->request['ordenar']) {
            switch ($this->request['ordenar']) {
                case 'mayor_gasto': $query->orderByDesc('ventas_sum_total'); break;
                case 'menor_gasto': $query->orderBy('ventas_sum_total'); break;
                case 'recientes': $query->latest(); break;
                case 'antiguos': $query->oldest(); break;
                default: $query->latest();
            }
        } else {
            $query->latest();
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Email',
            'Teléfono',
            'Dirección',
            'Total Gastado',
            'Última Compra',
            'Fecha Registro',
        ];
    }

    public function map($cliente): array
    {
        return [
            $cliente->id,
            $cliente->nombre,
            $cliente->email,
            $cliente->telefono,
            $cliente->direccion,
            $cliente->ventas_sum_total ?? 0,
            $cliente->ventas_max_fecha ? \Carbon\Carbon::parse($cliente->ventas_max_fecha)->format('d/m/Y H:i') : 'Sin compras',
            $cliente->created_at->format('d/m/Y'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']]],
        ];
    }
}
