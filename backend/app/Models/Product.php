<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'overview', 'price', 'category', 'is_active','image'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relasi ke Fitur Produk
    public function features()
    {
        return $this->hasMany(ProductFeature::class);
    }

    // Relasi ke Screenshots Produk
    public function screenshots()
    {
        return $this->hasMany(ProductScreenshot::class);
    }

    // Relasi ke FAQ Produk
    public function faqs()
    {
        return $this->hasMany(ProductFaq::class);
    }

    // Relasi ke Changelog Produk
    public function changelogs()
    {
        return $this->hasMany(ProductChangelog::class);
    }

    // Relasi ke Review User
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}