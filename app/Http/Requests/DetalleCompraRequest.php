<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DetalleCompraRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //

            'precio'      => 'required|numeric|min:0',
            'sub_total'   => 'required|numeric|min:0',
            'cantidad'    => 'required|integer|min:1',
            'id_compra'   => 'required|integer|exists:compras,id_compra',
            'id_repuesto' => 'required|integer|exists:repuestos,id_repuesto',
            'estado'      => 'nullable|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'numeric'  => 'El campo :attribute debe ser un número válido.',
            'integer'  => 'El campo :attribute debe ser un número entero.',
            'min'      => 'El campo :attribute debe ser mayor o igual a :min.',
            'exists'   => 'El :attribute seleccionado no existe en la base de datos.',
        ];
    }
}
