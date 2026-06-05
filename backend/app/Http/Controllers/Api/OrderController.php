<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Billing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OrderController extends Controller
{
    // [CUSTOMER] Ambil order milik user
    public function index()
    {
        $orders = Order::with('product')->where('user_id', Auth::id())->latest()->get();
        return response()->json($orders, 200);
    }

    // [CUSTOMER] Buat order baru
    public function store(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);
        $product = Product::findOrFail($request->product_id);
        $orderNumber = 'INV-' . time() . '-' . rand(1000, 9999);

        $order = Order::create([
            'user_id' => Auth::id(), 'product_id' => $product->id,
            'order_number' => $orderNumber, 'total_amount' => $product->price, 'status' => 'pending'
        ]);

        return response()->json(['message' => 'Pesanan berhasil dibuat.', 'order' => $order], 201);
    }

    // [CUSTOMER] Bayar order
    public function pay($id)
    {
        $order = Order::with('product')->where('user_id', Auth::id())->findOrFail($id);
        if ($order->status === 'pending') {
            $order->update(['status' => 'paid']);
            Billing::create([
                'user_id' => Auth::id(), 'invoice_number' => 'BILL-' . time() . rand(10, 99),
                'description' => 'Perpanjangan Tahunan: ' . $order->product->name,
                'amount' => $order->total_amount, 'due_date' => Carbon::now()->addYear(), 'status' => 'unpaid'
            ]);
            return response()->json(['message' => 'Pembayaran berhasil!'], 200);
        }
        return response()->json(['message' => 'Sudah diproses.'], 400);
    }

    // [ADMIN] Ambil semua order
    public function adminIndex()
    {
        $orders = Order::with(['product', 'user'])->latest()->get();
        return response()->json($orders, 200);
    }

    // [ADMIN] Setujui order & terbitkan billing
    public function approve($id)
    {
        $order = Order::with('product')->findOrFail($id);
        if ($order->status === 'pending') {
            $order->update(['status' => 'paid']);
            Billing::create([
                'user_id' => $order->user_id, 'invoice_number' => 'BILL-' . time() . rand(10, 99),
                'description' => 'Perpanjangan Tahunan: ' . $order->product->name,
                'amount' => $order->total_amount, 'due_date' => Carbon::now()->addYear(), 'status' => 'unpaid'
            ]);
            return response()->json(['message' => 'Order disetujui & Billing diterbitkan.'], 200);
        }
        return response()->json(['message' => 'Status tidak valid.'], 400);
    }

    // [ADMIN] Tolak order
    public function reject($id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Order berhasil ditolak.'], 200);
    }
}