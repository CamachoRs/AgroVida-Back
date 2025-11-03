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
            $payLoad = JWTAuth::parseToken()->authenticate();
            $inventory = DB::table('inventories')
                ->join('productCategories', 'inventories.categoryId', '=', 'productCategories.id')
                ->where('inventories.establishmentId', $payLoad->establishmentId)
                ->select('inventories.id', 'inventories.nameItem', 'inventories.quantity', 'inventories.unitMeasurement', 'inventories.entryDate', 'inventories.expiryDate', 'inventories.supplierName', 'inventories.categoryId', 'productCategories.name')
                ->get();
            
            if($inventory->isEmpty()){
                return response()->json([
                    'message' => 'No se encontraron productos para este establecimiento'
                ], 404);
            };

            return response()->json([
                'message'=>'Inventarios recuperados exitosamente',
                'data'=>$inventory
            ], 200);
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
            'inventory.categoryId' => 'required|integer|exists:productCategories,id',
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Algunos de los datos proporcionados son incorrectos. Por favor, verifica los campos y vuelve a intentarlo',
                'errors' => $validator->errors()
            ], 422);
        };

        DB::beginTransaction();
        try {
            $payLoad = JWTAuth::parseToken()->authenticate();
            $inventoryData = $request->input('inventory');
            InventoryModel::create([
                'establishmentId' => $payLoad->establishmentId,
                'categoryId' => $inventoryData['categoryId'],
                'nameItem' => $inventoryData['nameItem'],
                'quantity' => $inventoryData['quantity'],
                'unitMeasurement' => $inventoryData['unitMeasurement'],
                'entryDate' => Carbon::today(),
                'expiryDate' => $inventoryData['expiryDate'],
                'supplierName' => $inventoryData['supplierName']
            ]);

            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! El producto ha sido registrado correctamente en el inventario'
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Hubo un error al procesar la solicitud',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'inventory.nameItem' => 'required|string|min:3|max:50',
            'inventory.quantity' => 'required|integer',
            'inventory.unitMeasurement' => 'required|string',
            'inventory.expiryDate' => 'required|date',
            'inventory.supplierName' => 'required|string|min:3|max:50',
            'inventory.categoryId' => 'required|integer|exists:productCategories,id',
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Algunos de los datos proporcionados son incorrectos. Por favor, verifica los campos y vuelve a intentarlo',
                'errors' => $validator->errors()
            ], 422);
        };
        
        DB::beginTransaction();
        try {
            $payLoad = JWTAuth::parseToken()->authenticate();
            $inventoryData = $request->input('inventory');
            $updateInventory = InventoryModel::where('id', $id)
                ->first();
                
            $updateInventory->update([
                'establishmentId' => $payLoad->establishmentId,
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
