<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->string('gateway')->default('stripe');
            $table->string('type')->default('checkout');
            $table->string('status')->default('pending');
            $table->unsignedInteger('amount_cents')->default(0);
            $table->string('currency', 10)->default('usd');
            $table->string('external_id')->nullable()->index();
            $table->string('payment_intent_id')->nullable()->index();
            $table->json('payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
