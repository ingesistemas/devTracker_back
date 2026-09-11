<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GrupoTrabajoRequestUpdate extends FormRequest
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
            'id' => 'required|integer|exists:empresa_dinamica.grupos_trabajos,id',
            'grupo' => 'required',
            'detalle' => 'required',
            'id_jefe' => 'required|integer|exists:mysql.usuarios,id'
        ];
    }


    public function messages(): array
    {
        return [
            'id.required' => 'El ID del grupo de trabajo es obligatorio.',
            'id.integer' => 'El ID  del grupo de trabajo debe ser un número.',
            'id.exists' => 'El ID del grupo de trabajo no se encontró registrado en el sistema.',
            'grupo.required' => 'El nombre del grupo de trabajo es obligatorio.',
            'id_jefe.required' => 'Falta señalar el jefe del grupo de trabajo.',
            'id_jefe.integer' => 'El ID del jefe del grupo de trabajo debe ser un número.',
            'id_jefe.exists' => 'El ID del jefe del grupo de trabajo no se encontró registrado en el sistema.',
        ];
    }

    public function messagesEditar(): array
    {
        return [
            'id.required' => 'Falta señalar el grupo de trabajo que se desea editar.',
            'id.integer' => 'El ID del grupo de trabajo debe ser un número.',
            'id.exists' => 'El grupo de trabajo no se encontró registrado en el sistema.',
            'grupo.required' => 'El nombre del grupo de trabajo es obligatorio.',

            'id_usuario.required' => 'Falta señalar el usuario que crea el grupo de trabajo.',
            'id_usuario.integer' => 'El ID del usuario debe ser un número.',
            'id_usuario.exists' => 'El ID del usuario no se encontró registrado en el sistema.',

            'id_jefe.required' => 'Falta señalar el jefe del grupo de trabajo.',
            'id_jefe.integer' => 'El ID del jefe del grupo de trabajo debe ser un número.',
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
