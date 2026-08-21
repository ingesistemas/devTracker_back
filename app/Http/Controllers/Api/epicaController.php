<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EpicaRequestCreate;
use App\Http\Requests\loginRequest;
use App\Http\Requests\proyectoRequest;
use App\Models\empresaModel;
use App\Models\EpicaModel;
use App\Models\proyectoModel;
use App\Models\rolModel;
use App\Models\usuarioModel;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class epicaController extends Controller
{
    protected $mensaje = "";
    protected $error = false;
    protected $data = [];
    protected $status = "200";

    // Indica a Eloquent que agregue el atributo "nom_prioridad" al convertir a Array / JSON
    protected $appends = ['nom_prioridad'];

    
    /**
     * Mapea el ID de prioridad a su nombre legible
     */
    public function getNomPrioridadAttribute(): string
    {
        $prioridades = [
            1 => 'Crítica',
            2 => 'Alta',
            3 => 'Media',
            4 => 'Baja'
        ];

        // Devuelve el nombre según el ID, o 'Media' por defecto si viene nulo
        return $prioridades[$this->attributes['id_prioridad'] ?? 3] ?? 'Media';
    }
    public function consultar(Request $request)
    {
        try {
           
            $epicas = EpicaModel::select(
                'id', 
                'nombre', 
                'detalle', 
                'f_pentrega', 
                'fec_fin', 
                'f_rentrega', 
                'id_proyecto', 
                'id_estado', 
                'id_usu_creo', 
                'id_usu_asig',
                'id_prioridad',
                'created_at'
            )
            ->with([
                'proyecto:id,proyecto',
                'estado:id,estado',
                'usuarioCreo:id,nombre,email,id_rol',
                'usuarioCreo.rol:id,rol',
                'usuarioAsignado:id,nombre,email,id_rol',
                'usuarioAsignado.rol:id,rol'
            ])->get();

            if ($epicas->isNotEmpty()) {
                return response()->json([
                    'error' => false,
                    'mensaje' => 'Épicas encontradas.',
                    'datos' => $epicas
                ], 200);
            }

            return response()->json([
                'error' => true,
                'mensaje' => 'No se encontraron datos.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e,
                'data' => []
            ]);
        }
    }

    public function crear(EpicaRequestCreate $request)
    {
        try{

            $busqueda = EpicaModel::select('id')
            ->where('id_proyecto', $request->id_proyecto)
            ->where('nombre', $request->nombre)->first();

            if ($busqueda) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'La épica ya se encuentra registra en ese proyecto.',
                    'datos' => []
                ], 200);
            }

            $nuevaEpica = EpicaModel::create([
                'detalle' => $request->detalle,
                'nombre' => $request->nombre,
                'id_proyecto'=> $request->id_proyecto,
                'id_estado'=> '1',
                'id_usu_creo' => $request->id_usu_creo,
                'id_usu_asig' => $request->id_usu_asig,
                'activo' => 0
            ]);
            return response()->json([
                'error' => false,
                'mensaje' => 'El registro fue creado correctamente.',
                'data' => $nuevaEpica
            ]);
        }catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e,
                'data' => []
            ]);
        }
    }

    public function editar(proyectoRequest $request)
    {
        $validator = Validator::make(
        $request->all(),
        $request->rulesEditar(),
        $request->messagesEditar()
    );

    if ($validator->fails()) {
        return response()->json([
            'error' => true,
            'errores' => $validator->errors()
        ], 200);
    }
        try{
           

            $id = $request->id;

            $proyecto = proyectoModel::with(['usuario:id,email,id_rol','usuario.rol:id,rol'])
            ->select('id', 'proyecto', 'activo')
            ->where('id', $id)->first();


            if (!$proyecto) {
                return response()->json([
                    'error' => false,
                    'mensaje' => 'Este proyecto no se encuentra registrado en el sistema.',
                    'datos' => []
                ], 200);
            }

            $proyecto->update($request->validated());

            return response()->json([
                'error' => false,
                'mensaje' => 'El registro fue editado correctamente.',
                'data' => $proyecto
            ]);
        }catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e,
                'data' => []
            ]);
        }
    }

    public function estado(Request $request)
    {
        try{
            $id = $request->id;
            $proyecto = proyectoModel::find($id);

            if (!$proyecto) {
                return response()->json([
                    'error' => false,
                    'mensaje' => 'No se encontró el proyecto registrado en el sistema.'
                ], 200);
            }

            $proyecto->activo = !$proyecto->activo;
            $proyecto->save();

            return response()->json([
                'error' => false,
                'mensaje' => 'proyecto actualizado correctamente.',
                'datos' => $proyecto
            ], 200);        
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e,
                'data' => []
            ]);
        }
    }
}
