<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\MedicalReviewModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class MedicalReviewController extends Controller
{
    public function show($id)
    {
        try {
            $payLoad = JWTAuth::parseToken()->authenticate();
            $medicalReviews = MedicalReviewModel::where('animalId', $id)
                ->get();
            
                if($medicalReviews->isEmpty()){
                    return response()->json([
                        'message' => 'No se encontró información médica para este animal'
                    ], 404);
                };

                return response()->json([
                    'message'=>'Información médica recuperada exitosamente',
                    'data'=>$medicalReviews
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
            'medical.animalId' => 'required|exists:animals,id',
            'medical.reviewType' => 'required|in:Chequeo general,Tratamiento,Vacunación,Examen,Emergencia',
            'medical.observations' => 'sometimes|string|max:255',
            'medical.reviewerName' => 'required|string|min:3|max:50',
            'medical.medicationName' => 'sometimes|string|max:100',
            'medical.dose' => 'sometimes|string|nullable',
            'medical.administrationRoute' => 'sometimes|in:Oral,Inyectable,Tópico,Intravenoso',
            'medical.file' => 'sometimes|file|max:2048',
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Algunos de los datos proporcionados son incorrectos. Por favor, verifica los campos y vuelve a intentarlo',
                'errors' => $validator->errors()
            ], 422);
        };

        DB::beginTransaction();
        try {
            $medical = $request->input('medical');
            
            if($request->hasFile('medical.file') && $request->file('medical.file')->isValid()){
                $fileName = Str::uuid() . '.' . $request->file('medical.file')->getClientOriginalExtension();
                $filePath = $request->file('medical.file')->storeAs('medical', $fileName, 'public');
            } else {
                $filePath = null;
            };

            MedicalReviewModel::create([
                'animalId' => $medical['animalId'],
                'reviewType' => $medical['reviewType'],
                'observations' => $medical['observations'] ?? null,
                'reviewerName' => $medical['reviewerName'],
                'medicationName' => $medical['medicationName'] ?? null,
                'dose' => $medical['dose'] ?? null,
                'administrationRoute' => $medical['administrationRoute'] ?? null,
                'file' => $filePath
            ]);

            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! la información se ha sido registrado correctamente'
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
            'medical.animalId' => 'sometimes|exists:animals,id',
            'medical.reviewType' => 'sometimes|in:Chequeo general,Tratamiento,Vacunación,Examen,Emergencia',
            'medical.observations' => 'sometimes|string|max:255',
            'medical.reviewerName' => 'sometimes|string|max:255',
            'medical.medicationName' => 'sometimes|string|max:255',
            'medical.dose' => 'sometimes|string|nullable',
            'medical.administrationRoute' => 'sometimes|in:Oral,Inyectable,Tópico,Intravenoso',
            'medical.file' => 'sometimes|string|max:255',
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Algunos de los datos proporcionados son incorrectos. Por favor, verifica los campos y vuelve a intentarlo',
                'errors' => $validator->errors()
            ], 422);
        };

        DB::beginTransaction();
        try {
            $medicalData = $request->input('medical');
            $medical = MedicalReviewModel::findOrFail($id);
            $medical->fill($medicalData);

            if($request->hasFile('medical.file')){
                $fileName = Str::uuid() . '.' . $request->file('medical.file')->getClientOriginalExtension();
                $filePath = $request->file('medical.file')->storeAs('img/medical', $fileName, 'public');
                $medical->file = $filePath;
            };

            $medical->save();
            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! la información ha sido actualizado correctamente'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Hubo un error al procesar la solicitud',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function downloadFile($id)
    {
        try {
            $medicalReview = MedicalReviewModel::findOrFail($id);
            
            if (!$medicalReview->file || !Storage::disk('public')->exists($medicalReview->file)) {
                return response()->json([
                    'message' => 'Archivo no encontrado.',
                    'prueba' => $medicalReview
                ], 404);
            }

            $filePath = storage_path('app/public/' . $medicalReview->file);
            $fileName = basename($medicalReview->file);
            return response()->download($filePath, $fileName);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Hubo un error al procesar la solicitud',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
