<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProyectoRequest extends FormRequest
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
            'proyecto' => 'required',
            'id_usuario' => 'required|integer|exists:mysql.usuarios,id',
            
        ];
    }

    public function rulesEditar(): array
    {
        return [
            'id' => 'required|integer|exists:mysql.roles,id',
            'proyecto' => 'required',
            'id_usuario' => 'required|integer|exists:mysql.usuarios,id',
        ];

    }

    public function messages(): array
    {
        return [
            'proyecto.required' => 'El nombre del proyecto es obligatorio.',
            'id_usuario.required' => 'Falta señalar el usuario que crea el proyecto.',
            'id_usuario.integer' => 'El ID del usuario debe ser un número.',
            'id_usuario.exists' => 'El ID del usuario no se encontró registrado en el sistema.',
        ];
    }

    public function messagesEditar(): array
    {
        return [
            'id.required' => 'Falta señalar el proyecto que se desea editar.',
            'id.integer' => 'El ID del proyecto debe ser un número.',
            'id.exists' => 'El proyecto no se encontró registrado en el sistema.',

            'proyecto.required' => 'El nombre del proyecto es obligatorio.',

            'id_usuario.required' => 'Falta señalar el usuario que crea el proyecto.',
            'id_usuario.integer' => 'El ID del usuario debe ser un número.',
            'id_usuario.exists' => 'El ID del usuario no se encontró registrado en el sistema.',
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
