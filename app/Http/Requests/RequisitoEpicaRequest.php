<?php
namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class RequisitoEpicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Si la petición es PUT o PATCH (es una EDICIÓN)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'id' => 'required|integer|exists:empresa_dinamica.requisitos_epicas,id',
                'id_epica' => 'required|integer|exists:empresa_dinamica.epicas,id',
                'requisito' => [
                    'required',
                    Rule::unique('empresa_dinamica.requisitos_epicas', 'requisito')
                        ->ignore($this->id)
                        ->where('id_epica', $this->id_epica)
                ]
            ];
        }

        // Si es una CREACIÓN (POST)
        return [
            'id_epica' => 'required|integer|exists:empresa_dinamica.epicas,id',
            'requisito' => [
                'required',
                Rule::unique('empresa_dinamica.requisitos_epicas', 'requisito')
                    ->where('id_epica', $this->id_epica)
            ]
        ];
    }

    public function messages(): array
    {
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'id.required' => 'Falta señalar el requisito que se desea editar.',
                'id.integer' => 'El ID del requisito debe ser un número.',
                'id.exists' => 'El requisito no se encontró registrado en el sistema.',
                'id_epica.required' => 'Falta ingresar el ID de la épica.',
                'id_epica.exists' => 'El ID de la épica no se encontró registrado en el sistema.',
                'requisito.required' => 'El nombre del requisito es obligatorio.',
                'requisito.unique' => 'Este requisito ya se encuentra registrado para esta épica.',
            ];
        }

        return [
            'id_epica.required' => 'Falta ingresar el ID de la épica.',
            'id_epica.exists' => 'El ID de la épica no se encontró registrado en el sistema.',
            'requisito.required' => 'El nombre del requisito es obligatorio.',
            'requisito.unique' => 'Este requisito ya se encuentra registrado para esta épica.',
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