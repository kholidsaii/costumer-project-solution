<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Billing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; 
use Carbon\Carbon;

class OrderController extends Controller
{
    public function index() {
        $orders = Order::with('product')->where('user_id', Auth::id())->latest()->get();
        return response()->json($orders, 200);
    }

    // --- 1. LOGIKA CHECKOUT (PESANAN BARU) ---
    public function store(Request $request) {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_type' => 'required|in:software,digital,physical',
        ]);

        $product = Product::findOrFail($request->product_id);
        $user = Auth::user()->load('tier'); 
        $orderNumber = 'INV-' . time() . '-' . rand(1000, 9999);

        // --- PRODUK SOFTWARE ---
        if ($request->product_type === 'software') {
            if (!$user->tier || $user->tier->software_access != true) {
                return response()->json(['message' => 'Tier Anda saat ini dilarang mengakses Software.'], 403);
            }
            $request->validate([
                'payment_method' => 'required|string',
                'payment_proof' => 'required|image|max:2048',
            ]);

            $path = $request->file('payment_proof')->store('orders/proofs', 'public');

            $order = Order::create([
                'user_id' => $user->id, 'product_id' => $product->id, 'order_number' => $orderNumber,
                'total_amount' => $product->price, 'status' => 'pending',
                'payment_method' => $request->payment_method, 'payment_proof' => $path
            ]);
            return response()->json(['message' => 'Pesanan software berhasil.', 'order' => $order], 201);
        }

        // --- PRODUK FISIK ---
        if ($request->product_type === 'physical') {
            if ($product->stock < 1) return response()->json(['message' => 'Stok produk fisik habis.'], 400);

            $request->validate(['shipping_address' => 'required|string', 'courier' => 'required|string']);
            
            $product->decrement('stock', 1);

            // LOGIKA BARU: Jika kurir adalah Toko, status menjadi awaiting_ongkir
            $status = 'pending';
            $shippingCost = $request->shipping_cost ?? 0;

            if ($request->courier === 'Pengiriman Toko') {
                $status = 'awaiting_ongkir';
                $shippingCost = 0;
            }

            $order = Order::create([
                'user_id' => $user->id, 'product_id' => $product->id, 'order_number' => $orderNumber,
                'total_amount' => $product->price + $shippingCost, 'status' => $status,
                'shipping_address' => $request->shipping_address,
                'shipping_cost' => $shippingCost, 'courier' => $request->courier
            ]);
            return response()->json(['message' => 'Pesanan fisik berhasil dibuat.', 'order' => $order], 201);
        }
    }

    // --- 2. CUSTOMER: UPLOAD BUKTI TRANSFER UNTUK FISIK ---
    public function uploadProof(Request $request, $id) {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);
        $request->validate([
            'payment_method' => 'required|string',
            'payment_proof' => 'required|image|max:2048'
        ]);

        $path = $request->file('payment_proof')->store('orders/proofs', 'public');
        $order->update([
            'payment_method' => $request->payment_method,
            'payment_proof' => $path
        ]);
        return response()->json(['message' => 'Bukti pembayaran berhasil dikirim untuk verifikasi.']);
    }

    // --- 3. LOGIKA DOWNLOAD DIGITAL ---
    public function downloadDigital($productId) {
        $product = Product::findOrFail($productId);
        $user = Auth::user()->load('tier');

        if ($product->product_type !== 'digital') return response()->json(['message' => 'Bukan produk digital.'], 400);
        if (!$product->file_path) return response()->json(['message' => 'File digital belum diunggah admin.'], 404);

        $limit = $user->tier ? $user->tier->digital_limit : 0;
        if ($user->digital_downloads_count >= $limit && $limit < 999999) {
            return response()->json(['message' => "Limit download habis."], 403);
        }
        $user->increment('digital_downloads_count');
        return response()->json(['message' => 'Download diizinkan.', 'download_url' => url('storage/' . $product->file_path)], 200);
    }

    // --- RAJAONGKIR ---
    public function getProvinces() {
        try {
            $key = env('RAJAONGKIR_API_KEY');
            $response = Http::withoutVerifying()->timeout(30)->withHeaders(['key' => $key])->get('https://rajaongkir.komerce.id/api/v1/destination/province');
            if ($response->failed()) return response()->json(['message' => 'Ditolak Komerce: ' . $response->body()], 500);
            return $response->json();
        } catch (\Throwable $e) { return response()->json(['message' => 'Crash: ' . $e->getMessage()], 500); }
    }

    public function getCities($provinceId) {
        try {
            $key = env('RAJAONGKIR_API_KEY');
            $response = Http::withoutVerifying()->timeout(30)->withHeaders(['key' => $key])->get("https://rajaongkir.komerce.id/api/v1/destination/city/{$provinceId}");
            if ($response->failed()) return response()->json(['message' => 'Ditolak Komerce: ' . $response->body()], 500);
            return $response->json();
        } catch (\Throwable $e) { return response()->json(['message' => 'Crash: ' . $e->getMessage()], 500); }
    }

    public function checkOngkir(Request $request) {
        try {
            $key = env('RAJAONGKIR_API_KEY');
            $response = Http::withoutVerifying()->timeout(30)->asForm()->withHeaders(['key' => $key])->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                'origin' => env('RAJAONGKIR_ORIGIN_CITY', '154'), 'destination' => $request->destination_city_id,
                'weight' => $request->weight ?? 1000, 'courier' => $request->courier
            ]);
            if ($response->status() === 404) return response()->json(['message' => 'Maaf, kurir tidak menjangkau.'], 404);
            if ($response->failed()) return response()->json(['message' => 'Ditolak: ' . $response->body()], 500);
            return $response->json();
        } catch (\Throwable $e) { return response()->json(['message' => 'Crash: ' . $e->getMessage()], 500); }
    }

    // --- ADMIN ROUTES ---
    public function adminIndex() {
        $orders = Order::with(['product', 'user'])->latest()->get();
        return response()->json($orders, 200);
    }

    public function setupSoftware(Request $request, $id) {
        try {
            $order = Order::with('product')->findOrFail($id);
            $request->validate(['software_link' => 'required|url', 'software_username' => 'required|string', 'software_password' => 'required|string']);
            $order->update([
                'software_link' => $request->software_link,
                'software_username' => $request->software_username,
                'software_password' => $request->software_password,
            ]);
            return response()->json(['message' => 'Data Setup Disimpan.'], 200);
        } catch (\Throwable $e) { return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500); }
    }

    // ADMIN: INPUT ONGKIR MANUAL
    public function inputOngkir(Request $request, $id) {
        $order = Order::with('product')->findOrFail($id);
        $request->validate(['shipping_cost' => 'required|numeric|min:0']);
        
        $total = $order->product->price + $request->shipping_cost;
        $order->update([
            'shipping_cost' => $request->shipping_cost,
            'total_amount' => $total,
            'status' => 'pending' // Mengubah status agar customer bisa mulai upload bukti TF
        ]);
        return response()->json(['message' => 'Ongkir berhasil ditetapkan.'], 200);
    }

    public function approve(Request $request, $id) {
        $order = Order::with('product')->findOrFail($id);
        if ($order->status === 'pending') {
            if ($order->product->product_type === 'software') {
                if (empty($order->software_link) || empty($order->software_username)) {
                    return response()->json(['message' => 'Anda harus melakukan SETUP akun software.'], 400);
                }
            }
            $order->update(['status' => 'paid']);
            Billing::create([
                'user_id' => $order->user_id, 'invoice_number' => 'BILL-' . time() . rand(10, 99),
                'description' => 'Pembayaran: ' . $order->product->name,
                'amount' => $order->total_amount, 'due_date' => Carbon::now()->addYear(), 'status' => 'unpaid'
            ]);
            return response()->json(['message' => 'Pesanan disetujui.'], 200);
        }
        return response()->json(['message' => 'Status tidak valid.'], 400);
    }

    public function reject(Request $request, $id) {
        $order = Order::with('product')->findOrFail($id);
        if ($order->status !== 'cancelled') {
            $request->validate(['reject_reason' => 'required|string|max:500']);
            $order->update(['status' => 'cancelled', 'reject_reason' => $request->reject_reason]);
            if ($order->product->product_type === 'physical') $order->product->increment('stock', 1);
            return response()->json(['message' => 'Order ditolak.'], 200);
        }
        return response()->json(['message' => 'Order sudah dibatalkan.'], 400);
    }

    // [ADMIN] Hapus order permanen
    public function destroy($id) {
        try {
            $order = Order::findOrFail($id);
            
            // Opsional: Hapus file gambar bukti transfer dari penyimpanan agar server tidak penuh
            if ($order->payment_proof) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($order->payment_proof);
            }
            
            $order->delete();
            
            return response()->json(['message' => 'Pesanan berhasil dihapus permanen.'], 200);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal menghapus pesanan: ' . $e->getMessage()], 500);
        }
    }
}