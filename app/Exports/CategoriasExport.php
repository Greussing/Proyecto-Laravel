<?php

namespace App\Exports;

use App\Models\Categoria;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CategoriasExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Categoria::withCount('productos')
            ->with(['productos' => function($q) {
                $q->select('id', 'categoria', 'precio', 'cantidad');
            }])
            ->orderBy('nombre')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Cantidad de Productos',
            'Valor Inventario (Gs.)',
            'Productos Stock Crítico',
            'Estado',
        ];
    }

    public function map($categoria): array
    {
        // Calcular valor de inventario
        $valorInventario = $categoria->productos->sum(function ($producto) {
            return $producto->precio * $producto->cantidad;
        });
        
        // Calcular productos con stock crítico
        $stockCritico = $categoria->productos->filter(function ($producto) {
            return $producto->cantidad <= 5;
        })->count();
        
        // Determinar estado
        $estado = $categoria->productos_count > 0 ? 'Activa' : 'Vacía';

        return [
            $categoria->id,
            $categoria->nombre,
            $categoria->productos_count,
            number_format($valorInventario, 0, ',', '.'),
            $stockCritico,
            $estado,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
