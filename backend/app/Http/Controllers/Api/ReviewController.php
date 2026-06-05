<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // [CUSTOMER] Menyimpan ATAU Mengupdate review produk
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        // updateOrCreate akan mencari review berdasarkan product_id dan user_id
        // Jika ketemu, dia akan mengupdate rating & comment. Jika tidak, dia akan buat baru.
        $review = Review::updateOrCreate(
            [
                'product_id' => $request->product_id,
                'user_id' => Auth::id(),
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]
        );

        $review->load('user'); // Load data user agar nama langsung muncul di frontend

        return response()->json(['message' => 'Review berhasil disimpan', 'data' => $review], 200);
    }
}