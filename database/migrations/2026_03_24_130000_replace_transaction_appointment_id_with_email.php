<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            if (Schema::hasColumn('transactions', 'appointment_id')) {
                $table->dropForeign(['appointment_id']);
                $table->dropColumn('appointment_id');
            }

            if (! Schema::hasColumn('transactions', 'appointment_email')) {
                $table->string('appointment_email')->nullable()->after('id')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            if (Schema::hasColumn('transactions', 'appointment_email')) {
                $table->dropColumn('appointment_email');
            }

            if (! Schema::hasColumn('transactions', 'appointment_id')) {
                $table->foreignId('appointment_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('appointments')
                    ->nullOnDelete();
            }
        });
    }
};
