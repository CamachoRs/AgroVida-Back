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
        $validator = Validator::make($request->all(),[
            'email' => 'required|email'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Algunos de los datos proporcionados son incorrectos. Por favor, verifica los campos y vuelve a intentarlo',
                'errors' => $validator->errors()
            ], 422);
        };

        try {
            $email = $request->input('email');
            $user = UserModel::where('email', $email)
                ->where('status', true)
                ->first();
            
            $establishment = EstablishmentModel::where('id', $user->establishmentId)
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
            $payLoad = JWTAuth::parseToken()->getPayload();
            $establishmentId = $payLoad->get('establishment');
            $establishmentData = EstablishmentModel::where('id', $establishmentId)
                ->first();

            $establishment = $request->input('establishment');
            $establishmentData->fill($establishment);
            $establishmentData->save();
            $user = $request->input('user');
            $userData = UserModel::where('email', $user['email'])
                ->where('status', true)
                ->first();

            $userData->fill($user);
            $userData->save();
            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! Ha sido actualizada correctamente la información',
                'email' => $userData->email
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
