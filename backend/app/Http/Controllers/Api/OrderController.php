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
    public function index()
    {
        $orders = Order::with('product')->where('user_id', Auth::id())->latest()->get();
        return response()->json($orders, 200);
    }

    // --- 1. LOGIKA CHECKOUT (PESANAN BARU) ---
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_type' => 'required|in:software,digital,physical',
        ]);

        $product = Product::findOrFail($request->product_id);
        $user = Auth::user();
        $tier = $user->tier->slug ?? 'free';
        $orderNumber = 'INV-' . time() . '-' . rand(1000, 9999);

        // Jika Software, wajib Gold (Tanpa kurangi stok)
        if ($request->product_type === 'software') {
            if ($tier !== 'gold') {
                return response()->json(['message' => 'Hanya Gold Member yang dapat membeli Software.'], 403);
            }
            
            $request->validate([
                'payment_method' => 'required|string',
                'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $path = $request->file('payment_proof')->store('orders/proofs', 'public');

            $order = Order::create([
                'user_id' => $user->id, 'product_id' => $product->id, 'order_number' => $orderNumber,
                'total_amount' => $product->price, 'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_proof' => $path
            ]);

            return response()->json(['message' => 'Pesanan software berhasil. Menunggu verifikasi admin.', 'order' => $order], 201);
        }

        // Jika Fisik, cek & potong stok, lalu simpan alamat dan ongkir
        if ($request->product_type === 'physical') {
            
            // CEK STOK SEBELUM PESANAN DIBUAT
            if ($product->stock < 1) {
                return response()->json(['message' => 'Maaf, stok produk fisik ini sedang habis.'], 400);
            }

            $request->validate([
                'shipping_address' => 'required|string',
                'shipping_cost' => 'required|numeric',
                'courier' => 'required|string',
            ]);

            // POTONG STOK FISIK SAAT DIPESAN
            $product->decrement('stock', 1);

            $total = $product->price + $request->shipping_cost;

            $order = Order::create([
                'user_id' => $user->id, 'product_id' => $product->id, 'order_number' => $orderNumber,
                'total_amount' => $total, 'status' => 'pending',
                'shipping_address' => $request->shipping_address,
                'shipping_cost' => $request->shipping_cost,
                'courier' => $request->courier
            ]);

            return response()->json(['message' => 'Pesanan fisik berhasil dibuat.', 'order' => $order], 201);
        }
    }

    // --- 2. LOGIKA DOWNLOAD DIGITAL (Hanya berdasar Limit, TANPA Potong Stok) ---
    public function downloadDigital($productId)
    {
        $product = Product::findOrFail($productId);
        $user = Auth::user()->load('tier');

        if ($product->product_type !== 'digital') {
            return response()->json(['message' => 'Produk ini bukan produk digital.'], 400);
        }

        if (!$product->file_path) {
            return response()->json(['message' => 'File digital belum diunggah admin.'], 404);
        }

        // Ambil limit dari master tier
        $limit = $user->tier ? $user->tier->digital_limit : 0;
        
        // Cek Limit
        if ($user->digital_downloads_count >= $limit && $limit < 999999) {
            return response()->json(['message' => "Limit download Anda ({$limit}x) telah habis. Silakan upgrade tier Anda."], 403);
        }

        // HANYA tambah counter download, tanpa memotong stok produk
        $user->increment('digital_downloads_count');

        return response()->json([
            'message' => 'Download diizinkan.',
            'download_url' => url('storage/' . $product->file_path),
            'current_downloads' => $user->digital_downloads_count
        ], 200);
    }

    // --- 3. RAJAONGKIR (KOMERCE) IMPLEMENTATION ---
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
                'origin' => env('RAJAONGKIR_ORIGIN_CITY', '154'), 
                'destination' => $request->destination_city_id,
                'weight' => $request->weight ?? 1000,
                'courier' => $request->courier
            ]);
            
            if ($response->status() === 404) return response()->json(['message' => 'Maaf, kurir ' . strtoupper($request->courier) . ' tidak menjangkau rute ini.'], 404);
            if ($response->failed()) return response()->json(['message' => 'Ditolak Komerce: ' . $response->body()], 500);
            return $response->json();
        } catch (\Throwable $e) { return response()->json(['message' => 'Crash: ' . $e->getMessage()], 500); }
    }

    // --- 4. ADMIN: Manajemen Order ---
    public function adminIndex() {
        $orders = Order::with(['product', 'user'])->latest()->get();
        return response()->json($orders, 200);
    }

    public function approve(Request $request, $id) {
        $order = Order::with('product')->findOrFail($id);
        if ($order->status === 'pending') {
            $order->update(['status' => 'paid']);
            
            // Jika Software, simpan link akses dari admin
            if ($order->product->product_type === 'software') {
                $order->update(['software_link' => $request->software_link]);
            }
            // (Stok fisik sudah dipotong di awal saat store/pesanan dibuat)

            Billing::create([
                'user_id' => $order->user_id, 'invoice_number' => 'BILL-' . time() . rand(10, 99),
                'description' => 'Pembayaran: ' . $order->product->name,
                'amount' => $order->total_amount, 'due_date' => Carbon::now()->addYear(), 'status' => 'unpaid'
            ]);
            return response()->json(['message' => 'Order disetujui.'], 200);
        }
        return response()->json(['message' => 'Status tidak valid.'], 400);
    }

    public function reject($id) {
        $order = Order::with('product')->findOrFail($id);
        
        if ($order->status !== 'cancelled') {
            $order->update(['status' => 'cancelled']);
            
            // RESTORE STOK: Jika pesanan fisik ditolak/dibatalkan, kembalikan stoknya
            if ($order->product->product_type === 'physical') {
                $order->product->increment('stock', 1);
            }
            
            return response()->json(['message' => 'Order berhasil ditolak & stok dikembalikan.'], 200);
        }
        
        return response()->json(['message' => 'Order sudah dibatalkan.'], 400);
    }
}