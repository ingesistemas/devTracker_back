<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GrupoTrabajoRequestCreate;
use App\Http\Requests\GrupoTrabajoRequestUpdate;
use App\Models\EstadoModel;
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

class EstadoController extends Controller
{
    protected $mensaje = "";
    protected $error = false;
    protected $data = [];
    protected $status = "200";
    public function consultar(Request $request)
    {
        try {
           
           $estados = EstadoModel::select('id', 'estado', 'detalle', 'permiso')->get();

            if ($estados->isNotEmpty()) {
                return response()->json([
                    'error' => false,
                    'mensaje' => 'Estados de trabajo encontrados.',
                    'datos' => $estados
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
}
