<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PrivateController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AuthController;


Route::prefix('')->group(function () {
    Route::post('/register', [PublicController::class, 'store']); 
    Route::post('/login/{id?}', [PublicController::class, 'login']); 
    Route::post('/recover', [PublicController::class, 'recover']); 
});

Route::group([
    'middleware' => 'api'
], function ($router) {
    Route::get('/inventory', [InventoryController::class, 'index']);


    Route::post('/logout', [PrivateController::class, 'logout']);
    Route::post('/refresh', [PrivateController::class, 'refresh']);
});