<?php

namespace App\Http\Controllers;

use App\Models\UserModel;
use App\Models\EstablishmentModel;
use App\Mail\WelcomeEmail;
use App\Mail\RecoverEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PublicController extends Controller
{
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user.nameUser' => 'required|string|max:50|min:3',
            'user.email' => 'required|email',
            'user.password' => 'required|string|min:8',
            'user.phoneNumber' => 'required|string|max:10|min:10',
            'establishment.nameEstate' => 'required|string|max:50|min:3',
            'establishment.sidewalk' => 'required|string|max:50|min:3',
            'establishment.municipality' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Algunos de los datos proporcionados son incorrectos. Por favor, verifica los campos y vuelve a intentarlo',
                'errors' => $validator->errors()
            ], 422);
        };

        DB::beginTransaction();
        try {
            $userData = $request->input('user');
            $establishmentData = $request->input('establishment');
            $userExists = UserModel::where('email', $userData['email'])->first();
            if($userExists){
                if(!$userExists->status){
                    $establishmentExists = EstablishmentModel::where('userId', $userExists->id)->first();
                    $establishmentExists->update([
                        'nameEstate' => $establishmentData['nameEstate'],
                        'sidewalk' => $establishmentData['sidewalk'],
                        'municipality' => $establishmentData['municipality']
                    ]);

                    $userExists->update([
                        'establishmentId' => $establishmentExists->id,
                        'nameUser' => $userData['nameUser'],
                        'email' => $userData['email'],
                        'password' => $userData['password'],
                        'phoneNumber' => $userData['phoneNumber']
                    ]);
                    
                    Mail::to($userExists['email'])->queue(new WelcomeEmail($userExists, Crypt::encryptString($userExists->phoneNumber)));
                    DB::commit();
                    return response()->json([
                        'message' => '¡¡Todo listo! Revisa tu correo electrónico para activar tu cuenta y acceder a la finca',
                        'prueba'=>$phoneNumber
                    ], 201);
                } else {
                    return response()->json([
                        'message' => 'Este usuario ya está activado y registrado.'
                    ], 400);
                };
            } else {
                $establishment = EstablishmentModel::create([
                    'nameEstate' => $establishmentData['nameEstate'],
                    'sidewalk' => $establishmentData['sidewalk'],
                    'municipality' => $establishmentData['municipality']
                ]);

                $user = UserModel::create([
                    'establishmentId' => $establishment->id,
                    'nameUser' => $userData['nameUser'],
                    'email' => $userData['email'],
                    'password' => $userData['password'],
                    'phoneNumber' => $userData['phoneNumber'],
                    'status' => false,
                    'role' => 'dueño'
                ]);

                Mail::to($user['email'])->queue(new WelcomeEmail($user, Crypt::encryptString($user->phoneNumber)));
                DB::commit();
                return response()->json([
                    'message' => '¡Todo listo! Revisa tu correo electrónico para activar tu cuenta y acceder a la finca.'
                ], 201);
            };
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Hubo un error al procesar la solicitud',
                'error' => $th->getMessage()
            ], 500);
        };
    }

    public function login(Request $request, $id = null)
    {
        $validator = Validator::make($request->all(), [
            'user.email' => 'required|email',
            'user.password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Algunos de los datos proporcionados son incorrectos. Por favor, verifica los campos y vuelve a intentarlo',
                'errors' => $validator->errors()
            ], 422);
        };

        try {
            $user = UserModel::where('email', $request->input('user.email'))->first();

            if (!$user) {
                return response()->json([
                    'message' => 'Email y/o contraseña incorrectos'
                ], 401);
            };

            if($id !== null){
                $phoneNumber = Crypt::decryptString($id);
                if($user->phoneNumber == $phoneNumber){
                    $user->update([
                        'status' => true
                    ]);                
                };
            };       

            if (!\Hash::check($request->input('user.password'), $user->password)) {
                return response()->json([
                    'message' => 'Email y/o contraseña incorrectos'
                ], 401);
            };

            if ($user->status != true) {
                return response()->json([
                    'message' => 'Tu cuenta aún no ha sido activada. Por favor, revisa tu correo electrónico (incluyendo la carpeta de spam) para completar la verificación'
                ], 403);
            };

            $token = JWTAuth::fromUser($user);
            return response()->json([
                'message' => 'Inicio de sesión exitoso',
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Hubo un error al procesar la solicitud',
                'error' => $th->getMessage()
            ], 500);
        };
    }

    public function recover(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user.email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Algunos de los datos proporcionados son incorrectos. Por favor, verifica los campos y vuelve a intentarlo',
                'errors' => $validator->errors()
            ], 422);
        };


        try {
            $user = UserModel::where('email', $request->input('user.email'))->first();
            if (!$user) {
                return response()->json([
                    'message' => 'No encontramos el correo. Verifica si lo escribiste correctamente o regístrate'
                ], 401);
            };

            if ($user->status != true) {
                return response()->json([
                    'message' => 'Tu cuenta aún no ha sido activada. Por favor, revisa tu correo electrónico (incluyendo la carpeta de spam) para completar la verificación'
                ], 403);
            };

            $newPasswod = $this->generatePassword();
            $user->update([
                'password' => $newPasswod
            ]);

            Mail::to($user['email'])->queue(new RecoverEmail($user, $newPasswod));
            return response()->json([
                'message' => 'Hemos procesado tu solicitud. Revisa tu correo, incluyendo la carpeta de spam'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Hubo un error al procesar la solicitud',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    private function generatePassword()
    {
        $letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $specialChars = '!@#$%^&*(),.?":{}|<>';

        $password = '';
        $password .= $letters[rand(0, strlen($letters) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $specialChars[rand(0, strlen($specialChars) - 1)];

        $allChars = $letters . $numbers . $specialChars;
        for ($i = 3; $i < 9; $i++) {
            $password .= $allChars[rand(0, strlen($allChars) - 1)];
        }

        $password = str_shuffle($password);
        return $password;
    }
}
