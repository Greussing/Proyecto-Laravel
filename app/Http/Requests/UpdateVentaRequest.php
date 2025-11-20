<?php

namespace App\Http\Requests;

use App\Enums\MetodoPago;
use App\Enums\VentaEstado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente'         => ['required', 'exists:clientes,id'],
            'producto'        => ['required', 'exists:productos,id'],
            'cantidad'        => ['required', 'integer', 'min:1'],
            'precio_unitario' => ['required'],
            'metodo_pago'     => ['required', new Enum(MetodoPago::class)],
            'estado'          => ['required', new Enum(VentaEstado::class)],
            'fecha'           => ['required', 'date'],
        ];
    }
}
