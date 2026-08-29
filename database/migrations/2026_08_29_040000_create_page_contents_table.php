<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_contents', function (Blueprint $table): void {
            $table->id();
            $table->string('hero_eyebrow')->nullable();
            $table->string('hero_title_line_1')->nullable();
            $table->string('hero_title_line_2')->nullable();
            $table->text('hero_lead')->nullable();
            $table->string('hero_cta_text')->nullable();
            $table->string('hero_cta_link')->nullable();
            $table->string('about_eyebrow')->nullable();
            $table->text('about_heading')->nullable();
            $table->text('about_lead')->nullable();
            $table->string('who_eyebrow')->nullable();
            $table->text('who_heading')->nullable();
            $table->text('who_lead')->nullable();
            $table->json('audiences')->nullable();
            $table->string('belief_eyebrow')->nullable();
            $table->text('belief_lead')->nullable();
            $table->string('why_now_eyebrow')->nullable();
            $table->text('why_now_heading')->nullable();
            $table->json('why_now_insights')->nullable();
            $table->text('why_now_source')->nullable();
            $table->string('transform_eyebrow')->nullable();
            $table->json('transforms')->nullable();
            $table->string('process_eyebrow')->nullable();
            $table->text('process_heading')->nullable();
            $table->json('process_steps')->nullable();
            $table->string('why_eyebrow')->nullable();
            $table->text('why_tagline')->nullable();
            $table->text('why_paragraph_1')->nullable();
            $table->text('why_paragraph_2')->nullable();
            $table->text('why_paragraph_3')->nullable();
            $table->string('regions_eyebrow')->nullable();
            $table->text('regions_lead')->nullable();
            $table->json('regions')->nullable();
            $table->string('founder_eyebrow')->nullable();
            $table->string('founder_name')->nullable();
            $table->string('founder_role')->nullable();
            $table->text('founder_hook')->nullable();
            $table->text('founder_paragraph_1')->nullable();
            $table->text('founder_paragraph_2')->nullable();
            $table->text('founder_paragraph_3')->nullable();
            $table->json('founder_credentials')->nullable();
            $table->text('founder_quote')->nullable();
            $table->string('founder_quote_footer')->nullable();
            $table->string('cta_eyebrow')->nullable();
            $table->text('cta_heading')->nullable();
            $table->text('cta_body')->nullable();
            $table->string('cta_button_text')->nullable();
            $table->string('cta_button_link')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_contents');
    }
};
