<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_promo_codes', function (Blueprint $table): void {
            $table->unsignedTinyInteger('discount_percentage')->default(100)->after('code');
            $table->string('stripe_coupon_id')->nullable()->after('discount_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('event_promo_codes', function (Blueprint $table): void {
            $table->dropColumn(['discount_percentage', 'stripe_coupon_id']);
        });
    }
};
