<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    // [CUSTOMER] Tagihan miliknya
    public function index()
    {
        $billings = Billing::where('user_id', Auth::id())->orderBy('due_date', 'asc')->get();
        return response()->json($billings, 200);
    }

    // [ADMIN] Semua tagihan
    public function adminIndex()
    {
        $billings = Billing::with('user')->orderBy('due_date', 'asc')->get();
        return response()->json($billings, 200);
    }
}