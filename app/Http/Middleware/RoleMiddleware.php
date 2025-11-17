<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        try {
            $payLoad = JWTAuth::parseToken()->authenticate();
            $userRole = $payLoad->role;
            if (!in_array($userRole, $roles)) {
                return response()->json([
                    'message' => 'Acceso denegado. No tienes permisos para acceder a este recurso.'
                ], 403);
            }

            return $next($request);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'message' => 'El token ha expirado. Por favor inicia sesión nuevamente'
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'message' => 'El token es inválido. Por favor inicia sesión nuevamente'
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'No se proporcionó un token o hubo un error al procesarlo'
            ], 401);
        }
        catch (\Throwable $e) {
            return response()->json([
                'message' => 'No se pudo verificar el token.',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}
