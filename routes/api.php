<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PerformanceRecordController;
use App\Http\Controllers\PerformanceRecordExportController;
use App\Http\Controllers\VideoGeneratorController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/refresh', [AuthController::class, 'refresh']);

Route::middleware('auth:api')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/orders', [OrderController::class, 'store'])->middleware('idempotency');
    Route::get('/performance-records', [PerformanceRecordController::class, 'index']);
    Route::get('/performance-records/export', [PerformanceRecordExportController::class, 'export']);

    // AI Video Studio Routes
    Route::post('/ai/generate-video', [VideoGeneratorController::class, 'generate']);
    Route::get('/ai/video-status/{id}', [VideoGeneratorController::class, 'status']);
    Route::post('/ai/upload-youtube', [VideoGeneratorController::class, 'uploadToYoutube']);
});
