<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
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
            'seguro_id' => 'required|exists:seguros,id',
            'ramo_id' => 'required|exists:ramos,id',
            'pdf' => 'required|array|min:1',
            'pdf.*' => 'file|mimes:pdf|max:2048',
        ];
    }

    /**
     * Validación personalizada.
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $companiaId = $this->compania_id;
            $seguroId = $this->seguro_id;
            $ramoId = $this->ramo_id;

            // Validamos que los IDs sean válidos antes de hacer relaciones
            if (!$this->verificarExistencia('seguros', $seguroId) || !$this->verificarExistencia('ramos', $ramoId)) {
                return;
            }

            // 🔹 Hacemos una sola consulta para validar relaciones
            $relaciones = DB::table('seguros')
                ->leftJoin('ramos', 'seguros.id', '=', 'ramos.id_seguros')
                ->where('seguros.id', $seguroId)
                ->where('seguros.compania_id', $companiaId)
                ->where('ramos.id', $ramoId)
                ->first();

            if (!$relaciones) {
                $validator->errors()->add('seguro_id', 'El seguro no pertenece a la compañía seleccionada o el ramo no corresponde.');
            }
        });
    }

    /**
     * Método auxiliar para verificar si un ID existe en una tabla.
     */
    private function verificarExistencia(string $tabla, int $id): bool
    {
        return DB::table($tabla)->where('id', $id)->exists();
    }
}
