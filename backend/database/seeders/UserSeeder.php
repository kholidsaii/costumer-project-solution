<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tier; // <-- Tambahkan import model Tier
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tarik data master tier dari database agar ID-nya akurat
        $freeTier = Tier::where('slug', 'free')->first();
        $silverTier = Tier::where('slug', 'silver')->first();
        $goldTier = Tier::where('slug', 'gold')->first();

        // 1. Akun Administrator
        User::updateOrCreate(
            ['email' => 'adminkerjapro@gmail.com'], // Cek agar tidak duplikat
            [
                'name' => 'Admin Kerjapro',
                'password' => Hash::make('password123'), // Default password
                'role' => 'admin',
                'phone' => '+6285117001162',
                'tier_id' => null, // Admin tidak terikat tier limitasi
            ]
        );

        // 2. Akun Customer 1 (Diberikan Tier: Gold Member)
        User::updateOrCreate(
            ['email' => 'khoirusyamilyahya23@gmail.com'],
            [
                'name' => 'Khoirusyamil Yahya',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'phone' => '+6281234567890',
                'tier_id' => $goldTier ? $goldTier->id : null, // Memasukkan relasi Gold
            ]
        );

        // 3. Akun Customer 2 (Diberikan Tier: Free Member)
        User::updateOrCreate(
            ['email' => 'kholidsaifullah@gmail.com'],
            [
                'name' => 'Kholid Saifullah',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'phone' => '+6289876543210',
                'tier_id' => $freeTier ? $freeTier->id : null, // Memasukkan relasi Free
            ]
        );
    }
}