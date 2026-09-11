<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UsuarioUpdateRequest extends FormRequest
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
            'id' => 'required',
            'email' => 'required|email',
            'nombre' => 'required',
            'id_rol' => 'required|integer|exists:mysql.roles,id',
            'id_grupo_trabajo' => 'required|integer|exists:empresa_dinamica.grupos_trabajos,id',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'Falta señalar el grupo de trabajo que desea editar.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no tiene un formato válido.',
            'id_rol.required' => 'El rol es obligatorio.',
            'id_rol.exists' => 'El rol seleccionado no existe.',
            'nombre.required' => 'Falta ingresar el nombre del usuario.',
            'id_grupo_trabajo.required' => 'Falta señalar a que grupo de trabajo pertenece el usuario.',
            'id_grupo_trabajo.exists' => 'El grupo de trabajo no se encuentra registrado en el sistema.',
            'id_grupo_trabajo.integer' => 'El ID del grupo de trabajo debe ser numérico.'
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
