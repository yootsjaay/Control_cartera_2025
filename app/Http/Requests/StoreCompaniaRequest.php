<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompaniaRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'clase' => 'required|string|max:255',
        ];
    }
    public function messages()
    {
        return [
            'nombre.required' => 'El nombre de la compañía es obligatorio.',
            'clase.required' => 'Debe seleccionar una clase de servicio.',
        ];
    }
}
