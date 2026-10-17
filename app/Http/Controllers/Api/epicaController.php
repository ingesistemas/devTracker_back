<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EpicaRequestCreate;
use App\Http\Requests\EpicaRequestUpdate;
use App\Models\EpicaModel;
use App\Models\ProyectoModel;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class EpicaController extends Controller
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
            4 => 'Crítica',
            3 => 'Alta',
            2 => 'Media',
            1 => 'Baja'
        ];

        // Devuelve el nombre según el ID, o 'Media' por defecto si viene nulo
        return $prioridades[$this->attributes['id_prioridad'] ?? 2] ?? 'Media';
    }
    public function consultar(Request $request, $id = null){
        try {
            if ($id) {
                $epicas = EpicaModel::select(
                'id', 
                'nombre', 
                'detalle', 
                'historia',
                'f_pentrega', 
                'fec_fin', 
                'f_rentrega', 
                'id_proyecto', 
                'id_estado', 
                'id_usu_creo', 
                'id_usu_asig',
                'id_prioridad',
                'ruta_pdf',
                'ruta_video',
                'created_at')
                ->with([
                    'proyecto:id,proyecto',
                    'estado:id,estado',
                    'usuarioCreo:id,nombre,email,id_rol',
                    'usuarioCreo.rol:id,rol',
                    'usuarioAsignado:id,nombre,email,id_rol,id_grupo_trabajo',
                    'usuarioAsignado.rol:id,rol',
                    'usuarioAsignado.grupoTrabajo:id,grupo',
                    'tareas:id,nombre,id_epica,id_prioridad,calidad,id_complejidad,id_estado,created_at,id_usu_creo',
                    'tareas.observaciones:id,id_tarea,id_usuario,observacion,created_at',
                    'tareas.observaciones.creador:id,nombre,email',
                    'tareas.estado:id,estado',
                    'tareas.usuarioCreo:id,nombre,email',
                ])
                // Agrupamos las condiciones con OR para evitar mezclar lógica de filtros
                ->where(function ($query) use ($id) {
                    $query->where('id_usu_asig', $id) // 1. Épicas directamente asignadas a ti
                        ->orWhereHas('tareas', function ($q) use ($id) { 
                            // 2. O épicas que tienen al menos una tarea reasignada a ti
                            $q->where('id_reasig', $id); // Reemplaza 'id_reasig' por la columna real en tu tabla tareas
                        });
                })->get();
            
            } else {
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
                    'ruta_pdf',
                    'ruta_video',
                    'created_at'
                )
                ->with([
                    'proyecto:id,proyecto',
                    'estado:id,estado',
                    'usuarioCreo:id,nombre,email,id_rol',
                    'usuarioCreo.rol:id,rol',
                    'usuarioAsignado:id,nombre,email,id_rol,id_grupo_trabajo',
                    'usuarioAsignado.rol:id,rol',
                    'usuarioAsignado.grupoTrabajo:id,grupo',
                    'tareas:id,nombre,id_epica,id_prioridad,id_complejidad,id_estado,created_at,id_usu_creo',
                    'tareas.observaciones:id,id_tarea,id_usuario,observacion,created_at',
                    'tareas.observaciones.creador:id,nombre,email',
                    'tareas.estado:id,estado',
                    'tareas.usuarioCreo:id,nombre,email',
                ])->get();
            
            }

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
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function consultarQa(Request $request, $id = null){
        $id_estado = $request->id_estado;
        
        try {
            if ($id) {
                $epicas = EpicaModel::select(
                    'id', 
                    'nombre', 
                    'detalle', 
                    'historia',
                    'f_pentrega', 
                    'fec_fin', 
                    'f_rentrega', 
                    'id_proyecto', 
                    'id_estado', 
                    'id_usu_creo', 
                    'id_usu_asig',
                    'id_prioridad',
                    'calidad',
                    'ruta_pdf',
                    'ruta_video',
                    'created_at'
                )
                ->with([
                    'proyecto:id,proyecto',
                    'estado:id,estado',
                    'usuarioCreo:id,nombre,email,id_rol',
                    'usuarioCreo.rol:id,rol',
                    'usuarioAsignado:id,nombre,email,id_rol,id_grupo_trabajo',
                    'usuarioAsignado.rol:id,rol',
                    'usuarioAsignado.grupoTrabajo:id,grupo',
                    
                    // Relación de tareas (filtrada por el estado que necesitas)
                    'tareas' => function ($q) use ($id_estado) {
                        $q->select('id', 'nombre', 'id_epica', 'id_prioridad', 'id_complejidad', 'id_estado', 'created_at', 'id_usu_creo')
                        ->where('id_estado', $id_estado); 
                    },
                    
                    // Relaciones que cuelgan de las tareas (¡Aquí SÍ vive historiales y observaciones!)
                    'tareas.observaciones:id,id_tarea,id_usuario,observacion,created_at',
                    'tareas.observaciones.creador:id,nombre,email',
                    'tareas.estado:id,estado',
                    'tareas.usuarioCreo:id,nombre,email',
                    'tareas.historiales:id,id_tarea,detalle,ruta_pdf,ruta_video,id_estado_ini,id_estado_fin,id_usuario,created_at',
                    'tareas.historiales.creador:id,nombre',
                    'tareas.historiales.estadoInicial:id,estado',
                    'tareas.historiales.estadoFinal:id,estado'
                ])
                ->where(function ($query) use ($id, $id_estado) {
                    $query->where(function ($sub) use ($id) {
                            $sub->where('id_usu_asig', $id)
                                ->orWhereHas('tareas', function ($q) use ($id) { 
                                    $q->where('id_reasig', $id);
                                });
                        })
                        ->whereHas('tareas', function ($q) use ($id_estado) {
                            $q->where('id_estado', $id_estado);
                        });
                })
                ->get();
            
            } else {
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
                'ruta_pdf',
                'ruta_video',
                'created_at'
                )
                ->with([
                    'proyecto:id,proyecto',
                    'estado:id,estado',
                    'usuarioCreo:id,nombre,email,id_rol',
                    'usuarioCreo.rol:id,rol',
                    'usuarioAsignado:id,nombre,email,id_rol',
                    'usuarioAsignado.rol:id,rol',
                
                    // Relación de tareas (filtrada por el estado que necesitas)
                    'tareas' => function ($q) use ($id_estado) {
                        $q->select('id', 'nombre', 'id_epica', 'id_prioridad', 'id_complejidad', 'id_estado', 'created_at', 'id_usu_creo')
                        ->where('id_estado', $id_estado); 
                    },
                    
                    // Relaciones que cuelgan de las tareas
                    'tareas.observaciones:id,id_tarea,id_usuario,observacion,created_at',
                    'tareas.observaciones.creador:id,nombre,email',
                    'tareas.estado:id,estado',
                    'tareas.usuarioCreo:id,nombre,email',
                    'tareas.historiales:id,id_tarea,detalle,ruta_pdf,ruta_video,id_estado_ini,id_estado_fin,id_usuario,created_at',
                    'tareas.historiales.creador:id,nombre',
                    'tareas.historiales.estadoInicial:id,estado',
                    'tareas.historiales.estadoFinal:id,estado'
                ])
                ->whereHas('tareas', function ($q) use ($id_estado) {
                    $q->where('id_estado', $id_estado);
                })->get();            
            }

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
                'mensaje' => 'Error' . $e->getMessage(),
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
                    'mensaje' => 'La épica ya se encuentra registrada en ese proyecto.',
                    'datos' => []
                ], 200);
            }

            $pathPdf = null;
            if ($request->hasFile('archivo_pdf')) {
                $pathPdf = $request->file('archivo_pdf')->store('epicas_pdfs', 'public');
            }

            // 3. Procesar Video si viene en la petición
            $pathVideo = null;
            if ($request->hasFile('archivo_video')) {
                $pathVideo = $request->file('archivo_video')->store('epicas_videos', 'public');
                //$data['ruta_video'] = $pathVideo;
            }
            
           
            $nuevaEpica = EpicaModel::create([
                'detalle' => $request->detalle,
                'nombre' => $request->nombre,
                'id_proyecto'=> $request->id_proyecto,
                'id_estado'=> '1',
                'id_usu_creo' => $request->id_usu_creo,
                'id_usu_asig' => $request->id_usu_asig,
                'activo' => 0,
                'ruta_pdf'     => $pathPdf,
                'ruta_video' => $pathVideo
            ]);
            return response()->json([
                'error' => false,
                'mensaje' => 'El registro fue creado correctamente.',
                'data' => $nuevaEpica
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
            $id = $request->id_epica;
            $epica = EpicaModel::select('id')
            ->where('id', $id )->first();

            if (!$epica) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'El ID de la épica no se encuentra registrado en el sistema.'.$id,
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

    public function editar(EpicaRequestUpdate $request)
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

            $epica->update($request->validated());

            return response()->json([
                'error' => false,
                'mensaje' => 'El registro fue editado correctamente.',
                'data' => $epica
            ], 200);
        }catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /* public function estado(Request $request)
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
    } */

    public function eliminar(Request $request){
        try {
            $id = $request->id;

            // 1. Buscar la tarea por ID
            $epica = EpicaModel::find($id);

            // 2. Si la tarea NO existe en la base de datos
            if (!$epica) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'No se encontró la épica registrada en el sistema.',
                    'datos' => []
                ], 404);
            }

            // 4. Si existe y NO tiene tiempo, se elimina la instancia devuelta en $tarea
            $epica->delete();

            return response()->json([
                'error' => false,
                'mensaje' => 'Epica eliminada correctamente.',
                'datos' => []
            ], 200);

        } catch (\Throwable $e) { // Usar \Throwable para capturar tanto Exception como Error de PHP
            return response()->json([
                'error' => true,
                'mensaje' => 'Error: ' . $e->getMessage(),
                'datos' => []
            ], 500);
        }
    }
}
