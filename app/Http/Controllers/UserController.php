<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use App\Models\UserModel;
use App\Models\EstablishmentModel;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function show(Request $request)
    {
        try {
            $payLoad = JWTAuth::parseToken()->authenticate();
            $email = $request->input('user.email');
            $user = UserModel::where('id', $payLoad->id)
                ->where('status', true)
                ->select('nameUser', 'email', 'phoneNumber', 'role', 'created_at', 'establishmentId')
                ->first();
            
            $establishment = EstablishmentModel::where('id', $user->establishmentId)
                ->select('nameEstate', 'sidewalk', 'municipality')
                ->first();
            
            return response()->json([
                'message' => '¡Todo listo! El usuario se ha obtenido correctamente',
                'user' => $user,
                'establishment' => $establishment
            ], 200);   
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Hubo un error al procesar la solicitud',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user.nameUser' => 'sometimes|string|max:50|min:3',
            'user.email' => 'sometimes|email',
            'user.password' => 'sometimes|string|min:8',
            'user.phoneNumber' => 'sometimes|string|max:10|min:10',
            'establishment.nameEstate' => 'sometimes|string|max:50|min:3',
            'establishment.sidewalk' => 'sometimes|string|max:50|min:3',
            'establishment.municipality' => 'sometimes'
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
            $establishmentData = EstablishmentModel::where('id', $payLoad->establishmentId)
                ->first();

            $establishment = $request->input('establishment');
            $establishmentData->fill($establishment);
            $establishmentData->save();
            $user = $request->input('user');
            $userData = UserModel::where('id', $payLoad->id)
                ->where('status', true)
                ->first();

            $userData->fill($user);
            $userData->save();
            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! Ha sido actualizada correctamente la información'
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
