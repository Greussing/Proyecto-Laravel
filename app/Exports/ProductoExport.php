<?php

namespace App\Exports;

use App\Models\Producto;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductoExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $request = new \Illuminate\Http\Request($this->filters);

        // Mismo patrón que tu index de productos (scopes)
        $query = Producto::with('categoriaRelacion')
            ->buscar($request->input('search'))
            ->filtrarPorCategorias($request->input('categorias') ?? $request->input('categoria'))
            ->filtrarPorPrecio($request->input('precio_min'), $request->input('precio_max'))
            ->filtrarPorStock($request->input('stock'))
            ->ordenar($request->input('ordenar'));

        return $query->get()->map(function ($p) {
            return [
                'ID'        => $p->id,
                'Nombre'    => $p->nombre,
                'Categoría' => optional($p->categoriaRelacion)->nombre,
                'Cantidad'  => $p->cantidad,
                'Precio'    => $p->precio,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Categoría',
            'Cantidad',
            'Precio',
        ];
    }
}