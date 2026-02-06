<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PartnerController;
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
            
            // Partner Management
            Route::prefix('partners')->group(function () {
                Route::post('/register', [PartnerController::class, 'register']);
                Route::get('/', [PartnerController::class, 'index']);
                Route::get('/{id}', [PartnerController::class, 'show']);
                Route::put('/{id}/fee', [PartnerController::class, 'updateFee']);
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