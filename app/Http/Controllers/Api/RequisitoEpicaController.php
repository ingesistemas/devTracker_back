<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
/* use App\Http\Requests\loginRequest;
 */use App\Http\Requests\ProyectoRequest;
use App\Http\Requests\RequisitoEpicaRequest;
/* use App\Models\empresaModel; */
use App\Models\ProyectoModel;
use App\Models\RequisitoModel;
use App\Models\RequisitoTareaModel;
use App\Models\TareaModel;
/* use App\Models\rolModel;
use App\Models\usuarioModel; */
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\Switch_;

class RequisitoEpicaController extends Controller
{
    protected $mensaje = "";
    protected $error = false;
    protected $data = [];
    protected $status = "200";
    public function consultar(Request $request)
    {
        try {
            if(!$request->id_epica){
                return response()->json([
                    'error' => false,
                    'mensaje' => 'Falta el ID de la épica a consultar.',
                    'datos' => []
                ], 200);
            }
            $requisitos = RequisitoModel::select('id', 'id_epica', 'requisito', 'calidad', 'desarrollo', 'checked_id')
            ->where('id_epica', $request->id_epica)
            ->get();

            if ($requisitos->isNotEmpty()) {
                return response()->json([
                    'error' => false,
                    'mensaje' => 'Requisitos encontrados.',
                    'datos' => $requisitos
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

    public function crear(RequisitoEpicaRequest $request)
    {
        try{
            $requisito = $request->requisito;
            $id_epica = $request->id_epica;

            $busqueda = RequisitoModel::with(['epica:id,nombre'])
            ->select('id', 'requisito')
            ->where('requisito', $requisito)
            ->where('id_epica', $id_epica)
            ->first();

            if ($busqueda) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'Este requisito ya se encuentra registrado en el sistema.',
                    'datos' => []
                ], 200);
            }

            $nuevoRequisito = RequisitoModel::create([
                'requisito' => $requisito,
                'id_epica' => $id_epica,
                'activo' => 0
            ]);
            return response()->json([
                'error' => false,
                'mensaje' => 'El registro fue creado correctamente.',
                'data' => $nuevoRequisito
            ]);
        }catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function crearRequisitoTarea(Request $request)
    {
        try {
            // Usamos explícitamente la conexión 'empresa_dinamica' para la transacción
            $nuevoRequisito = DB::connection('empresa_dinamica')->transaction(function () use ($request) {
                $id_tarea = $request->id_tarea;
                $id_requisito = $request->id_requisito;
                $id_complejidad = $request->id_complejidad;
                $grado_complejidad = 0;
                switch($id_complejidad){
                    case 4:
                        $grado_complejidad = 1;
                        break;
                    case 3:
                        $grado_complejidad = 2;
                        break;
                    case 2:
                        $grado_complejidad = 3;
                        break;
                    case 1:
                        $grado_complejidad = 4;
                        break;
                    default:
                        //$valorComplijidad = 1; 
                        break;
                }

                // Creamos el registro en la tabla pivote bajo la misma conexión
                $registro = RequisitoTareaModel::create([
                    'id_tarea' => $id_tarea,
                    'id_requisito' => $id_requisito,
                    'grado_complejidad' => $grado_complejidad
                ]);

                // Ejecutamos el cálculo de porcentajes
                $this->calculoProgeso($id_requisito);

                return $registro;
            });

            return response()->json([
                'error' => false,
                'mensaje' => 'El registro fue creado correctamente.',
                'data' => $nuevoRequisito
            ], 201);

        } catch (Exception $e) {
            // Al fallar dentro del bloque de 'empresa_dinamica', hará rollback automático en esa conexión
            return response()->json([
                'error' => true,
                'mensaje' => 'Error exacto: ' . $e->getMessage() . ' en el archivo ' . $e->getFile() . ' línea ' . $e->getLine(),
                'data' => []
            ], 500);
        }
    }

    public function calculoProgeso(int $id_requisito)
    {
        $requisitos = RequisitoTareaModel::where('id_requisito', $id_requisito)->get();
        $totalRegistros = $requisitos->count();

        if ($totalRegistros === 0) {
            return 0;
        }

        // 1. Calculamos la suma de puntos ponderados (Escala lineal inversa de 1 a 4)
        // Grado 1 (Compleja) = 4 pts | Grado 2 (Alta) = 3 pts | Grado 3 (Media) = 2 pts | Grado 4 (Baja) = 1 pt
        $sumaPuntos = 0;
        foreach ($requisitos as $item) {
            $grado = min(max($item->grado_complejidad, 1), 4);
            $puntos = 5 - $grado;
            $sumaPuntos += $puntos;
        }

        // 2. Iteramos para calcular el porcentaje de cada tarea
        foreach ($requisitos as $value) {
            $grado = min(max($value->grado_complejidad, 1), 4);
            $puntos = 5 - $grado;

            if ($sumaPuntos > 0) {
                $valorPorcentaje = ($puntos / $sumaPuntos) * 100;
            } else {
                $valorPorcentaje = 100 / $totalRegistros;
            }
            $value->porcentaje = round($valorPorcentaje, 2);
            $value->save();
        }
        return $totalRegistros;
    }

    public function eliminarRequisitoTarea(Request $request)
    {
        try{
            $id_requisito = $request->id_requisito;
            $id_tarea = $request->id_tarea;
            $requisito = RequisitoTareaModel::where('id_requisito', $id_requisito)
            ->where('id_tarea', $id_tarea)
            ->first();

            if (!$requisito) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'No se encontró el requisito registrado en el sistema.'
                ], 200);
            }

            $requisito->delete();

            return response()->json([
                'error' => false,
                'mensaje' => 'El requisito fue eliminado correctamente.',
                'datos' => []
            ], 200);        
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error' . $e->getMessage(),
                'data' => []
            ]);
        }
    }


    public function editar(RequisitoEpicaRequest $request)
    {
        try {
            $requisito = RequisitoModel::find($request->id);

            if (!$requisito) {
                return response()->json([
                    'error' => false,
                    'mensaje' => 'Este requisito no se encuentra registrado en el sistema.',
                    'datos' => []
                ], 200);
            }

            // Actualizamos usando los datos ya validados por el FormRequest
            $requisito->update($request->validated());

            return response()->json([
                'error' => false,
                'mensaje' => 'El registro fue editado correctamente.',
                'data' => $requisito
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => 'Error: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function eliminar(Request $request)
    {
        try{
            $id = $request->id;
            $requisito = RequisitoModel::find($id);

            if (!$requisito) {
                return response()->json([
                    'error' => true,
                    'mensaje' => 'No se encontró el requisito registrado en el sistema.'
                ], 200);
            }

            $requisito->delete();

            return response()->json([
                'error' => false,
                'mensaje' => 'El requisito fue eliminado correctamente.',
                'datos' => []
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
