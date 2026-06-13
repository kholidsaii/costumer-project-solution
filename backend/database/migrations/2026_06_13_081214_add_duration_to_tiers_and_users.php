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
            $table->integer('duration_in_months')->default(12)->after('price'); // Durasi dalam bulan
        });
        Schema::table('users', function (Blueprint $table) {
            $table->date('tier_expires_at')->nullable()->after('tier_id'); // Batas waktu member
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tiers_and_users', function (Blueprint $table) {
            //
        });
    }
};
