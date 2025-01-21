<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTipoSeguroRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize()
    {
        return true; // Cambia esto según las políticas de autorización
    }

    /**
     * Reglas de validación para almacenar un seguro.
     */
    public function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'cobertura_minima' => 'nullable|numeric|gte:0',
            'cobertura_maxima' => 'nullable|numeric|gte:cobertura_minima',
            'duracion' => 'nullable|integer|min:1',
            'prima_promedio' => 'nullable|numeric|gte:0',
            'riesgo_asociado' => 'nullable|in:bajo,medio,alto',
            'requisitos' => 'nullable|string',
            'subtipos' => 'nullable|array', // Subtipos es opcional y debe ser un array
            'subtipos.*' => 'string|max:255', // Cada subtipo debe ser un string
        ];
    }

    /**
     * Mensajes de error personalizados (opcional).
     */
    public function messages()
    {
        return [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'cobertura_maxima.gte' => 'La cobertura máxima debe ser mayor o igual a la cobertura mínima.',
        ];
    }
}
