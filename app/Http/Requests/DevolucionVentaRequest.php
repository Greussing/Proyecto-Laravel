<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DevolucionVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Necesitamos acceder a la venta para validar max cantidad
        // Asumimos que la ruta es /ventas/{venta}/devolucion
        $venta = $this->route('venta');
        $detalle = $venta ? $venta->detalles->first() : null;
        $maxCantidad = $detalle ? $detalle->cantidad : 0;

        return [
            'cantidad_devolver' => [
                'required',
                'integer',
                'min:1',
                'max:' . $maxCantidad,
            ],
            'detalle' => ['nullable', 'string'],
        ];
    }
}
