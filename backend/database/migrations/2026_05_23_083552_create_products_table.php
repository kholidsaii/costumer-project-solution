<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // Untuk URL ramah SEO
            $table->text('description'); // Deskripsi singkat
            $table->longText('overview')->nullable(); // Deskripsi lengkap untuk tab Overview (dibuat nullable untuk berjaga-jaga)
            
            $table->string('category'); // Menampung nilai label (Software, Produk Digital, Produk Fisik)
            
            // --- PENYESUAIAN BARU ---
            $table->string('product_type'); // Menampung logika backend: software, digital, physical
            $table->decimal('price', 15, 2)->default(0); // Memastikan ada default value
            $table->string('access_tier')->default('all'); // Akses: gold, silver, free, all
            $table->integer('stock')->default(0); // Mengubah 'quantity' menjadi 'stock' agar sesuai dengan Controller
            // -----------------------

            $table->boolean('is_active')->default(true);
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};