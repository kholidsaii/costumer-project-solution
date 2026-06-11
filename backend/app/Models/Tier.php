<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tier extends Model
{
    use HasFactory;
    
    // PERBAIKAN: Tambahkan 'price' ke fillable
    protected $fillable = ['name', 'slug', 'description', 'price', 'digital_limit', 'software_access'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}