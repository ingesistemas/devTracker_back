<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CredencialesUpdateRequest extends FormRequest
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
            'contrasena' => 'required',
            'confirmar' => 'required|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'contrasena.required' => 'Falta ingresar la contraseña.',
            'confirmar.required' => 'Falta ingresar la nueva contraseña.',
            'confirmar.min' => 'La contraseña debe tener al menos 6 caracteres.',
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
