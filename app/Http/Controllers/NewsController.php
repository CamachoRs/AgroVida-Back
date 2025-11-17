<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\NewsModel;
use App\Models\UserModel;
use App\Models\AnimalCategoryModel;
use App\Models\NewsAnimalCategoryModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function index()
    {
        try {
            $payLoad = JWTAuth::parseToken()->authenticate();
            $news = NewsModel::join('newsAnimalCategories', 'news.id', '=', 'newsAnimalCategories.newId')
            ->join('animalCategories as ca', 'ca.id', '=', 'newsAnimalCategories.categoryAnimalId')
            ->join('users as u', 'u.id', '=', 'news.userId')
            ->whereIn('news.establishmentId', [$payLoad->establishmentId, 1])
            ->where('news.status', true)
            ->select('news.id', 'news.title', 'news.description', 'u.nameUser', 'news.updated_at', 'news.image', DB::raw('string_agg(ca.name, \', \') as animals'))
            ->groupBy('news.id', 'news.title', 'news.description', 'u.nameUser', 'news.image')
            ->get();

            if($news->isEmpty()){
                return response()->json([
                    'message' => 'No se encontraron novedades para este establecimiento'
                ], 404);
            };

            return response()->json([
                'message'=>'Novedades recuperados exitosamente',
                'data'=>$news
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
            'new.title' => 'required|string|min:5|max:100',
            'new.description' => 'required|string|min:10',
            'new.categoryAnimal' => 'required|string',
            'new.image' => 'sometimes|image|max:2048'
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
            $new = $request->input('new');
            $categoryAnimalNames = array_map('trim', explode(',', $new['categoryAnimal']));
            $categoryAnimals = AnimalCategoryModel::whereIn('name', $categoryAnimalNames)->get();

            if ($categoryAnimals->count() !== count($categoryAnimalNames)) {
                return response()->json([
                    'message' => 'Uno o más nombres de categorías de animales no son válidos.',
                ], 422);
            }

            if ($request->hasFile('new.image')) {
                $imageName = Str::uuid() . '.' . $request->file('new.image')->getClientOriginalExtension();
                $imagePath = $request->file('new.image')->storeAs('img/news', $imageName, 'public');
            } else {
                $imagePath = 'storage/img/news/AgroVida-register.svg';
            }

            $news = NewsModel::create([
                'establishmentId' => $payLoad->establishmentId,
                'userId' => $payLoad->id,
                'title' => $new['title'],
                'description' => $new['description'],
                'image' => $imagePath,
                'status' => true,
            ]);

            $categoryAnimalIds = $categoryAnimals->pluck('id')->toArray();
            $news->animalCategory()->attach($categoryAnimalIds);
            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! la novedad ha sido registrada correctamente'
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
            'new.title' => 'sometimes|string|min:5|max:100',
            'new.description' => 'sometimes|string|min:10',
            'new.categoryAnimal' => 'sometimes|string',
            'new.image' => 'sometimes|image'
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
            $new = NewsModel::findOrFail($id);

            if($new->userId == 1 && $payLoad->id != 1){
                return response()->json([
                    'message' => 'Lo sentimos, pero no se puede actualizar esta novedad'
                ], 400);
            };

            if($payLoad->role == 'empleado' && $new->userId != $payLoad->id){
                return response()->json([
                    'message' => 'Lo sentimos, pero solo puedes actualizar las novedades que tengan tu usuario'
                ], 403);
            };

            $newData = $request->input('new');
            $new->userId = $payLoad->id;
            $new->fill($newData);
            
            if ($request->hasFile('new.image')) {
                $imageName = Str::uuid() . '.' . $request->file('new.image')->getClientOriginalExtension();
                $imagePath = $request->file('new.image')->storeAs('img/news', $imageName, 'public');
                $new->image = $imagePath;
            }

            $new->save();

            if (isset($newData['categoryAnimal'])) {
                $categoryNames = array_map('trim', explode(',', $newData['categoryAnimal']));
                $categoryAnimals = AnimalCategoryModel::whereIn('name', $categoryNames)->get();

                if ($categoryAnimals->count() !== count($categoryNames)) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Uno o más nombres de categorías de animales no son válidos.',
                    ], 422);
                }

                $categoryAnimalIds = $categoryAnimals->pluck('id')->toArray();
                $new->animalCategory()->sync($categoryAnimalIds);
            };

            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! la novedad ha sido actualizada correctamente'
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
            $payLoad = JWTAuth::parseToken()->authenticate();
            $new = NewsModel::findOrFail($id);

            if($new->userId == 1 && $payLoad->id != 1){
                return response()->json([
                    'message' => 'Lo sentimos, pero no se puede eliminar esta novedad'
                ], 400);
            };

            if($payLoad->role == 'empleado' && $new->userId != $payLoad->id){
                return response()->json([
                    'message' => 'Lo sentimos, pero solo puedes eliminar las novedades que tengan tu usuario'
                ], 403);
            };

            $new->update(['status'=>false]);
            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! La novedad ha sido eliminada correctamente'
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
