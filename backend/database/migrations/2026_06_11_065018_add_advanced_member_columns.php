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
        Schema::table('tiers', function (Blueprint $table) {
            $table->integer('digital_limit')->default(0);
            $table->boolean('software_access')->default(false);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('file_path')->nullable(); // Untuk menyimpan file zip/pdf/dll
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('software_link')->nullable(); // Link akses setelah di-approve
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
