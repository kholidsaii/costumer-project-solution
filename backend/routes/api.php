<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MarketingLeadController;

// Karena ini project baru tanpa sistem login utuh dulu, 
// kita taruh di luar middleware auth agar mudah ditest dengan frontend.
Route::get('/marketing-leads', [MarketingLeadController::class, 'index']);
Route::post('/marketing-leads', [MarketingLeadController::class, 'store']);
Route::put('/marketing-leads/{id}', [MarketingLeadController::class, 'update']);
Route::delete('/marketing-leads/{id}', [MarketingLeadController::class, 'destroy']);