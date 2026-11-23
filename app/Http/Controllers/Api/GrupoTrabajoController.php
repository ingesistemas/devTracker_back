<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GrupoTrabajoRequestCreate;
use App\Http\Requests\GrupoTrabajoRequestUpdate;
/* use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProyectoRequest;
use App\Models\empresaModel;
 */use App\Models\GrupoTrabajoModel;
/* use App\Models\proyectoModel;
use App\Models\rolModel;
use App\Models\usuarioModel; */
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class GrupoTrabajoController extends Controller
{
    protected $mensaje = "";
    protected $error = false;
    protected $data = [];
    protected $status = "200";
    public function consultar(Request $request)
    {
        try {
           
           $grupos = grupoTrabajoModel::with([
                'jefe:id,nombre,email',           // Carga los datos del jefe
                'creador:id,nombre,email,id_rol',        // Carga los datos del usuario que creó el grupo
                'creador.rol:id,rol'              // Si el creador tiene una relación 'rol' en su modelo
            ])
            ->select('id', 'grupo', 'detalle', 'activo', 'id_jefe', 'id_usu_creo')->get();

            if ($grupos->isNotEmpty()) {
                return response()->json([
                    'error' => false,
                    'mensaje' => 'Grupos de trabajo encontrados.',
                    'datos' => $grupos
                ], 200);
            }

            return response()->json([
                'error' => true,
                'mensaje' => 'No se encontraron datos.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function crear(grupoTrabajoRequestCreate $request)
    {
        try{
            $grupo = $request->grupo;
            $id_usuario = $request->id_usu_creo;
            $id_jefe = $request->id_jefe;
            $detalle = $request->detalle;

            $busqueda = grupoTrabajoModel::select('id', 'grupo', 'activo')
            ->where('grupo', $grupo)->first();

            if ($busqueda) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Este grupo de trabajo ya se encuentra registrado en el sistema.',
                    'datos' => []
                ], 200);
            }

            $nuevoGrupo = grupoTrabajoModel::create([
                'grupo' => $grupo,
                'detalle' => $detalle,
                'id_jefe' => $id_jefe,
                'id_usu_creo' => $id_usuario,
                'activo' => 0
            ]);
            return response()->json([
                'error' => false,
                'mensaje' => 'El registro fue creado correctamente.',
                'data' => $nuevoGrupo
            ]);
        }catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function editar(grupoTrabajoRequestUpdate $request)
    {
        try{
           
            $id = $request->id;
            $grupo = grupoTrabajoModel::find($request->id);
            if (!$grupo) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Este grupo no se encuentra registrado en el sistema.',
                    'datos' => []
                ], 200);
            }

            $grupo->update($request->validated());

            return response()->json([
                'error' => false,
                'mensaje' => 'El registro fue editado correctamente.',
                'data' => $grupo
            ]);
        }catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function estado(Request $request)
    {
        try{
            $id = $request->id;
            $grupo = grupoTrabajoModel::find($id);

            if (!$grupo) {
                return response()->json([
                    'error' => false,
                    'mensaje' => 'No se encontró el proyecto registrado en el sistema.'
                ], 200);
            }

            $grupo->activo = !$grupo->activo;
            $grupo->save();

            return response()->json([
                'error' => false,
                'mensaje' => 'proyecto actualizado correctamente.',
                'datos' => $grupo
            ], 200);        
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }
}
