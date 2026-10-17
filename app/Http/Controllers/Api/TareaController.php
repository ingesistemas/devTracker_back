<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TareaRequestCreate;
use App\Http\Requests\TareaRequestUpdate;
use App\Models\EpicaModel;
use App\Models\HistorialTareaModel;
use App\Models\ObservacionModel;
use App\Models\ProyectoModel;
use App\Models\TareaModel;
use App\Models\UsuarioModel;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

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
                'orden',
                'nombre',
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
                'calidad',
                'tiempo',
                'created_at'
            )
            ->with([
                'epica:id,nombre,id_proyecto',
                'epica.proyecto:id,proyecto',
                'estado:id,estado',
                'usuarioCreo:id,nombre,email,id_rol',
                'usuarioCreo.rol:id,rol',
                'usuarioAsignado:id,nombre,email,id_rol,id_grupo_trabajo',
                'usuarioAsignado.rol:id,rol',
                'usuarioAsignado.grupoTrabajo:id,grupo',
                'observaciones:id,id_tarea,id_usuario,observacion,created_at',
                'observaciones.creador:id,nombre',
                'historiales:id,detalle,id_tarea,id_estado_ini,ruta_pdf,ruta_video,id_estado_fin,id_usuario,created_at',
                'historiales.creador:id,nombre',
                'historiales.estadoInicial:id,estado',
                'historiales.estadoFinal:id,estado'
            ])
            ->where('id_epica', $id_epica) // Filtra primero por la épica seleccionada
            ->where(function ($query) use ($id) {
                $query->where('id_usu_creo', $id)  // Tareas que creaste
                    ->orWhere('id_reasig', $id);  // O tareas reasignadas a ti
            })->orderBy('orden', 'asc')
            ->get();

            if ($tareas->isNotEmpty()) {
                return response()->json([
                    'error' => false,
                    'mensaje' => 'Tareas encontradas.',
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

    public function crear(TareaRequestCreate $request){
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
                'nombre' => $request->nombre,
                'detalle' => $request->detalle,
                'id_epica' => $request->id_epica,
                'f_pentrega'=> $request->f_pentrega,
                'id_estado'=> '1',
                'id_complejidad' => $request->id_complejidad,
                'id_prioridad' => $request->id_prioridad,
                'id_usu_creo' => $request->id_usu_creo,
                'id_reasig' => $request->id_reasig,
                'calidad' => $request->calidad,
                'activo' => 0,
                'cronometro' => 0,
                'tiempo' => 0
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

    public function editarHistoria(Request $request){
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

    public function editar(TareaRequestUpdate $request){
        try{

            $id = $request->id;
            $tarea = TareaModel::select('id', 'nombre', 'detalle', 'f_pentrega', 'id_estado', 'id_reasig', 
            'id_complejidad', 'id_prioridad')->where('id', $id)->first();
          
            if (!$tarea) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'El ID de la tarea no se encuentra registrado en el sistema.',
                    'datos' => []
                ], 200);
            }            
            $tarea->nombre = $request->nombre;
            $tarea->detalle = $request->detalle;
            $tarea->f_pentrega = $request->f_pentrega;
            $tarea->id_reasig = $request->id_reasig;
            $tarea->id_complejidad = $request->id_complejidad;
            $tarea->id_prioridad = $request->id_prioridad;
            $tarea->calidad = $request->calidad;

           /*  $tarea->update($request->validated()); */
            $tarea->save();

            return response()->json([
                'error' => false,
                'mensaje' => 'El registro fue editado correctamente.',
                'data' => $tarea
            ]);
        }catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function crearObservacion(Request $request){
        try{

            $id = $request->id;
            $tarea = TareaModel::select('id', 'nombre', 'detalle', 'f_pentrega', 'id_estado', 'id_reasig', 
            'id_complejidad', 'id_prioridad')->where('id', $id)->first();
          
            if (!$tarea) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'El ID de la tarea no se encuentra registrado en el sistema.'.  $id,
                    'datos' => $tarea
                ], 200);
            }       
            
            $observacion = ObservacionModel::create([
                'id_tarea'=> $request->id,
                'id_usuario'=> $request->id_reasig,
                'observacion'=> $request->observacion
            ]);

            return response()->json([
                'error' => false,
                'mensaje' => 'El registro fue editado correctamente.',
                'data' => $observacion
            ]);
        }catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }


    public function editarObservacion(Request $request){
        try{

            $id = $request->id_observacion;
            $obs = ObservacionModel::find($id);
          
            if(!$obs) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'El ID de la observación no se encuentra registrado en el sistema.'.  $id,
                    'datos' => $obs
                ], 200);
            }       
            
            $obs->observacion = $request->observacion;
            $obs->save();

            return response()->json([
                'error' => false,
                'mensaje' => 'El registro fue editado correctamente.',
                'data' => $obs
            ]);
        }catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function estado(Request $request){
        try{
            $id = $request->id;
            $tarea = TareaModel::find($id);

            if (!$tarea) {
                return response()->json([
                    'error' => false,
                    'mensaje' => 'No se encontró el tarea registrado en el sistema.'
                ], 200);
            }

            $tarea->activo = !$tarea->activo;
            $tarea->save();

            return response()->json([
                'error' => false,
                'mensaje' => 'Tarea actualizada correctamente.',
                'datos' => $tarea
            ], 200);        
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function estadoTarea(Request $request){
        try{
            $id = $request->id;
            $id_estado = $request->id_estado;
            $id_tarea = $request->id_tarea;
            $id_estado_fin = $request->id_estado;
            $id_usuario = $request->id_usuario;

            $tarea = DB::transaction(function () use ($id, $id_estado, $id_tarea, $id_estado_fin, $id_usuario) {
                $buscarTarea = TareaModel::find($id);

                if (!$buscarTarea) {
                    return response()->json([
                        'error' => false,
                        'mensaje' => 'No se encontró el tarea registrado en el sistema.'
                    ], 200);
                }

                $id_estado_ini = $buscarTarea->id_estado;
                $buscarTarea->id_estado = $id_estado;
                $buscarTarea->save();

                $historial = HistorialTareaModel::create([
                    'id_tarea' => $id,
                    'id_estado_ini' => $id_estado_ini,
                    'id_estado_fin' => $id_estado_fin,
                    'id_usuario' => $id_usuario
                ]);

                return response()->json([
                    'error' => false,
                    'mensaje' => 'Tarea actualizada correctamente.',
                    'datos' => $buscarTarea
                ], 200);  
            });
            return $tarea;
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function crearRechazo(Request $request){
        try{
            $id = $request->id;
            $id_estado = $request->id_estado;
            $id_tarea = $request->id_tarea;
            $id_estado_fin = $request->id_estado;
            $id_usuario = $request->id_usuario;
            $detalle = $request->detalle;
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

            $tarea = DB::transaction(function () use ($id, $id_estado, $id_tarea, $detalle, $id_estado_fin, $id_usuario, $pathPdf, $pathVideo) {
                $buscarTarea = TareaModel::find($id_tarea);

                if (!$buscarTarea) {
                    return response()->json([
                        'error' => true,
                        'mensaje' => 'No se encontró el tarea registrado en el sistema.'
                    ], 200);
                }

                $id_estado_ini = $buscarTarea->id_estado;
                $buscarTarea->id_estado = $id_estado;
                $buscarTarea->save();

                $historial = HistorialTareaModel::create([
                    'id_tarea' => $id_tarea,
                    'detalle'   => $detalle,
                    'id_estado_ini' => $id_estado_ini,
                    'id_estado_fin' => $id_estado_fin,
                    'id_usuario' => $id_usuario,
                    'ruta_pdf'     => $pathPdf,
                    'ruta_video' => $pathVideo
                ]);

                return response()->json([
                    'error' => false,
                    'mensaje' => 'Tarea actualizada correctamente.',
                    'datos' => $buscarTarea
                ], 200);  
            });
            return $tarea;
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function eliminar(Request $request){
        try {
            $id = $request->id;

            // 1. Buscar la tarea por ID
            $tarea = TareaModel::select('id', 'tiempo')->where('id', $id)->first();

            // 2. Si la tarea NO existe en la base de datos
            if (!$tarea) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'No se encontró la tarea registrada en el sistema.',
                    'datos' => []
                ], 404);
            }

            // 3. Validar si ya tiene tiempo ejecutado
            if ($tarea->tiempo > 0) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'La tarea ya cuenta con registros o tiempos ejecutados, por tanto no se puede eliminar.',
                    'datos' => []
                ], 200);
            }

            // 4. Si existe y NO tiene tiempo, se elimina la instancia devuelta en $tarea
            $tarea->delete();

            return response()->json([
                'error' => false,
                'mensaje' => 'Tarea eliminada correctamente.',
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

    public function eliminarObs(Request $request){
        try {
            $id = $request->id;

            // 1. Buscar la tarea por ID
            $obs = ObservacionModel::find($id);

            // 2. Si la tarea NO existe en la base de datos
            if (!$obs) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'La observación no se encontró registrada en el sistema.',
                    'datos' => []
                ], 404);
            }

            // 4. Si existe y NO tiene tiempo, se elimina la instancia devuelta en $tarea
            $obs->delete();

            return response()->json([
                'error' => false,
                'mensaje' => 'La observación fue eliminada correctamente.',
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

    public function cronometro(Request $request){
        try {
            $id = $request->id;
           /*  $idUsuario = $request->id_usu_creo; */
            $idUsuario = $request->id_reasig;
            $cronometro = $request->cronometro ? 0 : 1;
            $id_epica = $request->input('epica.id');
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
                        $usuario->id_epica = 0;
                        $usuario->id_tarea = 0;
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

    function temporizador(Request $request){
        try {
            $id = $request->id;
            $id_usuario = $request->id_usuario;
            $tarea = TareaModel::find($id);
            $fecha = now()->format('Y-m-d H:i:s');
            $tiempo = ($tarea->tiempo + 2);
            $tarea->fec_ini = $fecha;
            $tarea->tiempo = $tiempo;      
            $tarea->save();

            $usuario = UsuarioModel::select('id','id_tarea','tiempo','fec_ini')
            ->where('id', $id_usuario)
            ->where('id_tarea', $id)->first();

            if($request->tarea == ''){
               /*  $usuario->tiempo = 0;
                $usuario->fec_ini = $fecha;
                $usuario->id_tarea = 0;*/
                $usuario->id_epica = 0; 
            } else {
                $usuario->id_tarea = 77;
                $usuario->tiempo = $tiempo;
                $usuario->fec_ini = $fecha;
            }
            $usuario->save();
            
        } catch (Exception $e) {
            // Si algo falló en cualquier punto, se ejecutó un ROLLBACK automático.
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage(),
                'datos' => []
            ], 200);
        }
    }

    public function reordenarTareas(Request $request) {
        $posiciones = $request->input('tareas', []); 
        DB::transaction(function () use ($posiciones) {
            foreach ($posiciones as $pos) {
                if (isset($pos['id'], $pos['orden'])) {
                    TareaModel::where('id', $pos['id'])->update(['orden' => $pos['orden']]);
                }
            }
        });
        return response()->json(['mensaje' => 'Orden actualizado correctamente']);
    }
}
