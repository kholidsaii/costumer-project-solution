<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductScreenshot;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request) {
        $query = Product::where('is_active', true);
        if ($request->filled('category') && $request->category !== 'All Categories') $query->where('category', $request->category);
        if ($request->filled('price_range')) {
            switch ($request->price_range) {
                case '0-100k': $query->where('price', '<=', 100000); break;
                case '100k-500k': $query->whereBetween('price', [100000, 500000]); break;
                case 'over-500k': $query->where('price', '>', 500000); break;
            }
        }
        if ($request->filled('popularity')) {
            if ($request->popularity === 'Newest') $query->latest();
            elseif ($request->popularity === 'Best Seller') $query->withCount('reviews')->orderBy('reviews_count', 'desc');
        }
        $products = $query->withAvg('reviews', 'rating')->get();
        return response()->json($products, 200);
    }

    public function adminIndex(Request $request) {
        $products = Product::latest()->get();
        return response()->json($products, 200);
    }

    public function show($slug) {
        $product = Product::with(['features', 'screenshots', 'faqs', 'changelogs', 'reviews.user'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('slug', $slug)->first(); 
        if (!$product) return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        return response()->json($product, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'overview' => 'required|string',
            'price' => 'required|numeric',
            'category' => 'required|string',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'features' => 'nullable|array',
            'faqs' => 'nullable|array',
            'changelogs' => 'nullable|array',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }
        
        $product = Product::create($validated);

        if (!empty($request->features)) $product->features()->createMany($request->features);
        if (!empty($request->faqs)) $product->faqs()->createMany($request->faqs);
        if (!empty($request->changelogs)) $product->changelogs()->createMany($request->changelogs);

        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                $path = $file->store('products/screenshots', 'public');
                $product->screenshots()->create(['image_path' => $path]);
            }
        }

        return response()->json(['message' => 'Produk berhasil ditambahkan', 'data' => $product], 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'overview' => 'required|string',
            'price' => 'required|numeric',
            'category' => 'required|string',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'features' => 'nullable|array',
            'faqs' => 'nullable|array',
            'changelogs' => 'nullable|array',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        
        // --- LOGIKA HAPUS GAMBAR UTAMA ---
        if ($request->has('remove_main_image') && $request->remove_main_image == 'true') {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $validated['image'] = null; // Set null di database
        }

        // --- LOGIKA UPLOAD GAMBAR UTAMA BARU ---
        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        // Update Text Arrays
        $product->features()->delete();
        $product->faqs()->delete();
        $product->changelogs()->delete();

        if (!empty($request->features)) $product->features()->createMany($request->features);
        if (!empty($request->faqs)) $product->faqs()->createMany($request->faqs);
        if (!empty($request->changelogs)) $product->changelogs()->createMany($request->changelogs);

        // Update Screenshots Lama
        if ($request->has('deleted_screenshots')) {
            foreach ($request->deleted_screenshots as $ss_id) {
                $ss = ProductScreenshot::find($ss_id);
                if ($ss && $ss->product_id == $product->id) {
                    Storage::disk('public')->delete($ss->image_path);
                    $ss->delete();
                }
            }
        }

        // Tambah Screenshots Baru
        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                $path = $file->store('products/screenshots', 'public');
                $product->screenshots()->create(['image_path' => $path]);
            }
        }

        return response()->json(['message' => 'Produk berhasil diperbarui', 'data' => $product], 200);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        if ($product->image) Storage::disk('public')->delete($product->image);
        foreach($product->screenshots as $ss) {
            Storage::disk('public')->delete($ss->image_path);
        }
        $product->delete();
        return response()->json(['message' => 'Produk berhasil dihapus'], 200);
    }
}