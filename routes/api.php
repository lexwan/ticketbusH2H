<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MitraController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // =====================
    // AUTHENTICATION
    // =====================
    Route::prefix('auth')->group(function () {
        // Public
        Route::post('/login', [AuthController::class, 'login']);
        
        // Protected
        Route::middleware('auth:api')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::get('/permissions', [AuthController::class, 'permissions']);
        });
    });

    // =====================
    // PROTECTED ROUTES
    // =====================
    Route::middleware('auth:api')->group(function () {

        // =====================
        // ADMIN ONLY ROUTES
        // =====================
        Route::middleware('role.permission:admin')->group(function () {
            
            // Mitra Management
            Route::prefix('mitra')->group(function () {
                Route::post('/register', [MitraController::class, 'register']);
                Route::get('/', [MitraController::class, 'index']);
                Route::get('/{id}', [MitraController::class, 'show']);
                Route::put('/{id}/fee', [MitraController::class, 'updateFee']);
            });
        });

        // =====================
        // MITRA ONLY ROUTES
        // =====================
        Route::middleware('role.permission:mitra')->group(function () {
            // Transaction endpoints akan ditambahkan nanti
        });
    });

    // =====================
    // CALLBACK (Signature Verification)
    // =====================
    Route::middleware('verify.signature')->prefix('callbacks')->group(function () {
        Route::post('/provider/payment', function () {
            return response()->json(['status' => true, 'message' => 'Payment callback received']);
        });
        Route::post('/provider/ticket', function () {
            return response()->json(['status' => true, 'message' => 'Ticket callback received']);
        });
    });
});