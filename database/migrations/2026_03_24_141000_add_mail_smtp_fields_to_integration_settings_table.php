<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('integration_settings', function (Blueprint $table): void {
            $table->string('mail_mailer')->nullable()->after('mail_admin_address');
            $table->string('mail_scheme')->nullable()->after('mail_mailer');
            $table->string('mail_host')->nullable()->after('mail_scheme');
            $table->unsignedInteger('mail_port')->nullable()->after('mail_host');
            $table->string('mail_username')->nullable()->after('mail_port');
            $table->text('mail_password')->nullable()->after('mail_username');
        });
    }

    public function down(): void
    {
        Schema::table('integration_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'mail_mailer',
                'mail_scheme',
                'mail_host',
                'mail_port',
                'mail_username',
                'mail_password',
            ]);
        });
    }
};
