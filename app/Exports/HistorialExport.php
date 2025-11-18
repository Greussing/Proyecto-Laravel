<?php

namespace App\Exports;

use App\Models\Historial;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HistorialExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    /**
     * Datos que se exportan
     */
    public function collection()
    {
        return Historial::with([
                'producto' => function ($q) {
                    // Para mostrar nombre aunque el producto esté eliminado
                    $q->withTrashed();
                },
                'usuario',
            ])
            ->whereIn('accion', ['crear', 'editar', 'eliminar']) // solo historial de productos
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Encabezados de las columnas
     */
    public function headings(): array
    {
        return [
            'ID',
            'Producto',
            'Usuario',
            'Acción',
            'Descripción',
            'Fecha',
        ];
    }

    /**
     * Cómo se mapea cada fila
     */
    public function map($h): array
    {
        return [
            $h->id,
            $h->producto->nombre ?? 'N/A',
            $h->usuario->name ?? 'Admin',
            ucfirst($h->accion),
            $h->descripcion ?? '',
            $h->created_at ? $h->created_at->format('d/m/Y H:i') : '',
        ];
    }

    /**
     * Estilos (negrita en la fila de encabezados)
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}