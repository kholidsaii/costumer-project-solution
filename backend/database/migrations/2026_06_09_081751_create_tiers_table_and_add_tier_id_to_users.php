<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat tabel master tiers
        Schema::create('tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: 'Free Member'
            $table->string('slug')->unique(); // Contoh: 'free'
            $table->text('description')->nullable();
            
            // PERBAIKAN: Tambahkan kolom price untuk harga langganan/lisensi dasar
            $table->decimal('price', 15, 2)->default(0); 
            
            $table->timestamps();
        });

        // 2. Tambahkan relasi tier_id ke tabel users
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom tier lama jika sebelumnya Anda sempat membuatnya
            if (Schema::hasColumn('users', 'tier')) {
                $table->dropColumn('tier');
            }
            
            $table->foreignId('tier_id')->nullable()->constrained('tiers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tier_id']);
            $table->dropColumn('tier_id');
        });
        Schema::dropIfExists('tiers');
    }
};