<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductoRequest extends FormRequest
{
    // Autoriza que cualquier usuario autenticado pueda usar este request
    public function authorize(): bool
    {
        return true;
    }
// RULES → Definir reglas de validación
    // Conecta con: ProductoController (al guardar/editar productos)
    public function rules(): array
    {
        return [
            // Nombre obligatorio, texto, máximo 255 caracteres
            'nombre'    => 'required|string|max:255',
            // Cantidad obligatoria, número entero, no negativo
            'cantidad'  => 'required|integer|min:0',
            // Precio obligatorio, número, no negativo
            'precio'    => 'required|numeric|min:0',
            // Categoría obligatoria, debe existir en la tabla "categorias"
            'categoria' => [
                'required',
                'integer',
                Rule::exists('categorias', 'id'),
            ],
        ];
    }
            // MESSAGES → Mensajes personalizados de error
            // Se muestran cuando la validación falla
    public function messages(): array
    {
        return [
            'nombre.required'    => 'El nombre es obligatorio.',
            'cantidad.required'  => 'La cantidad es obligatoria.',
            'precio.required'    => 'El precio es obligatorio.',
            'precio.numeric'     => 'El precio debe ser un número válido.',
            'categoria.required' => 'Debes seleccionar una categoría.',
            'categoria.exists'   => 'La categoría seleccionada no es válida.',
        ];
    }

    /**
     * Normalizar datos antes de validar
     */
    protected function prepareForValidation()
    {
        $this->merge([
            // Quitar separadores de miles en precio antes de validar
            'precio' => $this->precio
                ? (float) str_replace(['.', ','], '', $this->precio)
                : null,
        ]);
    }
}