<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_contents', function (Blueprint $table): void {
            $table->string('mission_banner_path')->nullable()->after('cta_button_link');
            $table->string('mission_eyebrow')->nullable()->after('mission_banner_path');
            $table->string('mission_heading')->nullable()->after('mission_eyebrow');
            $table->text('mission_paragraph_1')->nullable()->after('mission_heading');
            $table->text('mission_paragraph_2')->nullable()->after('mission_paragraph_1');
            $table->text('mission_closing')->nullable()->after('mission_paragraph_2');
            $table->string('mission_cta_eyebrow')->nullable()->after('mission_closing');
            $table->text('mission_cta_heading')->nullable()->after('mission_cta_eyebrow');
            $table->text('mission_cta_body')->nullable()->after('mission_cta_heading');
            $table->string('mission_cta_button_text')->nullable()->after('mission_cta_body');
            $table->string('mission_cta_button_link')->nullable()->after('mission_cta_button_text');
        });
    }

    public function down(): void
    {
        Schema::table('page_contents', function (Blueprint $table): void {
            $table->dropColumn([
                'mission_banner_path',
                'mission_eyebrow',
                'mission_heading',
                'mission_paragraph_1',
                'mission_paragraph_2',
                'mission_closing',
                'mission_cta_eyebrow',
                'mission_cta_heading',
                'mission_cta_body',
                'mission_cta_button_text',
                'mission_cta_button_link',
            ]);
        });
    }
};
