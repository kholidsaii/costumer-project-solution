<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TierController extends Controller
{
    // Mengambil semua data tier (Bisa diakses publik/customer untuk list opsi)
    public function index()
    {
        $tiers = Tier::orderBy('price', 'asc')->get();
        return response()->json($tiers, 200);
    }

    // Menambah tier baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tiers,name',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);

        $tier = Tier::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
        ]);

        return response()->json(['message' => 'Tier berhasil ditambahkan', 'data' => $tier], 201);
    }

    // Memperbarui tier yang ada
    public function update(Request $request, $id)
    {
        $tier = Tier::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:tiers,name,' . $id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);

        $tier->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
        ]);

        return response()->json(['message' => 'Tier berhasil diperbarui', 'data' => $tier], 200);
    }

    // Menghapus tier
    public function destroy($id)
    {
        $tier = Tier::findOrFail($id);
        
        // Opsional: Cek apakah tier sedang digunakan oleh produk sebelum dihapus
        // if ($tier->products()->exists()) {
        //     return response()->json(['message' => 'Tier tidak bisa dihapus karena masih digunakan produk'], 422);
        // }

        $tier->delete();
        return response()->json(['message' => 'Tier berhasil dihapus'], 200);
    }
}