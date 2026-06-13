<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TierTransaction;
use App\Models\Tier;
use App\Models\User;
use App\Models\Billing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TierTransactionController extends Controller
{
    // [CUSTOMER] Meminta Upgrade Paket
    public function store(Request $request) {
        $request->validate([
            'tier_id' => 'required|exists:tiers,id',
            'payment_method' => 'required|string',
            'payment_proof' => 'required|image|max:2048'
        ]);

        $tier = Tier::findOrFail($request->tier_id);
        $path = $request->file('payment_proof')->store('tiers/proofs', 'public');

        $trx = TierTransaction::create([
            'user_id' => Auth::id(), 'tier_id' => $tier->id,
            'transaction_number' => 'UPG-' . time() . rand(10,99),
            'amount' => $tier->price, 'payment_method' => $request->payment_method,
            'payment_proof' => $path, 'status' => 'pending'
        ]);

        return response()->json(['message' => 'Permintaan upgrade berhasil dikirim. Menunggu verifikasi Admin.']);
    }

    // [ADMIN] Ambil semua transaksi upgrade
    public function index() {
        $trx = TierTransaction::with(['user', 'tier'])->latest()->get();
        return response()->json($trx, 200);
    }

    // [ADMIN] Ambil daftar member premium aktif
    public function activeMembers() {
        $users = User::with('tier')->whereNotNull('tier_id')->whereHas('tier', function($q) {
            $q->where('price', '>', 0);
        })->get();
        return response()->json($users, 200);
    }

    // [ADMIN] Approve & otomatis buat Billing
    public function approve($id) {
        $trx = TierTransaction::with('tier')->findOrFail($id);
        if ($trx->status === 'pending') {
            $trx->update(['status' => 'paid']);

            // Update Hak Akses User
            $user = User::findOrFail($trx->user_id);
            $user->update([
                'tier_id' => $trx->tier_id,
                'tier_expires_at' => $trx->tier->duration_in_months > 0 
                    ? Carbon::now()->addMonths($trx->tier->duration_in_months) 
                    : null // Jika 0 = Selamanya
            ]);

            // Sinkronisasi ke Billing (Sebagai Riwayat Lunas)
            Billing::create([
                'user_id' => $user->id,
                'invoice_number' => 'BILL-' . $trx->transaction_number,
                'description' => 'Langganan Paket: ' . $trx->tier->name,
                'amount' => $trx->amount,
                'due_date' => Carbon::now(),
                'status' => 'paid'
            ]);

            return response()->json(['message' => 'Upgrade disetujui! Member telah aktif.']);
        }
        return response()->json(['message' => 'Status tidak valid.'], 400);
    }

    public function reject(Request $request, $id) {
        $trx = TierTransaction::findOrFail($id);
        if ($trx->status === 'pending') {
            $request->validate(['reject_reason' => 'required|string']);
            $trx->update(['status' => 'rejected', 'reject_reason' => $request->reject_reason]);
            return response()->json(['message' => 'Upgrade ditolak.']);
        }
        return response()->json(['message' => 'Status tidak valid.'], 400);
    }
}