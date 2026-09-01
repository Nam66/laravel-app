<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VideoGeneratorController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // AI Video Studio Routes
    Route::post('/ai/generate-video', [VideoGeneratorController::class, 'generate']);
    Route::get('/ai/video-status/{id}', [VideoGeneratorController::class, 'status']);
    Route::post('/ai/upload-youtube', [VideoGeneratorController::class, 'uploadToYoutube']);
});
