<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PrivateController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
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
    Route::get('/category', [CategoryController::class, 'index']);
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::post('/inventory', [InventoryController::class, 'store']);
    Route::put('/inventory/{id}', [InventoryController::class, 'update']);
    Route::delete('/inventory/{id}', [InventoryController::class, 'delete']);
    Route::post('/profile', [UserController::class, 'show']);
    Route::put('/profile', [UserController::class, 'update']);

    Route::post('/refresh', [PrivateController::class, 'refresh']);
});