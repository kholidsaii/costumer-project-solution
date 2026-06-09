<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TierSeeder::class, // <-- Master tier harus dieksekusi lebih dulu
            UserSeeder::class, // <-- Baru user dieksekusi
        ]);
    }
}