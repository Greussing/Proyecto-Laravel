<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AnalisisProductosExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $stats;

    protected $desde;
    protected $hasta;
    protected $diasPeriodo;

    /**
     * @param  \Illuminate\Support\Collection  $stats
     */
    public function __construct(Collection $stats, $desde, $hasta, int $diasPeriodo)
    {
        $this->stats       = $stats;
        $this->desde       = $desde;
        $this->hasta       = $hasta;
        $this->diasPeriodo = $diasPeriodo;
    }

    public function collection()
    {
        return $this->stats;
    }

    public function headings(): array
    {
        return [
            'Producto',
            'Vendido (u)',
            'Ingreso total (Gs.)',
            '% de ingresos',
            'Stock actual',
            'Rotación',
            'Días sin venta',
            'Última venta',
        ];
    }

    public function map($row): array
    {
        return [
            $row->producto,
            $row->vendido,
            $row->ingreso_total,
            $row->porcentaje_ingresos,
            $row->stock_actual,
            $row->rotacion,
            $row->dias_sin_venta !== null ? $row->dias_sin_venta . ' días' : '—',
            $row->ultima_venta ? $row->ultima_venta->format('d/m/Y H:i') : '—',
        ];
    }
}