<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dateTime('starts_at')->nullable()->change();
            $table->string('location_type')->nullable()->change();
            $table->unsignedInteger('price_cents')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dateTime('starts_at')->nullable(false)->change();
            $table->string('location_type')->nullable(false)->change();
            $table->unsignedInteger('price_cents')->nullable(false)->change();
        });
    }
};
