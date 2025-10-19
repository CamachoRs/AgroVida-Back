<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\InventoryModel;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryController extends Controller
{
    public function index()
    {
        try {
            $payLoad = JWTAuth::parseToken()->getPayload();
            $establishmentId = $payLoad->get('establishment');
            $inventory = InventoryModel::select('id', 'nameItem', 'quantity', 'unitMeasurement', 'entryDate', 'expiryDate', 'supplierName', 'categoryId')
                ->with(['category' => function ($query) {
                    $query->select('id', 'nameCategory', 'description')->where('status', true);
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'inventory.nameItem' => 'required|string|min:3|max:50',
            'inventory.quantity' => 'required|integer',
            'inventory.unitMeasurement' => 'required|string',
            'inventory.expiryDate' => 'required|date',
            'inventory.supplierName' => 'required|string|min:3|max:50',
            'inventory.categoryId' => 'required|integer|exists:categories,id',
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Algunos de los datos proporcionados son incorrectos. Por favor, verifica los campos y vuelve a intentarlo',
                'errors' => $validator->errors()
            ], 422);
        };

        DB::beginTransaction();
        try {
            $payLoad = JWTAuth::parseToken()->getPayload();
            $establishmentId = $payLoad->get('establishment');
            $inventoryData = $request->input('inventory');
            InventoryModel::create([
                'establishmentId' => $establishmentId,
                'categoryId' => $inventoryData['categoryId'],
                'nameItem' => $inventoryData['nameItem'],
                'quantity' => $inventoryData['quantity'],
                'unitMeasurement' => $inventoryData['unitMeasurement'],
                'entryDate' => Carbon::today(),
                'expiryDate' => $inventoryData['expiryDate'],
                'supplierName' => $inventoryData['supplierName'],
                'status' => true
            ]);

            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! El producto ha sido registrado correctamente en el inventario'
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Hubo un error al procesar la solicitud',
                'error' => $th->getMessage(),
                'prueba'=>$establishmentId
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'inventory.id' => 'required|numeric',
            'inventory.nameItem' => 'required|string|min:3|max:50',
            'inventory.quantity' => 'required|integer',
            'inventory.unitMeasurement' => 'required|string',
            'inventory.expiryDate' => 'required|date',
            'inventory.supplierName' => 'required|string|min:3|max:50',
            'inventory.categoryId' => 'required|integer|exists:categories,id',
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Algunos de los datos proporcionados son incorrectos. Por favor, verifica los campos y vuelve a intentarlo',
                'errors' => $validator->errors()
            ], 422);
        };
        
        DB::beginTransaction();
        try {
            $inventoryData = $request->input('inventory');
            $payLoad = JWTAuth::parseToken()->getPayload();
            $establishmentId = $payLoad->get('establishment');
            $updateInventory = InventoryModel::where('id', $id)->first();
            $updateInventory->update([
                'establishmentId' => $establishmentId,
                'categoryId' => $inventoryData['categoryId'],
                'nameItem' => $inventoryData['nameItem'],
                'quantity' => $inventoryData['quantity'],
                'unitMeasurement' => $inventoryData['unitMeasurement'],
                'expiryDate' => $inventoryData['expiryDate'],
                'supplierName' => $inventoryData['supplierName']
            ]);

            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! El producto ha sido actualizado correctamente en el inventario'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Hubo un error al procesar la solicitud',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $inventory = InventoryModel::where('id', $id);
            $inventory->delete();
            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! El producto ha sido eliminado correctamente del inventario'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Hubo un error al procesar la solicitud',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
