<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\EmpresaConnectionService;
use Symfony\Component\HttpFoundation\Response;

class EmpresaJwtMiddleware
{
    public function handle($request, Closure $next): Response
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Token inválido o no enviado.'
            ], 401);
        }

        $id_empresa = $payload->get('id_empresa');
        $id_usuario = $payload->get('id_usuario');

        if (!$id_empresa) {
            return response()->json([
                'error' => true,
                'message' => 'ID no presente en el token.'
            ], 401);
        }

        EmpresaConnectionService::conectarPorNit($id_empresa);

         // Guardamos el ID para que esté disponible en el controlador
        $request->merge([
            'id_empresa' => $id_empresa,
            'id_usuario_actual' => $id_usuario
        ]);

        return $next($request);
    }
}
