<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\DB;

class StorePolizaRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para el formulario.
     */
    public function rules(): array
    {
        return [
            'compania_id' => 'required|exists:companias,id',
            'seguro_id'   => 'required|exists:seguros,id',
            'ramo_id'     => 'required|exists:ramos,id',
            'pdf'         => 'required|array|min:1',
            'pdf.*'       => 'file|mimes:pdf|max:2048',
        ];
    }

    /**
     * Validación personalizada.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $companiaId = $this->input('compania_id');
            $seguroId   = $this->input('seguro_id');
            $ramoId     = $this->input('ramo_id');

            // Verificar la relación entre seguro, compañía y ramo usando la tabla pivot
            $relacion = DB::table('compania_seguro')
                ->join('seguros', 'compania_seguro.seguro_id', '=', 'seguros.id')
                ->join('ramos', 'seguros.ramo_id', '=', 'ramos.id')
                ->where('compania_seguro.compania_id', $companiaId)
                ->where('seguros.id', $seguroId)
                ->where('ramos.id', $ramoId)
                ->exists();

            if (!$relacion) {
                $validator->errors()->add('seguro_id', 'El seguro no pertenece a la compañía seleccionada o el ramo no corresponde.');
            }
        });
    }
}
