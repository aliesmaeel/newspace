<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->unsignedInteger('price_cents')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('programs')->insert([
            [
                'slug' => 'twelve-weeks',
                'title' => '12 Weeks Commitment',
                'description' => 'Biweekly group coaching focused on idea, brand, productization, team, cash flow, and systems.',
                'image_url' => '/assets/program-12-weeks.png',
                'price_cents' => 0,
                'is_active' => true,
                'sort_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'six-months',
                'title' => '6 Months Commitment',
                'description' => 'Biweekly group coaching with retreats, leadership game sessions, and wealth-building strategies.',
                'image_url' => '/assets/program-6-months.png',
                'price_cents' => 120000,
                'is_active' => true,
                'sort_order' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'one-year',
                'title' => '1 Year Commitment',
                'description' => 'Platinum mastermind with multiple retreats, coaching sessions, and global community benefits.',
                'image_url' => '/assets/program-1-year.png',
                'price_cents' => 240000,
                'is_active' => true,
                'sort_order' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
