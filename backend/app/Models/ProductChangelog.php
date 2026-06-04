<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductChangelog extends Model
{
    protected $fillable = ['product_id', 'version', 'changes', 'release_date'];

    protected $casts = [
        'release_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}