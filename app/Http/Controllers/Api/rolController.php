<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\loginRequest;
use App\Models\empresaModel;
use App\Models\rolModel;
use App\Models\usuarioModel;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class rolController extends Controller
{
    protected $mensaje = "";
    protected $error = false;
    protected $data = [];
    protected $status = "200";
    public function consultar(Request $request)
    {
        try {
           
            $roles = rolModel::select('id', 'rol')->get();

            if ($roles->isNotEmpty()) {
                return response()->json([
                    'error' => false,
                    'mensaje' => 'Roles encontrados.',
                    'datos' => $roles
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
}
