<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Mengambil semua produk yang aktif untuk Landing Page & Customer Access
    public function index()
    {
        $products = Product::where('is_active', true)->get();
        return response()->json($products, 200);
    }

    // Nanti ditambahkan fungsi store, update, destroy untuk Admin Dashboard...
}