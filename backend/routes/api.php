<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\SupportController;

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES (Bisa diakses siapa saja, untuk Landing Page)
|--------------------------------------------------------------------------
*/
// Autentikasi
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Landing Page Data
Route::get('/products', [ProductController::class, 'index']);
Route::get('/articles', [ArticleController::class, 'index']);
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
        // Nanti ini untuk proses nambah produk, nulis artikel, cek orderan masuk
        // Route::post('/products', [ProductController::class, 'store']);
        // Route::post('/articles', [ArticleController::class, 'store']);
        // Route::get('/orders', [AdminOrderController::class, 'index']);
    });

});