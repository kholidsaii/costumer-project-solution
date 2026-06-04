<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // [CUSTOMER] Menyimpan review produk
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        $review = Review::create([
            'product_id' => $request->product_id,
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        $review->load('user'); // Load data user agar nama langsung muncul di frontend

        return response()->json(['message' => 'Review berhasil dikirim', 'data' => $review], 201);
    }
}