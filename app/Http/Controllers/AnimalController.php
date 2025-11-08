<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\AnimalModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnimalController extends Controller
{
    public function index()
    {
        try {
            $animals = AnimalModel::join('animalCategories', 'animals.categoryId', '=', 'animalCategories.id')
                ->where('animals.status', true)
                ->select('animals.id', 'animals.name', 'animals.categoryId', 'animalCategories.name as categoryName', 'animals.sex', 'animals.healthStatus', 'animals.ageRange', 'animals.weight', 'animals.observations', 'animals.image', 'animals.created_at')
                ->get();

            if($animals->isEmpty()){
                return response()->json([
                    'message' => 'No se encontraron animales para este establecimiento'
                ], 404);
            };

            return response()->json([
                'message'=>'Animales recuperados exitosamente',
                'data'=>$animals
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
            'animal.categoryId' => 'required|exists:animalCategories,id',
            'animal.name' => 'required|string|min:3|max:50',
            'animal.sex' => 'required|in:Macho,Hembra',
            'animal.healthStatus' => 'required|in:Sano,En tratamiento,En observación,Crónico,Grave',
            'animal.ageRange' => 'required|in:Cría,Juvenil,Adulto,Maduro,Geriátrico,Desconocido',
            'animal.weight' => 'required|numeric|min:0',
            'animal.observations' => 'sometimes|string|max:255',
            'animal.image' => 'sometimes|image|max:2048'
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
            $animal = $request->input('animal');

            if($request->hasFile('animal.image')){
                $imageName = Str::uuid() . '.' . $request->file('animal.image')->getClientOriginalExtension();
                $imagePath = $request->file('animal.image')->storeAs('img/animals', $imageName, 'public');
            } else {
                $imagePath = 'storage/img/animals/AgroVida-register.svg';
            };

            AnimalModel::create([
                'establishmentId' => $payLoad->establishmentId,
                'categoryId' => $animal['categoryId'],
                'name' => $animal['name'],
                'sex' => $animal['sex'],
                'healthStatus' => $animal['healthStatus'],
                'ageRange' => $animal['ageRange'],
                'weight' => $animal['weight'],
                'observations' => $animal['observations'] ?? null,
                'image' => $imagePath,
                'status' => true
            ]);

            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! el animal ha sido registrado correctamente'
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
            'animal.categoryId' => 'sometimes|exists:animalCategories,id',
            'animal.name' => 'sometimes|string|min:3|max:50',
            'animal.sex' => 'sometimes|in:Macho,Hembra',
            'animal.healthStatus' => 'sometimes|in:Sano,En tratamiento,En observación,Crónico,Grave',
            'animal.ageRange' => 'sometimes|in:Cría,Juvenil,Adulto,Maduro,Geriátrico,Desconocido',
            'animal.weight' => 'sometimes|numeric|min:0',
            'animal.observations' => 'sometimes|string|max:255',
            'animal.image' => 'sometimes|image|max:2048'
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
            $animal = AnimalModel::findOrFail($id);
            $animal->fill($animalData);

            if($request->hasFile('animal.image')){
                $imageName = Str::uuid() . '.' . $request->file('animal.image')->getClientOriginalExtension();
                $imagePath = $request->file('animal.image')->storeAs('img/animals', $imageName, 'public');
                $animal->image = $imagePath;
            };

            $animal->save();
            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! el animal ha sido actualizado correctamente'
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
            $animal = AnimalModel::findOrFail($id);
            $animal->update(['status' => false]);
            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! el animal ha sido eliminado correctamente'
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
