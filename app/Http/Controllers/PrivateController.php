<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;

class PrivateController extends Controller
{
    public function logout()
    {
        try {
            $token = JWTAuth::parseToken();
            JWTAuth::invalidate($token);
            return response()->json([
                'message' => 'Sesión cerrada correctamente'
            ], 200);
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
    }

    public function refresh()
        {
            try {
                $newToken = auth()->refresh();
                return response()->json([
                    'access_token' => $newToken,
                    'token_type' => 'bearer',
                    'expires_in' => auth()->factory()->getTTL() * 60
                ], 200);
            } catch (TokenBlacklistedException $e) {
                return response()->json([
                    'message' => 'El token ha sido revocado. Por favor inicia sesión nuevamente'
                ], 401);
                
            } catch (JWTException $e) {
                return response()->json([
                    'message' => 'No se proporcionó un token o hubo un error al procesarlo'
                ], 500);
            }
        }
}
