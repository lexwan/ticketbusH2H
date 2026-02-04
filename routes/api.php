<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);

// Public search routes (no auth required)
Route::get('/search', [SearchController::class, 'searchProducts']);
Route::get('/search/suggestions', [SearchController::class, 'suggestions']);
Route::get('/search/filters', [SearchController::class, 'filterOptions']);
Route::get('/search/popular', [SearchController::class, 'popularTerms']);

// Protected routes - require authentication
Route::middleware('auth:api')->group(function () {
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // User Profile Management
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::post('/profile/avatar', [UserController::class, 'uploadAvatar']);
    Route::delete('/profile/avatar', [UserController::class, 'deleteAvatar']);
    Route::get('/profile/activities', [UserController::class, 'activities']);
    
    // Logout
    Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
    
    // Product routes - all users can view
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    
    // Product management - admin only
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::patch('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
});
