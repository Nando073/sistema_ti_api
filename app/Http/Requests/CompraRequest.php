<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompraRequest extends FormRequest
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
        $compraId = $this->route('compra')?->id_compra;

        return [
            'n_documento' => [
                'required',
                'string',
                'max:50',
                Rule::unique('compras', 'n_documento')->ignore($compraId, 'id_compra'),
            ],
            'id_proveedor' => 'required|integer|exists:proveedores,id_proveedor',
            'id_usuario'   => 'required|integer|exists:usuarios,id_usuario',
            'total_compra' => 'required|numeric|min:0',
            'forma_pago'   => 'required|string|max:50',
            'observacion'  => 'nullable|string',
            'fecha'        => 'required|date|date_format:Y-m-d',
            'estado'       => 'nullable|boolean',

            'detalles' => 'required|array|min:1',
            'detalles.*.id_repuesto'       => 'required|integer|exists:repuestos,id_repuesto',
            'detalles.*.cantidad'          => 'required|integer|min:1',
            'detalles.*.precio'            => 'required|numeric|min:0',
            'detalles.*.sub_total'         => 'required|numeric|min:0',
            'detalles.*.estado'            => 'sometimes|boolean',
            'detalles.*.id_detalle_compra' => 'sometimes|integer|exists:detalle_compras,id_detalle_compra',
        ];
    }

    public function messages(): array
    {
        return [
            'required'                       => 'El campo :attribute es obligatorio.',
            'n_documento.unique'             => 'El número de documento ya está registrado en otra compra.',
            'exists'                         => 'El :attribute ingresado no existe en la base de datos.',
            'total_compra.numeric'           => 'El total de la compra debe ser un valor numérico.',
            'date_format'                    => 'La fecha debe tener el formato YYYY-MM-DD.',
            'detalles.required'              => 'La compra debe contener al menos un detalle.',
            'detalles.array'                 => 'Los detalles deben ser un arreglo.',
            'detalles.*.id_repuesto.required'=> 'El repuesto es obligatorio en cada detalle.',
            'detalles.*.cantidad.min'        => 'La cantidad debe ser al menos 1 en cada detalle.',
            'detalles.*.precio.min'          => 'El precio debe ser un valor válido en cada detalle.',
            'detalles.*.sub_total.min'       => 'El sub total debe ser un valor válido en cada detalle.',
        ];
    }
}
