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
                'description' => 'Akses dasar ke katalog digital. Cocok untuk pemula.',
                'price' => 0 
            ],
            [
                'name' => 'Silver Member', 
                'slug' => 'silver', 
                'description' => 'Akses ke 1000+ produk digital premium tanpa watermark.',
                'price' => 2000000 
            ],
            [
                'name' => 'Gold Member', 
                'slug' => 'gold', 
                'description' => 'Akses VIP ke semua ekosistem, software khusus, dan support prioritas.',
                'price' => 5000000
            ],
        ];

        foreach ($tiers as $tier) {
            Tier::updateOrCreate(
                ['slug' => $tier['slug']], // Cari berdasarkan slug
                [
                    'name' => $tier['name'],
                    'description' => $tier['description'],
                    'price' => $tier['price']
                ] // Update/Create atribut lainnya
            );
        }
    }
}