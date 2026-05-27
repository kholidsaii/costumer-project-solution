<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Wajib ditambahkan untuk query Likes & Comments

class ArticleController extends Controller
{
    /**
     * Mengambil semua artikel beserta author, perhitungan Like, dan Komentar (Untuk Feed)
     */
    public function index()
    {
        $userId = Auth::id(); // Ambil ID user yang sedang login

        // Ambil artikel urut dari yang paling baru
        $articles = Article::with('author:id,name')->latest()->get();
        
        // Memodifikasi output setiap artikel
        $articles->transform(function ($article) use ($userId) {
            // 1. Set URL Media
            $article->media_url = $article->media_path ? url('storage/' . $article->media_path) : null;
            
            // 2. Hitung total Like & Comment dari database
            $article->likes_count = DB::table('likes')->where('article_id', $article->id)->count();
            $article->comments_count = DB::table('comments')->where('article_id', $article->id)->count();
            
            // 3. Cek apakah user yang sedang login sudah me-like artikel ini
            $article->is_liked_by_me = $userId ? DB::table('likes')
                ->where('article_id', $article->id)
                ->where('user_id', $userId)
                ->exists() : false;

            return $article;
        });

        return response()->json($articles, 200);
    }

    /**
     * Mengambil satu artikel secara spesifik
     */
    public function show($id)
    {
        $article = Article::with('author:id,name')->findOrFail($id);
        $article->media_url = $article->media_path ? url('storage/' . $article->media_path) : null;
        
        return response()->json($article, 200);
    }

    /**
     * Membuat postingan baru dengan upload media (Foto/Video)
     */
    public function store(Request $request)
    {
        // Validasi input dari Vue
        $request->validate([
            'title' => 'nullable|string|max:255',
            'category' => 'required|string|max:100',
            'excerpt' => 'required|string',
            // File media opsional, max ukuran 20MB
            'media' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480', 
        ]);

        $mediaPath = null;
        $mediaType = null;

        // Jika user mengunggah file media
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $extension = $file->getClientOriginalExtension();
            
            // Cek apakah file berupa gambar atau video
            $imageExtensions = ['jpeg', 'png', 'jpg', 'gif'];
            $mediaType = in_array(strtolower($extension), $imageExtensions) ? 'image' : 'video';
            
            // Simpan file ke folder 'storage/app/public/articles'
            $mediaPath = $file->store('articles', 'public');
        }

        // Simpan ke database
        $article = Article::create([
            'user_id' => Auth::id(), // ID user yang sedang login
            'title' => $request->title,
            'category' => $request->category,
            'excerpt' => $request->excerpt,
            'media_path' => $mediaPath,
            'media_type' => $mediaType,
        ]);

        // Load relasi dan set data agar Vue bisa langsung nampilin tanpa refresh
        $article->load('author:id,name');
        $article->media_url = $article->media_path ? url('storage/' . $article->media_path) : null;
        
        return response()->json([
            'message' => 'Postingan berhasil dibuat!',
            'article' => $article
        ], 201);
    }

    /**
     * Menghapus postingan beserta file medianya
     */
    public function destroy($id)
    {
        $article = Article::findOrFail($id);

        // Pastikan hanya pemilik postingan yang bisa menghapus
        if ($article->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Hapus file fisik dari server jika ada
        if ($article->media_path) {
            Storage::disk('public')->delete($article->media_path);
        }

        $article->delete();

        return response()->json(['message' => 'Postingan berhasil dihapus!'], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | FITUR INTERAKSI SOSIAL (LIKE & COMMENT)
    |--------------------------------------------------------------------------
    */

    /**
     * Menambahkan atau menghapus Like (Toggle)
     */
    public function toggleLike($id)
    {
        $userId = Auth::id();
        
        // Cek apakah user sudah like artikel ini
        $existingLike = DB::table('likes')
            ->where('article_id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($existingLike) {
            // Jika sudah like, maka hapus (Unlike)
            DB::table('likes')->where('id', $existingLike->id)->delete();
            $isLiked = false;
        } else {
            // Jika belum like, maka tambahkan
            DB::table('likes')->insert([
                'article_id' => $id, 
                'user_id' => $userId, 
                'created_at' => now(), 
                'updated_at' => now()
            ]);
            $isLiked = true;
        }

        // Hitung ulang total likes untuk update di frontend
        $likesCount = DB::table('likes')->where('article_id', $id)->count();

        return response()->json([
            'is_liked' => $isLiked, 
            'likes_count' => $likesCount
        ], 200);
    }

    /**
     * Mengambil daftar komentar dari satu artikel
     */
    public function getComments($id)
    {
        $comments = DB::table('comments')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->where('comments.article_id', $id)
            ->select('comments.*', 'users.name as user_name')
            ->orderBy('comments.created_at', 'asc') // Urutkan komentar lama ke baru
            ->get();

        return response()->json($comments, 200);
    }

    /**
     * Menulis komentar baru
     */
    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        $userId = Auth::id();

        // Insert komentar baru dan ambil ID-nya
        $commentId = DB::table('comments')->insertGetId([
            'article_id' => $id,
            'user_id' => $userId,
            'content' => $request->content,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Ambil data komentar yang baru dibuat beserta nama usernya (untuk di-push ke array Vue)
        $newComment = DB::table('comments')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->where('comments.id', $commentId)
            ->select('comments.*', 'users.name as user_name')
            ->first();

        return response()->json($newComment, 201);
    }
}