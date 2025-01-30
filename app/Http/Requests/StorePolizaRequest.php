<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\DB;

class StorePolizaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Define las reglas de validación para el formulario
     */
    public function rules(): array
    {
        return [
            'compania_id' => 'required|exists:companias,id',
            'seguro_id' => 'required|exists:seguros,id',
            'ramo_id' => 'required|exists:ramos,id',
            'pdf' => 'required|array|min:1',
            'pdf.*' => 'file|mimes:pdf|max:2048',
        ];
    }

    /**
     * Validación personalizada
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            // Recuperamos los valores enviados en la solicitud
            $companiaId = $this->compania_id;
            $seguroId = $this->seguro_id;
            $ramoId = $this->ramo_id;

            // Verificamos si el seguro pertenece a la compañía seleccionada
            $relacionExist = DB::table('seguros')
                ->where('id', $seguroId)
                ->where('compania_id', $companiaId)
                ->exists();

            if (!$relacionExist) {
                $validator->errors()->add('seguro_id', 'El seguro no pertenece a la compañía seleccionada.');
            }

            // Verificamos si el ramo pertenece al seguro seleccionado
            $ramoExiste = DB::table('ramos')
                ->where('id', $ramoId)
                ->where('id_seguros', $seguroId)
                ->exists();

            if (!$ramoExiste) {
                $validator->errors()->add('ramo_id', 'El ramo no pertenece al seguro seleccionado.');
            }
        });
    }
}
