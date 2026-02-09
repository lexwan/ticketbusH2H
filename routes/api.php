<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MitraController;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // auth
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

    // protected
    Route::middleware('auth:api')->group(function () {

        // admin only
        Route::middleware('role.permission:admin')->group(function () {
            
            // Dashboard Admin
            Route::get('/dashboard/admin', [DashboardController::class, 'admin']);
            
            // Mitra Management
            Route::prefix('mitra')->group(function () {
                Route::post('/register', [MitraController::class, 'register']);
                Route::get('/', [MitraController::class, 'index']);
                Route::get('/{id}', [MitraController::class, 'show']);
                Route::put('/{id}/fee', [MitraController::class, 'updateFee']);
            });
        });

        // mitra only
        Route::middleware('role.permission:mitra')->group(function () {
            
            // Dashboard Mitra
            Route::get('/dashboard/mitra', [DashboardController::class, 'mitra']);
            
            // Transaction endpoints akan ditambahkan nanti
        });
    });

    // Callback signature verif
    Route::middleware('verify.signature')->prefix('callbacks')->group(function () {
        Route::post('/provider/payment', function () {
            return response()->json(['status' => true, 'message' => 'Payment callback received']);
        });
        Route::post('/provider/ticket', function () {
            return response()->json(['status' => true, 'message' => 'Ticket callback received']);
        });
    });
});