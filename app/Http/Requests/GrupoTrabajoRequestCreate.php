<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class grupoTrabajoRequestCreate extends FormRequest
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
            'grupo' => 'required',
            'id_jefe' => 'required|integer|exists:mysql.usuarios,id',
            'id_usu_creo' => 'required|integer|exists:mysql.usuarios,id',
            'detalle' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'grupo.required' => 'El nombre del grupo es obligatorio.',
            'detalle.required' => 'El detalle del grupo es obligatorio.',
            'id_usu_creo.required' => 'Falta señalar el usuario que crea el grupo.',
            'id_usu_creo.integer' => 'El ID del usuario debe ser un número.',
            'id_usu_creo.exists' => 'El ID del usuario no se encontró registrado en el sistema.',
            'id_jefe.required' => 'Falta señalar el jefe del grupo de trabajo.',
            'id_jefe.integer' => 'El ID del jefe del grupos de trabajo debe ser un número.',
            'id_jefe.exists' => 'El ID del jefe del grupo de trabajo no se encontró registrado en el sistema.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'error' => true,
                'errores' => $validator->errors()
            ], 200)
        );
    }
}
