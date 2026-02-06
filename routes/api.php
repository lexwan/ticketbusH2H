<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // =====================
    // PUBLIC ROUTES
    // =====================
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::apiResource('posts', PostController::class)
        ->only(['index', 'show']);

    // =====================
    // PROTECTED ROUTES (Bearer Token)
    // =====================
    Route::middleware('auth:api')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        
        Route::get('/user', function (Request $request) {
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $request->user()
            ]);
        });

        // Admin only routes
        Route::middleware('role.permission:admin')->group(function () {
            Route::apiResource('posts', PostController::class)
                ->except(['index', 'show']);
        });
    });

    // =====================
    // CALLBACK ROUTES (Signature Verification)
    // =====================
    Route::middleware('verify.signature')->group(function () {
        Route::post('/callback/payment', function (Request $request) {
            return response()->json([
                'status' => true,
                'message' => 'Callback received'
            ]);
        });
    });

});
