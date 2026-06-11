<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TierController extends Controller
{
    public function index()
    {
        $tiers = Tier::orderBy('price', 'asc')->get();
        return response()->json($tiers, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tiers,name',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'digital_limit' => 'required|integer|min:0',
            'software_access' => 'required|boolean'
        ]);

        $tier = Tier::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'digital_limit' => $request->digital_limit,
            'software_access' => $request->software_access
        ]);

        return response()->json(['message' => 'Tier berhasil ditambahkan', 'data' => $tier], 201);
    }

    public function update(Request $request, $id)
    {
        $tier = Tier::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:tiers,name,' . $id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'digital_limit' => 'required|integer|min:0',
            'software_access' => 'required|boolean'
        ]);

        $tier->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'digital_limit' => $request->digital_limit,
            'software_access' => $request->software_access
        ]);

        return response()->json(['message' => 'Tier berhasil diperbarui', 'data' => $tier], 200);
    }

    public function destroy($id)
    {
        $tier = Tier::findOrFail($id);
        $tier->delete();
        return response()->json(['message' => 'Tier berhasil dihapus'], 200);
    }
}