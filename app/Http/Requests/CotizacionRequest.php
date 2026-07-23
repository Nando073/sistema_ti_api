<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CotizacionRequest extends FormRequest
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
            // Validamos que los IDs sean enteros y existan en sus tablas (Ajusta los nombres de tablas si son distintos)
            'id_orden'    => 'required|integer|exists:ordenes,id',
            'id_oferta'   => 'required|integer|exists:ofertas,id',
            'id_cliente'  => 'required|integer|exists:clientes,id',
            'id_usuario'  => 'required|integer|exists:usuarios,id',
            
            // Validamos que sea una fecha válida y con formato año-mes-día
            'fecha_cad'   => 'required|date|date_format:Y-m-d',
            
            // Monto total debe ser un número decimal/float mayor o igual a 0
            'monto_total' => 'required|numeric|min:0',
            
            // El descuento puede ser opcional (nullable), pero si se envía, debe ser un número
            'descuento'   => 'nullable|numeric|min:0',
            
            // El estado suele ser un texto corto (ej: 'pendiente', 'aceptado')
            'estado'      => 'required|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'required'    => 'El campo :attribute es obligatorio.',
            'integer'     => 'El campo :attribute debe ser un número entero.',
            'numeric'     => 'El campo :attribute debe ser un número válido.',
            'exists'      => 'El :attribute seleccionado no es válido en el sistema.',
            'date_format' => 'La fecha debe cumplir el formato Año-Mes-Día (YYYY-MM-DD).',
        ];
    }
}
