<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\InventoryModel;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class InventoryController extends Controller
{
    public function index()
    {
        try {
            $this->tokenValidation();
            $payload = JWTAuth::parseToken()->getPayload();
            $role = $payload->get('role');
            if($role !== 'dueño'){
                return response()->json([
                    'message' => 'Acceso denegado. No puede acceder a este recurso'
                ], 403);
            };

            $establishmentId = $payload->get('establishment');
            $inventory = InventoryModel::select('id', 'nameItem', 'quantity', 'unitMeasurement', 'entryDate', 'expiryDate', 'supplierName', 'categoryId')
                ->with(['category' => function ($query) {
                    $query->select('id', 'nameCategory')->where('status', true);
                }])
                ->where('establishmentId', $establishmentId)
                ->where('status', true)
                ->get();
            
            if($inventory->isEmpty()){
                return response()->json([
                    'message' => 'No se encontraron productos para este establecimiento'
                ], 404);
            };

            return response()->json([
                'message'=>'Inventarios recuperados exitosamente',
                'data'=>$inventory
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Hubo un error al procesar la solicitud',
                'error' => $th->getMessage()
            ], 500);
        }        
    }


    private function tokenValidation()
    {
        try {
            JWTAuth::parseToken();
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
}
