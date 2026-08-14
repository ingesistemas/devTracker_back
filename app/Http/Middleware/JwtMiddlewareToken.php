<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Services\EmpresaConnectionService;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
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

        return $next($request);
    }
}

