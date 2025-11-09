<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\TaskModel;
use App\Models\InventoryModel;
use App\Models\AnimalModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TasksController extends Controller
{
    public function index()
    {
        try {
            $payLoad = JWTAuth::parseToken()->authenticate();
            $tasks = TaskModel::join('users as u', 'u.id', '=', 'tasks.userId')
                ->leftJoin('inventories as i', 'i.id', '=', 'tasks.inventoryId')
                ->leftJoin('animalTask as at', 'at.taskId', '=', 'tasks.id')
                ->leftJoin('animals as a', 'a.id', '=', 'at.animalId')
                ->where('tasks.userId', $payLoad->id)
                ->select('tasks.*', 'u.nameUser as userName', 'i.nameItem as inventoryName', DB::raw('STRING_AGG(a.name::text, \', \') as animalNamesList'), DB::raw('STRING_AGG(a.id::text, \', \') as animalIdsList'))
                ->groupBy('tasks.id', 'u.nameUser', 'i.nameItem')
                ->get();

            return response()->json([
                'message' => 'Tareas recuperadas exitosamente',
                'data' => $tasks
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
            'task.name' => 'required|string|min:5|max:100',
            'task.urgency' => 'required|in:Alta,Media,Baja',
            'task.deadline' => 'required|date',
            'task.description' => 'required|string|min:10',
            'task.userId' => 'required|exists:users,id',
            'task.inventoryId' => 'sometimes|exists:inventories,id',
            'task.itemQuantity' => 'sometimes|integer|min:1',
            'task.animalIds' => 'sometimes|array|exists:animals,id',
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Algunos de los datos proporcionados son incorrectos. Por favor, verifica los campos y vuelve a intentarlo',
                'errors' => $validator->errors()
            ], 422);
        };

        DB::beginTransaction();
        try {
            $taskData = $request->input('task');
            
            if(!empty($taskData['animalIds'])){
                $animals = AnimalModel::whereIn('id', $taskData['animalIds'])->get();

                if ($animals->count() !== count($taskData['animalIds'])) {
                    return response()->json([
                        'message' => 'Uno o más animales no existen o no son válidos.',
                    ], 422);
                }
            };

            if (!empty($taskData['inventoryId']) && !empty($taskData['itemQuantity'])) {
                $inventory = InventoryModel::find($taskData['inventoryId']);

                if (!$inventory || $inventory->quantity < $taskData['itemQuantity']) {
                    return response()->json([
                        'message' => 'Inventario insuficiente para completar la tarea'
                    ], 400);
                }

                $inventory->quantity -= $taskData['itemQuantity'];
                $inventory->save();
            }

            $payLoad = JWTAuth::parseToken()->authenticate();
            $task = TaskModel::create([
                'establishmentId' => $payLoad->establishmentId,
                'name' => $taskData['name'],
                'urgency' => $taskData['urgency'],
                'deadline' => $taskData['deadline'],
                'description' => $taskData['description'],
                'userId' => $taskData['userId'],
                'inventoryId' => $taskData['inventoryId'] ?? null,
                'itemQuantity' => $taskData['itemQuantity'] ?? null,
            ]);

            if (!empty($taskData['animalIds'])) {
                $task->animals()->attach($taskData['animalIds']);
            }

            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! la tarea ha sido registrada correctamente'
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
            'task.name' => 'sometimes|string|min:5|max:100',
            'task.urgency' => 'sometimes|in:Alta,Media,Baja',
            'task.deadline' => 'sometimes|date',
            'task.description' => 'sometimes|string|min:10',
            'task.userId' => 'sometimes|exists:users,id',
            'task.inventoryId' => 'sometimes|exists:inventories,id',
            'task.itemQuantity' => 'sometimes|integer|min:1',
            'task.animalIds' => 'sometimes|array|exists:animals,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Algunos de los datos proporcionados son incorrectos.',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $taskData = $request->input('task');
            $task = TaskModel::findOrFail($id);

            if (isset($taskData['animalIds'])) {
                $animals = AnimalModel::whereIn('id', $taskData['animalIds'])->get();

                if ($animals->count() !== count($taskData['animalIds'])) {
                    return response()->json([
                        'message' => 'Uno o más animales no existen o no son válidos.',
                    ], 422);
                }

                $task->animals()->sync($taskData['animalIds']);
            }

            if (isset($taskData['inventoryId'], $taskData['itemQuantity'])) {
                $newInventory = InventoryModel::find($taskData['inventoryId']);

                if (!$newInventory || $newInventory->quantity < $taskData['itemQuantity']) {
                    return response()->json([
                        'message' => 'Inventario insuficiente para completar la tarea.'
                    ], 400);
                }

                if ($task->inventoryId && $task->itemQuantity) {
                    $oldInventory = InventoryModel::find($task->inventoryId);
                    if ($oldInventory) {
                        $oldInventory->quantity += $task->itemQuantity;
                        $oldInventory->save();
                    }
                }

                $newInventory->quantity -= $taskData['itemQuantity'];
                $newInventory->save();
            }

            $task->fill($taskData);
            $task->save();
            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! la tarea ha sido actualizada correctamente'
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
            $task = TaskModel::findOrFail($id);

            if ($task->resolvedAt && $task->inventoryId && $task->itemQuantity) {
                $inventory = InventoryModel::find($task->inventoryId);
                if ($inventory) {
                    $inventory->quantity += $task->itemQuantity;
                    $inventory->save();
                }
            }

            $task->animals()->detach();
            $task->delete();
            DB::commit();
            return response()->json([
                'message' => '¡Todo listo! La tarea ha sido eliminada correctamente'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Hubo un error al procesar la solicitud',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function resolve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'task.descriptionR' => 'sometimes|string|min:10',
            'task.imageR' => 'sometimes|string',
            'task.resolvedAt' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos para completar la tarea.',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $taskData = $request->input('task');
            $task = TaskModel::findOrFail($id);
            $task->fill($taskData);

            if ($request->hasFile('task.imageR')) {
                $imageName = Str::uuid() . '.' . $request->file('task.imageR')->getClientOriginalExtension();
                $imagePath = $request->file('task.imageR')->storeAs('img/tasks', $imageName, 'public');
                $task->imageR = $imagePath;
            }

            $task->save();
            DB::commit();
            return response()->json([
                'message' => 'Tarea marcada como completada.'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al completar la tarea.',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}