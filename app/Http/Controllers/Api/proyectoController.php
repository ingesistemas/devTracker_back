<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\loginRequest;
use App\Http\Requests\proyectoRequest;
use App\Models\empresaModel;
use App\Models\proyectoModel;
use App\Models\rolModel;
use App\Models\usuarioModel;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class proyectoController extends Controller
{
    protected $mensaje = "";
    protected $error = false;
    protected $data = [];
    protected $status = "200";
    public function consultar(Request $request)
    {
        try {
           
            $proyectos = proyectoModel::with(['usuario:id,email,nombre,id_rol','usuario.rol:id,rol'])
            ->select('id', 'proyecto', 'activo', 'id_usuario')->get();

            if ($proyectos->isNotEmpty()) {
                return response()->json([
                    'error' => false,
                    'mensaje' => 'Proyectos encontrados.',
                    'datos' => $proyectos
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

    public function crear(proyectoRequest $request)
    {
        try{
            $proyecto = $request->proyecto;
            $id_usuario = $request->id_usuario;

            $busqueda = proyectoModel::with(['usuario:id,email,id_rol','usuario.rol:id,rol'])
            ->select('id', 'proyecto', 'activo')
            ->where('proyecto', $proyecto)->first();

            if ($busqueda) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Este proyecto ya se encuentra registrado en el sistema.',
                    'datos' => []
                ], 200);
            }

            $nuevoProyecto = proyectoModel::create([
                'proyecto' => $proyecto,
                'id_usuario' => $id_usuario,
                'activo' => 0
            ]);
            return response()->json([
                'error' => false,
                'mensaje' => 'El registro fue creado correctamente.',
                'data' => $nuevoProyecto
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
