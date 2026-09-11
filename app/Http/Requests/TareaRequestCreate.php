<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TareaRequestCreate extends FormRequest
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
            'id_epica' => 'required',
            'detalle' => 'required',
            'f_pentrega' => 'required',
            'id_estado' => 'required',
            'id_usu_creo' => 'required',
            'id_reasig' => 'required',
            'id_complejidad' => 'required',
            'id_prioridad'  => 'required',
            //'id_empresa' => 'required|integer|exists:mysql.roles,id'
        ];
    }

    public function messages(): array
    {
        return [
            'id_epica.required' => 'Falta señalar la épica a la cual pertenece la tarea.',
            'detalle.required' => 'Falta ingresar el detalle de la tarea.',
            'f_pentrega.required' => 'La ingresar la fecha estimada de la entrega.',
            'id_estado.required' => 'Falta señalar el estado de la tarea.',
            'id_usu_creo.required' => 'El señalar el usuario quién crea la tarea.',
            'id_reasig.required' => 'El señalar el usuario a quién se le asigna la tarea.',
            'id_complejidad.required' => 'Falta señalar el grado de complejidad de la tarea.',
            'id_prioridad.required' => 'Falta señalar el grado de prioridad de la tarea.',
            //'id_empresa.exists' => 'El ID de la empresa relacionada no existe.'
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
