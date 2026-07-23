<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class OfertaRequest extends FormRequest
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
            'of_nombre'   => 'sometimes|required|string|max:100',
            'descripcion' => 'nullable|string',
            'porcentaje'  => 'sometimes|required|numeric|between:0,100',
            'fecha_inc'   => 'sometimes|required|date|date_format:Y-m-d',
            // Si viene la fecha_fin, se asegura de que sea igual o posterior a fecha_inc
            'fecha_fin'   => 'sometimes|required|date|date_format:Y-m-d|after_or_equal:fecha_inc',
            'estado'      => 'nullable|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'required'               => 'El campo :attribute no puede estar vacío.',
            'date_format'            => 'El formato de fecha debe ser YYYY-MM-DD.',
            'fecha_fin.after_or_equal' => 'La fecha de fin no puede ser menor a la fecha de inicio.',
            'porcentaje.between'     => 'El porcentaje debe estar entre 0 y 100.',
        ];
    }
}
