<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('events', function (Blueprint $table): void {
            $table->foreignId('event_type_id')->nullable()->after('title')->constrained('event_types')->nullOnDelete();
        });

        Schema::table('event_registration_histories', function (Blueprint $table): void {
            $table->string('event_type')->nullable()->after('event_title');
        });
    }

    public function down(): void
    {
        Schema::table('event_registration_histories', function (Blueprint $table): void {
            $table->dropColumn('event_type');
        });

        Schema::table('events', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('event_type_id');
        });

        Schema::dropIfExists('event_types');
    }
};
