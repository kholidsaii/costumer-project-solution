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
        Schema::create('tier_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tier_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_number')->unique();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method');
            $table->string('payment_proof');
            $table->string('status')->default('pending'); // pending, paid, rejected
            $table->text('reject_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tier_transactions');
    }
};
