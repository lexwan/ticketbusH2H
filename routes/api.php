<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MitraController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TopupController;
use App\Http\Controllers\Api\BalanceController;
use App\Http\Controllers\Api\FeeLedgerController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\CallbackController;
use App\Http\Controllers\Api\ReportController;
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

        // Both admin & mitra
        Route::middleware('role.permission:admin,mitra')->group(function () {
            Route::get('/balance', [BalanceController::class, 'index']);
            Route::get('/balances/histories', [BalanceController::class, 'histories']);
            Route::get('/fee/ledgers', [FeeLedgerController::class, 'index']);
        });

        // admin only
        Route::middleware('role.permission:admin')->group(function () {
            
            // Dashboard Admin
            Route::get('/dashboard/admin', [DashboardController::class, 'admin']);

            // User Management
            Route::prefix('users')->group(function () {
                Route::get('/', [UserController::class, 'index']);
                Route::post('/', [UserController::class, 'store']);
                Route::get('/{id}', [UserController::class, 'show']);
                Route::put('/{id}', [UserController::class, 'update']);
                Route::delete('/{id}', [UserController::class, 'destroy']);
            });

            // Role & Permission Management
            Route::prefix('roles')->group(function () {
                Route::get('/', [RoleController::class, 'index']);
                Route::post('/', [RoleController::class, 'store']);
                Route::get('/{id}', [RoleController::class, 'show']);
                Route::put('/{id}', [RoleController::class, 'update']);
                Route::post('/{id}/permissions', [RoleController::class, 'assignPermissions']);
            });
            Route::get('/permissions', [RoleController::class, 'permissions']);
            
            // Mitra Management
            Route::prefix('mitra')->group(function () {
                Route::post('/register', [MitraController::class, 'register']);
                Route::get('/', [MitraController::class, 'index']);
                Route::get('/{id}', [MitraController::class, 'show']);
                Route::post('/{id}/approve', [MitraController::class, 'approve']);
                Route::post('/{id}/reject', [MitraController::class, 'reject']);
                Route::put('/{id}/fee', [MitraController::class, 'updateFee']);
            });

            // Topup Management (Admin only)
            Route::prefix('topups')->group(function () {
                Route::post('/{id}/approve', [TopupController::class, 'approve']);
                Route::post('/{id}/reject', [TopupController::class, 'reject']);
            });

            Route::prefix('reports')->group(function () {
                Route::get('/transactions', [ReportController::class, 'transactions']);
                Route::get('/topups', [ReportController::class, 'topups']);
                Route::get('/fees', [ReportController::class, 'fees']);
                Route::get('/balances', [ReportController::class, 'balances']);
            });
        });

        // mitra only
        Route::middleware('role.permission:mitra')->group(function () {
            
            // Dashboard Mitra
            Route::get('/dashboard/mitra', [DashboardController::class, 'mitra']);

            // Topup Management (Mitra only)
            Route::prefix('topups')->group(function () {
                Route::post('/', [TopupController::class, 'store']);
            });
            
            // Transaction Management (Mitra only)
            Route::prefix('transactions')->group(function () {
                Route::post('/search', [TransactionController::class, 'search']);
                Route::post('/seat-map', [TransactionController::class, 'seatMap']);
                Route::post('/book', [TransactionController::class, 'book']);
                Route::post('/pay', [TransactionController::class, 'pay']);
                Route::post('/{trx_code}/issue', [TransactionController::class, 'issue']);
                Route::post('/{trx_code}/cancel', [TransactionController::class, 'cancel']);
            });
        });

        // Topup Management (admin & mitra)
        Route::middleware('role.permission:admin,mitra')->prefix('topups')->group(function () {
            Route::get('/', [TopupController::class, 'index']);
            Route::get('/{id}', [TopupController::class, 'show']);
        });

        // Balance & Ledger (admin & mitra)
        Route::middleware('role.permission:admin,mitra')->group(function () {
            Route::get('/balance', [BalanceController::class, 'index']);
            Route::get('/balance/histories', [BalanceController::class, 'histories']);
            Route::get('/fee/ledgers', [FeeLedgerController::class, 'index']);
        });

        // Both admin & mitra (view transactions)
        Route::middleware('role.permission:admin,mitra')->group(function () {
            Route::get('/transactions/{trx_code}', [TransactionController::class, 'show']);
        });
    });

    // Callback signature verif
    Route::middleware('verify.signature')->prefix('callbacks')->group(function () {
        Route::post('/provider/payment', [CallbackController::class, 'payment']);
        Route::post('/provider/ticket', [CallbackController::class, 'ticket']);
    });
});