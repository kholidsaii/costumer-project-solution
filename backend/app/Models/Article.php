<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    // Tentukan kolom apa saja yang bisa diisi (Mass Assignment)
    protected $fillable = [
        'user_id', 
        'title', 
        'category', 
        'excerpt', 
        'media_path', // Untuk menyimpan lokasi file di server
        'media_type'  // Untuk membedakan apakah ini 'image' atau 'video'
    ];

    // Relasi ke User (Penulis Artikel)
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Opsi tambahan jika Anda membuat Model Comment dan Like di masa depan
    // public function comments() { return $this->hasMany(Comment::class); }
    // public function likes() { return $this->hasMany(Like::class); }
}