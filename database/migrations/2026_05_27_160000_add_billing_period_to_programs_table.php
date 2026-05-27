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
            $table->string('billing_period', 20)->nullable()->after('stripe_price_id');
        });

        DB::table('programs')
            ->where('price_cents', '>', 0)
            ->whereNull('billing_period')
            ->update(['billing_period' => 'monthly']);
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table): void {
            $table->dropColumn('billing_period');
        });
    }
};
