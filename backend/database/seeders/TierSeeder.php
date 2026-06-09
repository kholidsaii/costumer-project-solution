<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tier;

class TierSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            [
                'name' => 'Free Member', 
                'slug' => 'free', 
                'description' => 'Akses 20 produk digital',
                'price' => 0 // Harga gratis
            ],
            [
                'name' => 'Silver Member', 
                'slug' => 'silver', 
                'description' => 'Akses 1000 produk digital',
                'price' => 2000000 
            ],
            [
                'name' => 'Gold Member', 
                'slug' => 'gold', 
                'description' => 'Akses penuh ke semua ekosistem',
                'price' => 5000000 // Contoh harga Rp 450.000
            ],
        ];

        foreach ($tiers as $tier) {
            Tier::updateOrCreate(['slug' => $tier['slug']], $tier);
        }
    }
}