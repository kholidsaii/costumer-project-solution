<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Middleware\CheckRole;

// 1. PUBLIC ROUTES
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/products', [ProductController::class, 'index']); 
Route::get('/products/{slug}', [ProductController::class, 'show']); 

// 2. PROTECTED ROUTES
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) { return $request->user(); });

    // 3. CUSTOMER ROUTES
    Route::middleware(CheckRole::class.':customer')->prefix('customer')->group(function () {
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/reviews', [ReviewController::class, 'store']); // Rute Review Baru
    });

    // 4. ADMINISTRATOR ROUTES
    Route::middleware(CheckRole::class.':admin')->prefix('admin')->group(function () {
        Route::get('/products', [ProductController::class, 'adminIndex']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    });
});