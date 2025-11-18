<?php

namespace App\Exports;

use App\Models\MovimientoStock;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MovimientoStockExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $request = new \Illuminate\Http\Request($this->filters);

        $query = MovimientoStock::with('producto');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tipo', 'like', "%$search%")
                  ->orWhere('detalle', 'like', "%$search%")
                  ->orWhereHas('producto', fn($p) =>
                      $p->where('nombre', 'like', "%$search%")
                  );
            });
        }

        if ($request->filled('tipo')) {
            $tipos = (array) $request->tipo;
            $query->whereIn('tipo', $tipos);
        }

        if ($request->filled('ordenar')) {
            $orden = $request->ordenar;
            $campo = 'created_at';
            $dir   = 'desc';

            switch ($orden) {
                case 'fecha_asc':      $campo = 'created_at'; $dir = 'asc'; break;
                case 'fecha_desc':     $campo = 'created_at'; $dir = 'desc'; break;
                case 'cantidad_asc':   $campo = 'cantidad';   $dir = 'asc'; break;
                case 'cantidad_desc':  $campo = 'cantidad';   $dir = 'desc'; break;
            }

            $query->orderBy($campo, $dir);
        } else {
            $query->latest();
        }

        return $query->get()->map(function ($m) {
            return [
                'ID'            => $m->id,
                'Producto'      => optional($m->producto)->nombre,
                'Tipo'          => $m->tipo,
                'Cantidad'      => $m->cantidad,
                'Stock Antes'   => $m->stock_antes,
                'Stock Después' => $m->stock_despues,
                'Detalle'       => $m->detalle,
                'Fecha'         => optional($m->created_at)->format('d/m/Y H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Producto',
            'Tipo',
            'Cantidad',
            'Stock Antes',
            'Stock Después',
            'Detalle',
            'Fecha',
        ];
    }
}