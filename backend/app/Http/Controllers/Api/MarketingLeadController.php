<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketingLeadController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('marketing_leads')
                ->join('users', 'marketing_leads.user_id', '=', 'users.id')
                ->select('marketing_leads.*', 'users.name as pic_name');

            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('marketing_leads.status', $request->status);
            }

            if ($request->filled('search')) {
                $keyword = $request->search;
                $query->where(function($q) use ($keyword) {
                    $q->where('marketing_leads.lead_name', 'ILIKE', "%{$keyword}%")
                      ->orWhere('marketing_leads.company_name', 'ILIKE', "%{$keyword}%")
                      ->orWhere('marketing_leads.notes', 'ILIKE', "%{$keyword}%");
                });
            }

            $leads = $query->orderBy('marketing_leads.created_at', 'desc')->get();
            return response()->json($leads, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'lead_name' => 'required|string|max:255',
            'status' => 'required|string',
            'estimated_value' => 'required|numeric'
        ]);

        try {
            $id = DB::table('marketing_leads')->insertGetId([
                'lead_name' => $request->lead_name,
                'company_name' => $request->company_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'source' => $request->source,
                'status' => $request->status,
                'estimated_value' => $request->estimated_value,
                'notes' => $request->notes,
                'user_id' => 1, // Kita set 1 karena tadi di SQL kita buat 1 user dummy
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(['message' => 'Prospek berhasil ditambah!', 'id' => $id], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            DB::table('marketing_leads')->where('id', $id)->update([
                'lead_name' => $request->lead_name,
                'company_name' => $request->company_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'source' => $request->source,
                'status' => $request->status,
                'estimated_value' => $request->estimated_value,
                'notes' => $request->notes,
                'updated_at' => now()
            ]);

            return response()->json(['message' => 'Progress diperbarui!'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::table('marketing_leads')->where('id', $id)->delete();
            return response()->json(['message' => 'Data dihapus!'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}