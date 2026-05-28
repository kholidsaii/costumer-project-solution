<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\SupportController;

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES (Bisa diakses siapa saja tanpa login)
|--------------------------------------------------------------------------
*/
// Autentikasi
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Landing Page Data
Route::get('/products', [ProductController::class, 'index']);
Route::get('/tutorials', [SupportController::class, 'getTutorials']);

/*
|--------------------------------------------------------------------------
| 2. PROTECTED ROUTES (Wajib Login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    
    // Logout (Semua role bisa)
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Ambil data profil user yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    /*
    |----------------------------------------------------------------------
    | INTERAKSI MEDIA / FEED (Semua user login bisa posting & interaksi)
    |----------------------------------------------------------------------
    */
    Route::prefix('articles')->group(function () {
        // Feed Data (Dipindah ke dalam auth agar Auth::id() berfungsi untuk ngecek Like)
        Route::get('/', [ArticleController::class, 'index']); 
        Route::get('/{id}', [ArticleController::class, 'show']); 
        Route::get('/{id}/comments', [ArticleController::class, 'getComments']); 

        // Aksi Postingan
        Route::post('/', [ArticleController::class, 'store']); // Buat postingan baru
        // Route::put('/{id}', [ArticleController::class, 'update']); // Edit (Bisa diaktifkan jika controllernya sudah Anda buat)
        Route::delete('/{id}', [ArticleController::class, 'destroy']); // Hapus postingan sendiri
        
        // Fitur Sosial ala LinkedIn
        Route::post('/{id}/like', [ArticleController::class, 'toggleLike']); // Suka / Batal Suka
        Route::post('/{id}/comments', [ArticleController::class, 'storeComment']); // Tulis komentar
        // Route::delete('/comments/{commentId}', [ArticleController::class, 'destroyComment']); // Hapus komentar sendiri
    });

    /*
    |----------------------------------------------------------------------
    | 3. CUSTOMER ROUTES (Khusus Role: Customer)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:customer')->prefix('customer')->group(function () {
        // Nanti kita buatkan Controllernya di tahap selanjutnya
        // Route::get('/orders', [CustomerOrderController::class, 'index']);
        // Route::get('/billings', [CustomerBillingController::class, 'index']);
        // Route::post('/tickets', [TicketController::class, 'store']);
    });

    /*
    |----------------------------------------------------------------------
    | 4. ADMINISTRATOR ROUTES (Khusus Role: Admin)
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Area khusus admin untuk manajemen master data
        // Route::post('/products', [ProductController::class, 'store']);
        // Route::put('/products/{id}', [ProductController::class, 'update']);
        // Route::get('/orders', [AdminOrderController::class, 'index']);
    });

});