<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        // Para update
        $id = $this->route('proveedore');

        return [

            'nombre' => [
                'required',
                'string',
                'max:255'
            ],

            'nit' => [
                'required',
                'string',
                'max:255',
                'unique:proveedores,nit,' . $id . ',id_proveedor'
            ],

            'departamento' => [
                'required',
                'string',
                'max:255'
            ],

            'direccion' => [
                'required',
                'string',
                'max:255'
            ],

            'correo' => [
                'required',
                'email',
                'max:255',
                'unique:proveedores,correo,' . $id . ',id_proveedor'
            ],

            'celular' => [
                'required',
                'string',
                'max:255'
            ],

            'estado' => [
                'required',
                'integer',
                'in:0,1'
            ],

        ];
    }


    public function messages(): array
    {
        return [

            'nombre.required' => 'El nombre del proveedor es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',


            'nit.required' => 'El NIT es obligatorio.',
            'nit.unique' => 'El NIT ya se encuentra registrado.',


            'departamento.required' => 'El departamento es obligatorio.',


            'direccion.required' => 'La dirección es obligatoria.',


            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo debe tener un formato válido.',
            'correo.unique' => 'El correo ya se encuentra registrado.',


            'celular.required' => 'El celular es obligatorio.',


            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado solo puede ser 0 (inactivo) o 1 (activo).',

        ];
    }
}