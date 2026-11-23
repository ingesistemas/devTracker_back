<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EpicaRequestCreate;
use App\Http\Requests\EpicaRequestUpdate;
use App\Models\EpicaModel;
use App\Models\ProyectoModel;
use App\Models\RequisitoModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Dompdf\Dompdf;
use Dompdf\Options;


class EpicaController extends Controller
{
    protected $mensaje = "";
    protected $error = false;
    protected $data = [];
    protected $status = "200";
    protected $totalPorcetajeEpica = 0;

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
                'tareas:id,nombre,id_epica,id_prioridad,calidad,id_complejidad,id_estado,created_at,id_usu_creo,f_pentrega,hrs_estimadas,tiempo,id_reasig',
                'tareas.observaciones:id,id_tarea,id_usuario,observacion,created_at',
                'tareas.observaciones.creador:id,nombre,email',
                'tareas.estado:id,estado',
                'tareas.usuarioCreo:id,nombre,email',
                'tareas.requisitoTareas:id,id_tarea,id_requisito,porcentaje,checked_id',
                'requisitos:id,id_epica,requisito,calidad,desarrollo',
                'tareas.historiales:id,detalle,id_tarea,id_estado_ini,ruta_pdf,ruta_video,id_estado_fin,id_usuario,created_at',
                'tareas.historiales.creador:id,nombre',
                'tareas.historiales.estadoInicial:id,estado',
                'tareas.historiales.estadoFinal:id,estado',
                'tareas.usuarioAsignado:id,nombre,email',
                // Última asignación de la tarea (para obtener su fecha de asignación)
                'tareas.ultimaAsignacion',
            ])
            ->where(function ($query) use ($id) {
                $query->where('id_usu_asig', $id)
                    ->orWhereHas('tareas', function ($q) use ($id) {
                        $q->where('id_reasig', $id);
                    });
            })
            ->get()
            ->map(function ($epica) {
                // Extraemos todas las requisitoTareas cargadas en las tareas de esta épica
                $todasLasRequisitoTareas = $epica->tareas->pluck('requisitoTareas')->flatten();

                // Cruzamos cada requisito de la épica con sus respectivas requisitoTareas
                $epica->requisitos->transform(function ($requisito) use ($todasLasRequisitoTareas) {
                    $requisito->requisito_tareas = $todasLasRequisitoTareas->where('id_requisito', $requisito->id)->values();
                    return $requisito;
                });

                // Exponemos la fecha de asignación directamente en cada tarea si lo deseas
                $epica->tareas->transform(function ($tarea) {
                    $tarea->fecha_asignacion = optional($tarea->ultimaAsignacion)->created_at;
                    return $tarea;
                });

                return $epica;
            });
            
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
                'tareas:id,nombre,id_epica,id_prioridad,id_complejidad,id_estado,created_at,id_usu_creo,f_pentrega,hrs_estimadas,tiempo,id_reasig',
                'tareas.observaciones:id,id_tarea,id_usuario,observacion,created_at',
                'tareas.observaciones.creador:id,nombre,email',
                'tareas.estado:id,estado',
                'tareas.usuarioCreo:id,nombre,email',
                'tareas.requisitoTareas:id,id_tarea,id_requisito,porcentaje,checked_id',
                'requisitos:id,id_epica,requisito,calidad,desarrollo',
                'tareas.historiales:id,detalle,id_tarea,id_estado_ini,ruta_pdf,ruta_video,id_estado_fin,id_usuario,created_at',
                'tareas.historiales.creador:id,nombre',
                'tareas.historiales.estadoInicial:id,estado',
                'tareas.historiales.estadoFinal:id,estado',
                'tareas.usuarioAsignado:id,nombre,email',
                // Última asignación de la tarea para obtener su fecha de asignación
                'tareas.ultimaAsignacion',
            ])
            ->get()
            ->map(function ($epica) {
                // 1. Extraemos todas las relaciones requisitoTareas asociadas a las tareas de la épica
                $todasLasRequisitoTareas = $epica->tareas->pluck('requisitoTareas')->flatten();

                // 2. Inyectamos la relación filtrada dentro de cada objeto requisito
                $epica->requisitos->transform(function ($requisito) use ($todasLasRequisitoTareas) {
                    $requisito->requisito_tareas = $todasLasRequisitoTareas
                        ->where('id_requisito', $requisito->id)
                        ->values();
                    return $requisito;
                });

                // 3. Exponemos la fecha de asignación en cada tarea
                $epica->tareas->transform(function ($tarea) {
                    $tarea->fecha_asignacion = optional($tarea->ultimaAsignacion)->created_at;
                    return $tarea;
                });

                return $epica;
            });
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
                        $q->select('id', 'nombre', 'id_epica', 'id_prioridad', 'id_complejidad', 'id_estado', 'created_at', 'id_usu_creo',
                        'f_pentrega')
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
                        $q->select('id', 'nombre', 'id_epica', 'id_prioridad', 'id_complejidad', 'id_estado', 'created_at', 'id_usu_creo',
                        'f_pentrega')
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
            $id_estado = null;
            if($request->id_usu_asig){
                $id_estado = 1;
            }
           
            $nuevaEpica = EpicaModel::create([
                'detalle' => $request->detalle,
                'nombre' => $request->nombre,
                'id_proyecto'=> $request->id_proyecto,
                'id_prioridad'=> $request->id_prioridad,
                'id_estado'=> $id_estado,
                'historia'    => $request->historia ?? '',
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
            $datos = $request->validated();
            // Asignamos el estado según tu regla de negocio
            $datos['id_estado'] = $request->id_usu_asig ? 1 : $epica->id_estado;
            $epica->update($datos);

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

    public function eliminar(Request $request){
        try {
            $id = $request->id;

            $proceso = DB::connection('empresa_dinamica')->transaction(function () use ($id){
                $epica = EpicaModel::find($id);
                $requisitos = RequisitoModel::where('id_epica', $id)->get();
                
                // Si la épica no existe en la base de datos
                if (!$epica) {
                    throw new Exception('No se encontró la épica registrada en el sistema.');
                }

                // Borramos la épica
                $epica->delete();

                // Borramos cada uno de los requisitos de la colección correctamente
                if ($requisitos->count() > 0) {
                    foreach ($requisitos as $requisito) {
                        $requisito->delete();
                    }
                }
            });
           
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

    public function getTotalPorcentaje(float $porcentaje, int $id_checked) {
        //if (!$requisitoTareas || $requisitoTareas.length === 0) return 0;

        $valorTotalCalculado = 0;
        $porcentajeBase = $porcentaje;
        $checkedId = $id_checked;       
        

        // 1. Aplicamos la regla base del 70% a cada elemento
        $valorItem = 0;
        

        // 2. Sumamos el bono adicional según el checked_id de la tarea
        switch ($checkedId) {
            case 3:
                // (valor * 70) / 100 (No suma extra, o se queda con la base)
                $valorItem = ($porcentajeBase * 70) / 100;
                break;
            case 4:
                $valorItem = ($porcentajeBase * 90) / 100;
                break;
            case 8:
                $valorItem = ($porcentajeBase * 90) / 100;
                $valorItem = $valorItem - 10;
                break;
            case 9:
                $valorItem = ($porcentajeBase * 95) / 100;
                break;
            case 5:
                $valorItem = ($porcentajeBase * 95) / 100;
                break;
            case 10:
                $valorItem = ($porcentajeBase * 90) / 100;
                break;
            case 11:
                $valorItem = ($porcentajeBase * 100) / 100;
                break;
            default:
                // Si no tiene un estado válido, no suma valor
                $valorItem = 0;
                break;
        }

        // Acumulamos el valor de todas las tareas
        $valorTotalCalculado = $valorTotalCalculado + $valorItem;
        

        // 3. Redondeamos a 1 decimal (o 2 según prefieras) y retornamos
        return number_format($valorTotalCalculado, 1, '.', '');
    }
    
    public function generarPdfEpicas(Request $request)
    {
        $epicas = $request->input('datos', []);

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
                h1 { color: #028ec1; text-align: center; }
                h3, h5, .titulo { color: #005ca7; margin: 0; }
                .epica-box { 
                    border: 1px solid #eaeaea; 
                    padding: 10px; 
                    margin-bottom: 15px; 
                    overflow: hidden;  
                }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th, td { border: 1px solid #eaeaea; padding: 6px; font-size: 10px; text-align: left; }
                th { background-color: #028ec1; color: white; }
                .avance {background-color: #54cba1; color: #FFF; border-radius: 3px; padding: 3px;}
                .fondoTitulo { background-color: #e0e5ea !important;}
            </style>
        </head>
        <body>
            <h1>Reporte de Épicas</h1>

            <?php if (empty($epicas)): ?>
                <p>No hay datos para mostrar.</p>
            <?php else: ?>
                <?php foreach ($epicas as $epica):       
                    $this->totalPorcetajeEpica = 0;
                    foreach ($epica['requisitos'] as $requisito):
                        foreach ($requisito['requisito_tareas'] as $requisitoTarea):
                            $porcentaje = $this->getTotalPorcentaje((float)$requisitoTarea['porcentaje'],(int)$requisitoTarea['checked_id']);
                            $this->totalPorcetajeEpica = $this->totalPorcetajeEpica + $porcentaje;
                        endforeach;
                    endforeach;                   
                                    
                    $fechaStr = '';
                    if (!empty($epica['created_at'])) {
                            // Creamos el objeto DateTime correctamente
                            $fechaObj = new \DateTime($epica['created_at']);
                            // Aplicamos format() al objeto, NO al string
                            $fechaStr = ' - ' . $fechaObj->format('d/m/Y H:i');
                        }
                    ?>
                    <div class="epica-box">
                        <!-- Título a la izquierda, usuario a la derecha -->
                        <div style="float: right; font-size: 9px; color: #555; padding-top: 2px; margin-left: 5px">
                            <strong>Asignado:</strong> <?php echo htmlspecialchars($epica['usuario_asignado']['nombre'] ?? 'Sin asignar');
                            echo $fechaStr; ?>
                        </div>
                        <div style="float: left;" >
                            <h4 class="titulo">Proyecto: <?php echo $epica['proyecto']['proyecto'] ?? ''; ?> - Épica: <?php echo htmlspecialchars($epica['nombre'] ?? '')." <span class='avance'>".$this->totalPorcetajeEpica. "%</span> de avance. "; ?></h4>
                        </div>
                        <div style="clear: both;"></div>
                        
                        <p style="margin-top: 10px;"><strong>Detalle:</strong> <?php echo $epica['detalle'] ?? ''; ?></p>

                        <h4>Tareas:</h4>
                        <table>
                            <tr>
                                <th>ID</th>
                                <th>Nombre Tarea</th>
                                <th>Estado</th>
                                <th>Hrs est.</th>
                                <th>Hrs lab.</th>
                                <th>Hrs dif.</th>
                            </tr>

                            <?php  if (!empty($epica['tareas'])): ?>
                                <?php foreach ($epica['tareas'] as $index => $tarea): 
                                    $hrs_est = $tarea['hrs_estimadas'] ?? 0;                                    
                                    $horas = floor($tarea['tiempo']) / 60;
                                    $horasDecimal = (float) $horas;
                                    $horas = floor($horasDecimal);
                                    $minutos = round(($horasDecimal - $horas) * 60);
                                    $hrs_dif = ($hrs_est - $horas);
                                ?>
                                    <tr class="fondoTitulo">
                                        <td><?php echo $tarea['id']; ?></td>
                                        <td><?php echo htmlspecialchars($tarea['nombre'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($tarea['estado']['estado'] ?? ''); ?></td>
                                        <td><?php echo $hrs_est; ?> hrs</td>
                                        <td><?php echo $horas . ':' . str_pad($minutos, 2, '0', STR_PAD_LEFT) . ' hrs'?></td>
                                        <td>  
                                            <span style="color: <?= $hrs_dif < 0 ? 'red' : 'green' ?>;">
                                                <?php echo $hrs_dif.':' . str_pad($minutos, 2, '0', STR_PAD_LEFT) . ' hrs' ?>
                                            </span>   
                                        </td>                                       
                                    </tr>
                                    
                                    <?php foreach ( $tarea['observaciones'] as $iobs => $obs): ?>
                                        <tr >
                                            <td style="border: 0" colspan="6">                                                
                                                <?php 
                                                    $fechaObs = new \DateTime($obs['created_at']);
                                                    echo '<strong>'.$fechaObs->format('d/m/Y H:i').' - '
                                                    .$obs['creador']['nombre'].'</strong>'
                                                    .'<br>'.$obs['observacion']; 
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>

                        </table>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </body>
        </html>
        <?php
        $html = ob_get_clean();

        $dompdf = new Dompdf();
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="reporte_epicas.pdf"',
        ]);
    }
}
