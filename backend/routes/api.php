<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\TierController;
use App\Http\Middleware\CheckRole;

// ==========================================
// 1. PUBLIC ROUTES
// ==========================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::get('/products', [ProductController::class, 'index']); 
Route::get('/products/{slug}', [ProductController::class, 'show']); 
Route::get('/tiers', [TierController::class, 'index']); 

// ==========================================
// 2. PROTECTED ROUTES (Wajib Login)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    
    // FIX 404 LOGOUT: Tambahkan route logout di sini karena butuh auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // ------------------------------------------
    // 3. CUSTOMER ROUTES
    // ------------------------------------------
    Route::middleware(CheckRole::class.':customer')->prefix('customer')->group(function () {
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders/{id}/pay', [OrderController::class, 'pay']);
        Route::get('/billings', [BillingController::class, 'index']); 
    });

    // ------------------------------------------
    // 4. ADMINISTRATOR ROUTES
    // ------------------------------------------
    Route::middleware(CheckRole::class.':admin')->prefix('admin')->group(function () {
        // Manajemen Bisnis
        Route::get('/customers', [AuthController::class, 'adminCustomers']);
        Route::get('/orders', [OrderController::class, 'adminIndex']);
        Route::post('/orders/{id}/approve', [OrderController::class, 'approve']);
        Route::post('/orders/{id}/reject', [OrderController::class, 'reject']);
        Route::get('/billings', [BillingController::class, 'adminIndex']);

        // Manajemen Produk
        Route::get('/products', [ProductController::class, 'adminIndex']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);

        // Manajemen Tier (Master Setup)
        Route::get('/tiers', [TierController::class, 'index']); // Gunakan jika admin butuh query khusus ke depannya
        Route::post('/tiers', [TierController::class, 'store']);
        Route::put('/tiers/{id}', [TierController::class, 'update']);
        Route::delete('/tiers/{id}', [TierController::class, 'destroy']);
    });
});