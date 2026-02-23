<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/resend-code', [AuthController::class, 'resendCode']);
    });

     Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
        Route::post('/update/avatar', [ProfileController::class, 'updateAvatar']);
        Route::get('/get/profile', [ProfileController::class, 'getProfile']);
        Route::post('/update', [AuthController::class, 'update']);
    });

});
