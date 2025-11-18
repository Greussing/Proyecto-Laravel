<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CaducidadProductosExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $rows;

    /**
     * @param  \Illuminate\Support\Collection  $proximos
     * @param  \Illuminate\Support\Collection  $vencidos
     * @param  \Illuminate\Support\Collection  $revision
     */
    public function __construct($proximos, $vencidos, $revision)
    {
        // Unificamos en una sola colección con una columna "tipo"
        $this->rows = collect();

        foreach ($proximos as $p) {
            $this->rows->push([
                'tipo'              => 'Próximo a vencer',
                'producto'          => $p,
            ]);
        }

        foreach ($vencidos as $p) {
            $this->rows->push([
                'tipo'              => 'Vencido',
                'producto'          => $p,
            ]);
        }

        foreach ($revision as $p) {
            $this->rows->push([
                'tipo'              => 'En revisión (31–60 días)',
                'producto'          => $p,
            ]);
        }
    }

    /**
     * Devuelve la colección de filas a exportar.
     */
    public function collection()
    {
        return $this->rows;
    }

    /**
     * Encabezados de las columnas.
     */
    public function headings(): array
    {
        return [
            'Tipo',
            'Producto',
            'Categoría',
            'Lote',
            'Fecha de vencimiento',
            'Días restantes',
            'Stock',
        ];
    }

    /**
     * Cómo se mapea cada fila.
     */
    public function map($row): array
    {
        $p    = $row['producto'];
        $tipo = $row['tipo'];

        return [
            $tipo,
            $p->nombre,
            optional($p->categoriaRelacion)->nombre ?? '-',
            $p->lote ?? '—',
            optional($p->fecha_vencimiento)->format('d/m/Y'),
            $p->dias_restantes, // negativo si está vencido
            $p->cantidad,
        ];
    }
}