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
        $query = Product::where('is_active', true)->with(['reviews.user']);
        
        if ($request->filled('category') && $request->category !== 'All Categories') {
            $query->where('category', $request->category);
        }
        
        if ($request->filled('popularity')) {
            if ($request->popularity === 'Newest') $query->latest();
            elseif ($request->popularity === 'Best Seller') $query->withCount('reviews')->orderBy('reviews_count', 'desc');
        }
        
        $products = $query->withAvg('reviews', 'rating')->get();
        return response()->json($products, 200);
    }

    public function adminIndex()
    {
        $products = Product::with(['features', 'faqs', 'changelogs'])->latest()->get();
        return response()->json($products, 200);
    }

    public function show($slug)
    {
        // Mencari produk berdasarkan slug beserta semua relasi datanya
        $product = Product::where('slug', $slug)
            ->with(['features', 'faqs', 'changelogs', 'screenshots', 'reviews.user'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->first();

        // Jika produk tidak ditemukan, kembalikan error 404
        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json($product, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'product_type' => 'required|in:software,digital,physical',
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name) . '-' . time();
        $product->description = $request->description;
        $product->overview = $request->overview;
        $product->category = $request->category ?? 'Software';
        
        // Membaca boolean dari angka 1/0
        $product->is_active = $request->is_active == '1' ? true : false; 
        
        $product->product_type = $request->product_type;
        $product->stock = $request->product_type === 'physical' ? ($request->stock ?? 0) : 0;
        
        // PENTING: Menyimpan Harga & Tier Akses
        $product->price = $request->price ?? 0;
        $product->access_tier = $request->access_tier ?? 'all';

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products/images', 'public');
            $product->image = $path;
        }

        // Menyimpan file master digital (RAB, template, dll)
        if ($request->hasFile('digital_file')) {
            if (isset($product->file_path)) Storage::disk('public')->delete($product->file_path);
            $product->file_path = $request->file('digital_file')->store('products/files', 'public');
        }

        $product->save();

        if (!empty($request->features)) $product->features()->createMany($request->features);
        if (!empty($request->faqs)) $product->faqs()->createMany($request->faqs);
        if (!empty($request->changelogs)) $product->changelogs()->createMany($request->changelogs);

        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                $path = $file->store('products/screenshots', 'public');
                $product->screenshots()->create(['image_path' => $path]);
            }
        }

        return response()->json(['message' => 'Produk berhasil dibuat', 'data' => $product], 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'product_type' => 'required|in:software,digital,physical',
        ]);

        $product->name = $request->name;
        $product->description = $request->description;
        $product->overview = $request->overview;
        $product->category = $request->category;
        $product->is_active = $request->is_active;
        
        $product->product_type = $request->product_type;
        $product->stock = $request->product_type === 'physical' ? ($request->stock ?? 0) : 0;

        if ($request->has('remove_image') && $request->remove_image == true) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $product->image = null;
        }

        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $path = $request->file('image')->store('products/images', 'public');
            $product->image = $path;
        }

        // Menyimpan file master digital (RAB, template, dll)
        if ($request->hasFile('digital_file')) {
            if (isset($product->file_path)) Storage::disk('public')->delete($product->file_path);
            $product->file_path = $request->file('digital_file')->store('products/files', 'public');
        }

        $product->save();

        $product->features()->delete();
        $product->faqs()->delete();
        $product->changelogs()->delete();

        if (!empty($request->features)) $product->features()->createMany($request->features);
        if (!empty($request->faqs)) $product->faqs()->createMany($request->faqs);
        if (!empty($request->changelogs)) $product->changelogs()->createMany($request->changelogs);

        if ($request->has('deleted_screenshots')) {
            foreach ($request->deleted_screenshots as $ss_id) {
                $ss = ProductScreenshot::find($ss_id);
                if ($ss && $ss->product_id == $product->id) {
                    Storage::disk('public')->delete($ss->image_path);
                    $ss->delete();
                }
            }
        }

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