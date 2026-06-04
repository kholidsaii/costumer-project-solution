<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Akun Administrator
        User::updateOrCreate(
            ['email' => 'adminkerjapro@gmail.com'], // Cek agar tidak duplikat
            [
                'name' => 'Admin Kerjapro',
                'password' => Hash::make('password123'), // Default password
                'role' => 'admin',
                'phone' => '+6285117001162',
            ]
        );

        // 2. Akun Customer 1
        User::updateOrCreate(
            ['email' => 'khoirusyamilyahya23@gmail.com'],
            [
                'name' => 'Khoirusyamil Yahya',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'phone' => '+6281234567890',
            ]
        );

        // 3. Akun Customer 2
        User::updateOrCreate(
            ['email' => 'kholidsaifullah@gmail.com'],
            [
                'name' => 'Kholid Saifullah',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'phone' => '+6289876543210',
            ]
        );
    }
}