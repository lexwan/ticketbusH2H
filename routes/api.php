<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
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

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

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

    //categories
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    
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
    
    // Product Images - admin only
    Route::post('/products/{product}/images', [ProductController::class, 'uploadImages']);
    Route::delete('/products/{product}/images/{image}', [ProductController::class, 'deleteImage']);
    Route::put('/products/{product}/images/{image}/primary', [ProductController::class, 'setPrimaryImage']);
    
    // Orders
    Route::apiResource('orders', App\Http\Controllers\Api\OrderController::class);
    
    // Cart
    Route::get('/cart', [App\Http\Controllers\Api\CartController::class, 'index']);
    Route::post('/cart', [App\Http\Controllers\Api\CartController::class, 'store']);
    Route::put('/cart/{cart}', [App\Http\Controllers\Api\CartController::class, 'update']);
    Route::delete('/cart/{cart}', [App\Http\Controllers\Api\CartController::class, 'destroy']);
    Route::delete('/cart', [App\Http\Controllers\Api\CartController::class, 'clear']);
    Route::post('/cart/checkout', [App\Http\Controllers\Api\CartController::class, 'checkout']);
    
    // Payments
    Route::post('/payments', [App\Http\Controllers\Api\PaymentController::class, 'store']);
    Route::get('/payments/{payment}', [App\Http\Controllers\Api\PaymentController::class, 'show']);
    Route::post('/payments/{payment}/confirm', [App\Http\Controllers\Api\PaymentController::class, 'confirm']);
    Route::get('/payments/{payment}/status', [App\Http\Controllers\Api\PaymentController::class, 'status']);
});
