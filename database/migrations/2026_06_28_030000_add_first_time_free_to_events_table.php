<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->boolean('first_time_free')->default(false)->after('is_active');
        });

        Schema::table('event_registration_histories', function (Blueprint $table): void {
            $table->unsignedBigInteger('event_type_id')->nullable()->after('event_type');
        });
    }

    public function down(): void
    {
        Schema::table('event_registration_histories', function (Blueprint $table): void {
            $table->dropColumn('event_type_id');
        });

        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn('first_time_free');
        });
    }
};
