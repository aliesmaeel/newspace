<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_contents', function (Blueprint $table): void {
            $table->string('hero_banner_path')->nullable()->after('hero_cta_link');
        });
    }

    public function down(): void
    {
        Schema::table('page_contents', function (Blueprint $table): void {
            $table->dropColumn('hero_banner_path');
        });
    }
};
