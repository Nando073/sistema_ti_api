<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DetalleCotizacionRequest extends FormRequest
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
            'id_cotizacion' => 'required|integer|exists:cotizaciones,id_cotizacion',
            'id_equipo'     => 'required|integer|exists:equipos,id_equipo',
            'id_repuesto'   => 'nullable|integer|exists:repuestos,id_repuesto',
            'precio'        => 'required|numeric|min:0',
            'cantidad'      => 'required|integer|min:1',
            'descuento'     => 'nullable|numeric|min:0',
            'estado'        => 'nullable|boolean'
        ];
    }


    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'numeric'  => 'El campo :attribute debe ser un número válido.',
            'integer'  => 'El campo :attribute debe ser un número entero.',
            'min'      => 'El campo :attribute debe ser al menos :min.',
            'exists'   => 'El :attribute seleccionado no existe en la base de datos.',
        ];
    }
}
