<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller {
    public function refrescar()
    {
        try {
            // Genera un nuevo token e invalida el anterior
            $nuevoToken = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'error' => false,
                'token' => $nuevoToken
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'mensaje' => 'Sesión expirada'], 401);
        }
}
}
