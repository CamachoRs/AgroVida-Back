<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\CategoryModel;

class CategoryController extends Controller
{
    public function index()
    {
        $payLoad = JWTAuth::parseToken()->getPayload();
        $categories = CategoryModel::where('status', true)->get();
        return response()->json([
            'message'=>'Categorías recuperadas exitosamente',
            'data'=>$categories
        ]);
    }
}
