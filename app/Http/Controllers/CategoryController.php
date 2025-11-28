<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategoryModel;
use App\Models\AnimalCategoryModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function indexProduct()
    {
        $categories = ProductCategoryModel::where('status', true)
            ->select('id', 'name', 'description', 'status')
            ->get();
        return response()->json([
            'message'=>'Categorías recuperadas exitosamente',
            'data'=>$categories
        ], 200);
    }

    public function storeProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product.name' => 'required|string|min:3|max:50',
            'product.description' => 'sometimes|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Algunos de los datos proporcionados son incorrectos. Por favor, verifica los campos y vuelve a intentarlo',
                'errors' => $validator->errors()
            ], 422);
        };

        DB::beginTransaction();
        try {
            $productData = $request->input('product');
            ProductCategoryModel::create([
                'name' => $productData['name'],
                'description' => $productData['description'] ?? null,
                'status' => true
            ]);

            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! La cateogría ha sido registrada correctamente'
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Hubo un error al procesar la solicitud',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function updateProduct(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'product.name' => 'required|string|min:3|max:50',
            'product.description' => 'sometimes|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Algunos de los datos proporcionados son incorrectos. Por favor, verifica los campos y vuelve a intentarlo',
                'errors' => $validator->errors()
            ], 422);
        };

        DB::beginTransaction();
        try {
            $productData = $request->input('product');
            $product = ProductCategoryModel::findOrFail($id);
            $product->fill($productData);
            $product->save();
            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! La cateogría ha sido actualizada correctamente'
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Hubo un error al procesar la solicitud',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function deleteProduct($id)
    {
        DB::beginTransaction();
        try {
            $product = ProductCategoryModel::findOrFail($id);
            $product->delete();
            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! La cateogría ha sido eliminada correctamente'
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Hubo un error al procesar la solicitud',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function indexAnimal()
    {
        $categories = AnimalCategoryModel::where('status', true)
            ->select('id', 'name', 'description', 'status')
            ->get();
        return response()->json([
            'message'=>'Categorías recuperadas exitosamente',
            'data'=>$categories
        ], 200);
    }

    public function storeAnimal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'animal.name' => 'required|string|min:3|max:50',
            'animal.description' => 'sometimes|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Algunos de los datos proporcionados son incorrectos. Por favor, verifica los campos y vuelve a intentarlo',
                'errors' => $validator->errors()
            ], 422);
        };

        DB::beginTransaction();
        try {
            $animalData = $request->input('animal');
            AnimalCategoryModel::create([
                'name' => $animalData['name'],
                'description' => $animalData['description'] ?? null,
                'status' => true
            ]);

            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! La cateogría ha sido registrada correctamente'
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Hubo un error al procesar la solicitud',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function updateAnimal(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'animal.name' => 'required|string|min:3|max:50',
            'animal.description' => 'sometimes|string'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Algunos de los datos proporcionados son incorrectos. Por favor, verifica los campos y vuelve a intentarlo',
                'errors' => $validator->errors()
            ], 422);
        };

        DB::beginTransaction();
        try {
            $animalData = $request->input('animal');
            $animal = AnimalCategoryModel::findOrFail($id);
            $animal->fill($animalData);
            $animal->save();
            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! La cateogría ha sido actualizada correctamente'
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Hubo un error al procesar la solicitud',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function deleteAnimal($id)
    {
        DB::beginTransaction();
        try {
            $animal = AnimalCategoryModel::findOrFail($id);
            $animal->delete();
            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! La cateogría ha sido eliminada correctamente'
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Hubo un error al procesar la solicitud',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
