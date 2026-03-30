<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_unavailabilities', function (Blueprint $table): void {
            $table->id();
            $table->date('blocked_date');
            $table->boolean('is_full_day')->default(false);
            $table->time('slot_time')->nullable();
            $table->timestamps();

            $table->index(['blocked_date', 'is_full_day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_unavailabilities');
    }
};
