<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'    => 'required|string|max:255',
            'cantidad'  => 'required|integer|min:0',
            'precio'    => 'required|numeric|min:0',

            // Validación de categoría por nombre
            'categoria' => [
                'required',
                'string',
                'max:255',
                Rule::exists('categorias', 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'    => 'El nombre es obligatorio.',
            'cantidad.required'  => 'La cantidad es obligatoria.',
            'precio.required'    => 'El precio es obligatorio.',
            'categoria.required' => 'Debes seleccionar una categoría.',
            'categoria.exists'   => 'La categoría seleccionada no es válida.',
        ];
    }

    /**
     * Normaliza el precio antes de validar
     */
    protected function prepareForValidation()
    {
        if ($this->has('precio')) {
            $valor = (string) $this->input('precio');

            // quitar todo menos dígitos, puntos y comas
            $valor = preg_replace('/[^\d\.,]/', '', $valor);

            if (str_contains($valor, ',')) {
                // Formato tipo "1.234.567,89" → quitar puntos, usar coma como decimal
                $valor = str_replace('.', '', $valor);
                $valor = str_replace(',', '.', $valor);
            } else {
                // Solo puntos, pueden ser miles o decimal
                if (preg_match('/\.\d{1,2}$/', $valor)) {
                    // hay decimales → separar parte entera y decimal
                    $pos = strrpos($valor, '.');
                    $ent = substr($valor, 0, $pos);
                    $dec = substr($valor, $pos); // incluye el punto
                    $ent = str_replace('.', '', $ent);
                    $valor = $ent.$dec;
                } else {
                    // solo separadores de miles → quitarlos
                    $valor = str_replace('.', '', $valor);
                }
            }

            $this->merge([
                'precio' => $valor,
            ]);
        }
    }
}