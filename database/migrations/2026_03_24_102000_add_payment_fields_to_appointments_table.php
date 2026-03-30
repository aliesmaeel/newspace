<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE appointments MODIFY status ENUM('pending','pending_payment','approved','rejected','passed') NOT NULL DEFAULT 'pending'");

        Schema::table('appointments', function (Blueprint $table): void {
            $table->string('program_plan_key')->nullable()->after('message');
            $table->string('program_plan_name')->nullable()->after('program_plan_key');
            $table->boolean('requires_payment')->default(false)->after('program_plan_name');
            $table->enum('payment_status', ['unpaid', 'pending', 'paid', 'failed'])->default('unpaid')->after('requires_payment');
            $table->string('stripe_checkout_session_id')->nullable()->unique()->after('payment_status');
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_checkout_session_id');
            $table->timestamp('paid_at')->nullable()->after('stripe_payment_intent_id');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropColumn([
                'program_plan_key',
                'program_plan_name',
                'requires_payment',
                'payment_status',
                'stripe_checkout_session_id',
                'stripe_payment_intent_id',
                'paid_at',
            ]);
        });

        DB::statement("ALTER TABLE appointments MODIFY status ENUM('pending','approved','rejected','passed') NOT NULL DEFAULT 'pending'");
    }
};
