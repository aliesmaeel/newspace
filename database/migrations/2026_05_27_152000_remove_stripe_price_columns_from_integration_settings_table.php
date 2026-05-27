<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('integration_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'stripe_price_12_weeks',
                'stripe_price_6_months',
                'stripe_price_1_year',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('integration_settings', function (Blueprint $table): void {
            $table->unsignedInteger('stripe_price_12_weeks')->nullable()->after('stripe_webhook_secret');
            $table->unsignedInteger('stripe_price_6_months')->nullable()->after('stripe_price_12_weeks');
            $table->unsignedInteger('stripe_price_1_year')->nullable()->after('stripe_price_6_months');
        });
    }
};
