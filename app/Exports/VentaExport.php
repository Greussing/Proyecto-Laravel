<?php

namespace App\Exports;

use App\Models\Venta;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class VentaExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $request = new \Illuminate\Http\Request($this->filters);

        $query = Venta::with(['usuarioRelacion', 'clienteRelacion', 'detalles'])
            ->withCount(['detalles as cantidad_productos' => function ($q) {
                $q->select(\DB::raw('coalesce(sum(cantidad),0)'));
            }])
            ->filtrarPorFecha($request->input('fecha_desde'), $request->input('fecha_hasta'))
            ->filtrarPorMetodo($request->input('metodo_pago'))
            ->filtrarPorEstado($request->input('estado'))
            ->filtrarPorTotal($request->input('total_min'), $request->input('total_max'))
            ->ordenar($request->input('ordenar'));

        return $query->get()->map(function ($v) {
            return [
                'ID'        => $v->id,
                'Fecha'     => optional($v->fecha)->format('d/m/Y'),
                'Cliente'   => optional($v->clienteRelacion)->nombre,
                'Total'     => $v->total,
                'Método'    => $v->metodo_pago,
                'Estado'    => $v->estado,
                'Usuario'   => optional($v->usuarioRelacion)->name,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha',
            'Cliente',
            'Total',
            'Método',
            'Estado',
            'Usuario',
        ];
    }
}