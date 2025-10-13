<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Controller;

Route::prefix('public')->group(function () {
    Route::post('/register', [PublicController::class, 'store']); 
    Route::post('/login/{id?}', [PublicController::class, 'login']); 
    Route::post('/recover', [PublicController::class, 'recover']); 
});

Route::post('/logout', [PublicController::class, 'logout']);
Route::post('/refresh', [Controller::class, 'refresh']);
// Route::middleware('auth:api')->get('user', [AuthController::class, 'me']);
