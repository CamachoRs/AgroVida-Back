<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\UserModel;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployController extends Controller
{
    public function index()
    {
        try {
            $payLoad = JWTAuth::parseToken()->getPayload();
            $establishmentId = $payLoad->get('establishment');
            $users = UserModel::where('establishmentId', $establishmentId)
                ->where('role', "empleado")
                ->get();
            
            if($users->isEmpty()){
                return response()->json([
                    'message' => 'No se encontraron usuarios para este establecimiento'
                ], 404);
            };

            return response()->json([
                'message'=>'Usuarios recuperados exitosamente',
                'data'=>$users
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
            'user.nameUser' => 'required|string|max:50|min:3',
            'user.email' => 'required|email',
            'user.password' => 'required|string|min:8',
            'user.phoneNumber' => 'required|string|max:10|min:10',
            'user.status' => 'required|boolean',
            'user.role' => 'required|in:dueño,empleado,encargado'
        ]);
        
        if($validator->fails()){
            return response()->json([
                'message' => 'Algunos de los datos proporcionados son incorrectos. Por favor, verifica los campos y vuelve a intentarlo',
                'errors' => $validator->errors()
            ], 422);
        };

        DB::beginTransaction();
        try {
            $userData = $request->input('user');
            $payLoad = JWTAuth::parseToken()->getPayload();
            $establishmentId = $payLoad->get('establishment');
            UserModel::create([
                'establishmentId' => $establishmentId,
                'nameUser' => $userData['nameUser'],
                'email' => $userData['email'],
                'password' => $userData['password'],
                'phoneNumber' => $userData['phoneNumber'],
                'status' => $userData['status'],
                'role' => $userData['role']
            ]);
            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! Revisa tu correo electrónico para activar tu cuenta y acceder a la finca'
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
            'user.nameUser' => 'sometimes|string|max:50|min:3',
            'user.email' => 'sometimes|email',
            'user.password' => 'sometimes|string|min:8',
            'user.phoneNumber' => 'sometimes|string|max:10|min:10',
            'user.status' => 'sometimes|boolean',
            'user.role' => 'sometimes|in:dueño,empleado,encargado'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Algunos de los datos proporcionados son incorrectos. Por favor, verifica los campos y vuelve a intentarlo',
                'errors' => $validator->errors()
            ], 422);
        };
        
        DB::beginTransaction();
        try {
            $userData = $request->input('user');
            $user = UserModel::where('id', $id)
                ->first();

            $user->fill($userData);
            $user->save();
            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! Ha sido actualizada correctamente la información'
            ], 200);
        }catch(\Throwable $th){
            DB::rollBack();
            return response()->json([
                'message' => 'Hubo un error al procesar la solicitud',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function delete($id){
        DB::beginTransaction();
        try {
            $user = UserModel::where('id',$id);
            $user->delete();
            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! El usuario ha sido eliminado correctamente'
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
