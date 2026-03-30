<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('integration_settings', function (Blueprint $table) {
            $table->id();
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->nullable();
            $table->string('mail_admin_address')->nullable();
            $table->string('zoho_account_hosted')->nullable();
            $table->string('zoho_accounts_base')->nullable();
            $table->string('zoho_mail_api_base')->nullable();
            $table->string('zoho_client_id')->nullable();
            $table->text('zoho_client_secret')->nullable();
            $table->string('zoho_redirect_uri')->nullable();
            $table->string('zoho_scope')->nullable();
            $table->string('zoho_mail_account_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_settings');
    }
};
