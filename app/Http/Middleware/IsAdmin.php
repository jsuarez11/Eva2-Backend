<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Verificar si hay usuario autenticado (auth:sanctum ya pasó)
        // 2. Verificar el campo 'isAdmin' (booleano)
        if (!$request->user() || !$request->user()->isAdmin) {
            return response()->json([
                'message' => 'Acceso denegado. Se requieren permisos de administrador.',
                'status' => 403
            ], 403);
        }

        return $next($request);
    }
}
