<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Membuat Akun Administrator
        User::create([
            'name' => 'Super Admin Kerjapro',
            'email' => 'adminkerjapro@gmail.com',
            'password' => Hash::make('admin123'), // Password untuk login
            'role' => 'admin',
            'phone' => '+628111111111',
            'date_of_birth' => '1990-01-01',
            'gender' => 'Male',
            'country' => 'Indonesia',
            'address' => 'Gedung Kerjapro Pusat, Jakarta',
        ]);

        // 2. Membuat Akun Customer (Pelanggan)
        User::create([
            'name' => 'Khoirusyamil',
            'email' => 'khoirusyamilyahya23@gmail.com',
            'password' => Hash::make('syamil123'), // Password untuk login
            'role' => 'customer',
            'phone' => '+628222222222',
            'date_of_birth' => '1995-05-15',
            'gender' => 'Male',
            'country' => 'Indonesia',
            'address' => 'Jl. Masjid Bendungan 2',
        ]);
    }
}