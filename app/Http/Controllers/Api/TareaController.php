<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
/* use App\Http\Requests\EpicaRequestCreate;
use App\Http\Requests\EpicaRequestUpdate;
use App\Http\Requests\loginRequest;
use App\Http\Requests\proyectoRequest; */
use App\Http\Requests\TareaRequestCreate;
use App\Http\Requests\TareaRequestUpdate;
/* use App\Models\empresaModel; */
use App\Models\EpicaModel;
use App\Models\ProyectoModel;
/* use App\Models\rolModel; */
use App\Models\TareaModel;
use App\Models\UsuarioModel;
use Carbon\Carbon;
/* use App\Models\usuarioModel; */
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class TareaController extends Controller
{
    protected $mensaje = "";
    protected $error = false;
    protected $data = [];
    protected $status = "200";

    // Indica a Eloquent que agregue el atributo "nom_prioridad" al convertir a Array / JSON
    protected $appends = ['nom_prioridad', 'nom_complejidad'];

    
    /**
     * Mapea el ID de prioridad a su nombre legible
     */
    public function getNomPrioridadAttribute(): string
    {
        $prioridades = [
            4 => 'Crítica',
            3 => 'Alta',
            2 => 'Media',
            1 => 'Baja'
        ];
        // Devuelve el nombre según el ID, o 'Media' por defecto si viene nulo
        return $prioridades[$this->attributes['id_prioridad'] ?? 2] ?? 'Media';
    }

    public function getNomComplejidadAttribute(): string
    {
        $complejidad = [
            4 => 'Compleja',
            3 => 'Alta',
            2 => 'Media',
            1 => 'Baja'
        ];
        return $complejidad[$this->attributes['id_complejidad'] ?? 2] ?? 'Media';
    }

    public function consultar(Request $request, $id = null, $id_epica = null)
    {
        try {
          
            $tareas = TareaModel::select(
                'id', 
                'detalle', 
                'f_pentrega', 
                'f_rentrega',
                'fec_ini',  
                'fec_fin',             
                'id_epica', 
                'id_estado', 
                'id_usu_creo', 
                'id_reasig',
                'id_prioridad',
                'id_complejidad',
                'activo',
                'cronometro',
                'tiempo',
                'created_at'
            )
            ->with([
                'epica:id,nombre,id_proyecto',
                'epica.proyecto:id,proyecto',
                'estado:id,estado',
                'usuarioCreo:id,nombre,email,id_rol',
                'usuarioCreo.rol:id,rol',
                'usuarioAsignado:id,nombre,email,id_rol',
                'usuarioAsignado.rol:id,rol'
            ])
            ->where('id_epica', $id_epica) // Filtra primero por la épica seleccionada
            ->where(function ($query) use ($id) {
                $query->where('id_usu_creo', $id)  // Tareas que creaste
                    ->orWhere('id_reasig', $id);  // O tareas reasignadas a ti
            })
            ->get();
            

            if ($tareas->isNotEmpty()) {
                return response()->json([
                    'error' => false,
                    'mensaje' => 'Épicas encontradas.',
                    'datos' => $tareas
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

    public function crear(TareaRequestCreate $request)
    {
        try{
        
            $busqueda = TareaModel::select('id','id_epica')
            ->where('id_epica', $request->id_epica)
            ->where('detalle', $request->detalle)->first();

            if ($busqueda) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'La tarea ya se encuentra registrada en ese proyecto.',
                    'datos' => []
                ], 200);
            }            
           
            $nuevaTarea = TareaModel::create([
                'detalle' => $request->detalle,
                'id_epica' => $request->id_epica,
                'f_pentrega'=> $request->f_pentrega,
                'id_estado'=> '1',
                'id_complejidad' => $request->id_complejidad,
                'id_prioridad' => $request->id_prioridad,
                'id_usu_creo' => $request->id_usu_creo,
                'id_reasig' => $request->id_reasig,
                'activo' => 0,
                'cronometro' => 0
            ]);
            return response()->json([
                'error' => false,
                'mensaje' => 'El registro fue creado correctamente.',
                'data' => $nuevaTarea
            ]);
        }catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function editarHistoria(Request $request)
    {
        try{
            $id = $request->id;
            $epica = EpicaModel::select('id')
            ->where('id', $request->id)->first();

            if (!$epica) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'El ID de la épica no se encuentra registrado en el sistema.',
                    'datos' => []
                ], 200);
            }

            $epica->historia = $request->historia;
            $epica->save();

            return response()->json([
                'error' => false,
                'mensaje' => 'El registro fue editado correctamente.',
                'data' => $epica
            ]);
        }catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function editar(TareaRequestUpdate $request)
    {
        try{

            $id = $request->id;

            $epica = TareaModel::select('id')
            ->where('id', $request->id)->first();

            if (!$epica) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'El ID de la tarea no se encuentra registrado en el sistema.',
                    'datos' => []
                ], 200);
            }

            $epica->update($request->validated());

            return response()->json([
                'error' => false,
                'mensaje' => 'El registro fue editado correctamente.',
                'data' => $epica
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
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function cronometro(Request $request)
    {
        try {
            $id = $request->id;
            $idUsuario = $request->id_usu_creo;
            $cronometro = $request->cronometro;
            $id_epica = $request->input('epica.id');

            // Ejecutamos todo dentro de una transacción atómica
            $tarea = DB::transaction(function () use ($id, $idUsuario, $cronometro, $id_epica) {

                $tareaAnterior = TareaModel::select('id', 'fec_inicio', 'fec_ini', 'fec_fin', 'cronometro', 'tiempo')
                ->where('id_reasig', $idUsuario)
                ->where('cronometro', 1)
                ->first(); 
        
                // 1. Desactivar todos los cronómetros de este usuario
                TareaModel::where('id_reasig', $idUsuario)
                    ->update(['cronometro' => 0]);

                // 2. Buscar la tarea objetivo
                $tareaEncontrada = TareaModel::select('id', 'cronometro', 'tiempo', 'fec_ini', 'fec_fin')
                    ->where('id', $id)
                    ->where('id_reasig', $idUsuario)
                    ->first();

                // Si no existe la tarea, lanzamos una excepción para forzar el rollback
                if (!$tareaEncontrada) {
                    throw new Exception('No se encontró la tarea registrada en el sistema o no tienes permisos para esta acción.');
                }

                
                $tareaEncontrada->fec_ini = now();
                
                $fecFin = now();   
                $fecIni = now();
                $tareaEncontrada->fec_ini = $fecIni;
                $tareaEncontrada->cronometro = $cronometro;
                if($tareaEncontrada->fec_inicio == null){
                        $tareaEncontrada->fec_inicio = $fecIni;
                    }
                $tareaEncontrada->save();
                
                if ($tareaAnterior) {
                    $fecIni = Carbon::parse($tareaAnterior->fec_ini);
                    $tareaAnterior->fec_fin = $fecFin;
                    $tareaAnterior->cronometro = 0;
                    $minutosActuales = $fecIni->diffInMinutes($fecFin);
                    $tareaAnterior->tiempo = (($tareaAnterior->tiempo ?? 0) + $minutosActuales);
                    
                    $tareaAnterior->save();
                }

                $usuario = UsuarioModel::find($idUsuario);
                if ($usuario) {
                    if ($cronometro == 1) {
                        // Si activa el cronómetro, asignamos la épica y tarea actuales
                        $usuario->id_epica = $id_epica;
                        $usuario->id_tarea = $id; // Corrección: antes tenías $usuario->id_epica = $id
                    } else {
                        // Si detiene el cronómetro, limpiamos la épica y tarea activas
                        $usuario->id_epica = null;
                        $usuario->id_tarea = null;
                    }
                    $usuario->save();
                }

                // Si todo sale bien, retorna la tarea para usarla fuera del Closure
                return $tareaEncontrada;
            });

            // Si llega a este punto, la transacción hizo COMMIT con éxito
            return response()->json([
                'error' => false,
                'mensaje' => 'Cronómetro actualizado correctamente.',
                'datos' => $tarea
            ], 200);

        } catch (Exception $e) {
            // Si algo falló en cualquier punto, se ejecutó un ROLLBACK automático.
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage(),
                'datos' => []
            ], 200);
        }
    }
}
