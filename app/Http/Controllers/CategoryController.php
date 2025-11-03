<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategoryModel;
use App\Models\AnimalCategoryModel;

class CategoryController extends Controller
{
    public function indexProduct()
    {
        $categories = ProductCategoryModel::where('status', true)
            ->select('id', 'name')
            ->get();
        return response()->json([
            'message'=>'Categorías recuperadas exitosamente',
            'data'=>$categories
        ], 200);
    }

    public function indexAnimal()
    {
        $categories = AnimalCategoryModel::where('status', true)
            ->select('id', 'name')
            ->get();
        return response()->json([
            'message'=>'Categorías recuperadas exitosamente',
            'data'=>$categories
        ], 200);
    }
}
