<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CategoryController;

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
        Route::post('/update', [AuthController::class, 'updated']);
    });

     Route::middleware('auth:sanctum')->prefix('product')->group(function () {
        Route::post('/store', [ProductController::class, 'store']);
        Route::get('/list', [ProductController::class, 'index']);
        Route::get('/detail/{product}', [ProductController::class, 'show']);
        Route::post('/order/buy', [OrderController::class, 'buy']);
        Route::post('/payment/callback', [OrderController::class, 'callback']);
    });

    Route::middleware('auth:sanctum')->prefix('category')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index']);
    });


});
