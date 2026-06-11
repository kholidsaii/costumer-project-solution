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
        Schema::table('users', function (Blueprint $table) {
            // Melacak jumlah download digital
            $table->integer('digital_downloads_count')->default(0);
        });

        Schema::table('orders', function (Blueprint $table) {
            // Kolom untuk Software (Bukti Bayar)
            $table->string('payment_method')->nullable();
            $table->string('payment_proof')->nullable();
            
            // Kolom untuk Fisik (Pengiriman)
            $table->text('shipping_address')->nullable();
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->string('courier')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_and_orders', function (Blueprint $table) {
            //
        });
    }
};
