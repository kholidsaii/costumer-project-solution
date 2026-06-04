<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    // [CUSTOMER ACCESS] Mengambil riwayat order milik pelanggan aktif
    public function index()
    {
        $orders = Order::with('product')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json($orders, 200);
    }

    // [CUSTOMER ACCESS] Membuat pesanan baru saat klik "Place Order"
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $product = Product::findOrFail($request->product_id);

        // Pembuatan kode invoice unik otomatis
        $orderNumber = 'INV-' . time() . '-' . rand(1000, 9999);

        $order = Order::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'order_number' => $orderNumber,
            'total_amount' => $product->price,
            'status' => 'pending' // Default status sebelum integrasi payment gateway
        ]);

        return response()->json([
            'message' => 'Pesanan berhasil dibuat, silahkan lakukan pembayaran.',
            'order' => $order
        ], 201);
    }
}