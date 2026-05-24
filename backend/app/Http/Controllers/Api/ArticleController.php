<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    // Mengambil artikel terbaru untuk Landing Page
    public function index()
    {
        // Mengambil artikel beserta nama penulisnya (relasi user), diurutkan dari yang terbaru
        $articles = Article::with('author:id,name')->latest()->get();
        return response()->json($articles, 200);
    }
}