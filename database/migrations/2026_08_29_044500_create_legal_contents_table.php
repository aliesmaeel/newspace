<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_contents', function (Blueprint $table): void {
            $table->id();
            $table->string('controller_name')->nullable();
            $table->text('controller_address')->nullable();
            $table->string('privacy_contact_email')->nullable();
            $table->longText('privacy_policy_html')->nullable();
            $table->longText('cookie_policy_html')->nullable();
            $table->text('cookie_notice_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_contents');
    }
};
