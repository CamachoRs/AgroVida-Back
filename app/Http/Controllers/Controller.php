<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function refresh(Request $request)
    {
        try {
            $newToken = auth()->refresh();
            return response()->json([
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'No se pudo refrescar el token. Por favor, inicia sesión nuevamente.',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}