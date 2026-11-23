<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class EpicaRequestCreate extends FormRequest
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
            'nombre' => 'required',
            'detalle' => 'required',
            'id_proyecto' => 'required|integer|exists:empresa_dinamica.proyectos,id',
            'id_usu_creo' => 'required|integer|exists:mysql.usuarios,id',
            'id_usu_asig'  => 'nullable|integer|exists:mysql.usuarios,id',
            'id_prioridad'  => 'required|integer',
            'archivo_pdf'   => 'nullable|file|mimes:pdf|max:10240',
            'archivo_video'   => 'nullable|file|mimes:mp4,mov,avi,wmv,webm,mkv,flv,3gp|max:102400',
        ];
        
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'id_usu_asig' => $this->id_usu_asig ?: null,
        ]);
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'Falta ingresar el nombre de la épica.',
            'detalle.required' => 'Falta describir el detalle de la épica.',
            'id_proyecto.required' => 'Falta señalar el proyecto al que se relaciona la épica.',
            'id_proyecto.exists' => 'El proyecto no se encuentra registrado en el sistema.',
            'id_usu_creo.exists' => 'El usuario no se encuentra registrado en el sistema.',
            'id_usu_creo.required' => 'Falta señalar el usuario quién crea la épica.',
            'id_usu_asig.exists' => 'El usuario a quién asigna la épica, no se encuentra registrado en el sistema.',
            'id_prioridad' => 'Falta indicar el tipo de prioridad que tiene la épica o requerimiento.'
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
