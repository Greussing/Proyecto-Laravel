<?php

namespace App\Http\Requests;

use App\Enums\MetodoPago;
use App\Enums\VentaEstado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreVentaRequest extends FormRequest
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
            'precio_unitario' => ['required'], // Se procesa en el servicio/controlador si viene con formato
            'metodo_pago'     => ['required', new Enum(MetodoPago::class)],
            'estado'          => ['required', new Enum(VentaEstado::class)],
            'fecha'           => ['required', 'date'],
        ];
    }

    public function prepareForValidation()
    {
        // Opcional: si quisieras limpiar el precio aquí, podrías.
        // Pero el controlador original lo hacía en el método store.
        // Lo dejaremos pasar y que el servicio lo limpie, o usar un mutator.
    }
}
