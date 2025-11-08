<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PrivateController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmployController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\MedicalReviewController;
use App\Http\Middleware\RoleMiddleware;

Route::prefix('')->group(function () {
    Route::post('/register', [PublicController::class, 'store']); 
    Route::post('/login/{id?}', [PublicController::class, 'login']); 
    Route::post('/recover', [PublicController::class, 'recover']); 
});

Route::group([
    'middleware' => [
        'api',
        RoleMiddleware::class
    ]
], function ($router) {
    Route::get('/logout', [PrivateController::class, 'logout']);
    Route::get('/categoryProduct', [CategoryController::class, 'indexProduct']);
    Route::get('/categoryAnimal', [CategoryController::class, 'indexAnimal']);
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::post('/inventory', [InventoryController::class, 'store']);
    Route::put('/inventory/{id}', [InventoryController::class, 'update']);
    Route::delete('/inventory/{id}', [InventoryController::class, 'delete']);
    Route::get('/profile', [UserController::class, 'show']);
    Route::put('/profile', [UserController::class, 'update']);
    Route::get('/employees', [EmployController::class, 'index']);
    Route::post('/employ', [EmployController::class, 'store']);
    Route::put('/employ/{id}', [EmployController::class, 'update']);
    Route::delete('/employ/{id}', [EmployController::class, 'delete']);
    Route::get('/news', [NewsController::class, 'index']);
    Route::post('/news', [NewsController::class, 'store']);
    Route::post('/news/{id}', [NewsController::class, 'update']);
    Route::delete('/news/{id}', [NewsController::class, 'delete']);
    Route::get('/animals', [AnimalController::class, 'index']);
    Route::post('/animals', [AnimalController::class, 'store']);
    Route::post('/animals/{id}', [AnimalController::class, 'update']);
    Route::delete('/animals/{id}', [AnimalController::class, 'delete']);
    
    Route::get('/medical/{id}', [MedicalReviewController::class, 'show']);
    Route::post('/medical', [MedicalReviewController::class, 'store']);
    Route::post('/medical/{id}', [MedicalReviewController::class, 'update']);
    Route::get('medical-reviews/{id}/download', [MedicalReviewController::class, 'downloadFile']);
    Route::post('/refresh', [PrivateController::class, 'refresh']);
});