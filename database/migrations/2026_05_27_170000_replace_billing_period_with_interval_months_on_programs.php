<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table): void {
            $table->string('stripe_product_id')->nullable()->after('price_cents');
            $table->unsignedTinyInteger('billing_interval_months')->nullable()->after('stripe_price_id');
        });

        DB::table('programs')->where('billing_period', 'monthly')->update(['billing_interval_months' => 1]);
        DB::table('programs')->where('billing_period', 'yearly')->update(['billing_interval_months' => 12]);

        Schema::table('programs', function (Blueprint $table): void {
            $table->dropColumn('billing_period');
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table): void {
            $table->string('billing_period', 20)->nullable()->after('stripe_price_id');
        });

        DB::table('programs')->where('billing_interval_months', 1)->update(['billing_period' => 'monthly']);
        DB::table('programs')->where('billing_interval_months', 12)->update(['billing_period' => 'yearly']);

        Schema::table('programs', function (Blueprint $table): void {
            $table->dropColumn(['stripe_product_id', 'billing_interval_months']);
        });
    }
};
