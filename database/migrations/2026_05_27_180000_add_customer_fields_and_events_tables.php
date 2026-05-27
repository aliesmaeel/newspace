<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interest_options', function (Blueprint $table): void {
            $table->id();
            $table->string('label');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_admin')->default(false)->after('email');
            $table->string('phone', 50)->nullable()->after('email');
            $table->foreignId('interest_option_id')->nullable()->after('phone')->constrained('interest_options')->nullOnDelete();
            $table->string('hear_about_us')->nullable()->after('interest_option_id');
        });

        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('location_type')->default('physical');
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('virtual_link')->nullable();
            $table->unsignedInteger('price_cents')->default(0);
            $table->string('stripe_product_id')->nullable();
            $table->string('stripe_price_id')->nullable();
            $table->unsignedTinyInteger('billing_interval_months')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('event_promo_codes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('uses_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'code']);
        });

        Schema::create('event_registrations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending_payment');
            $table->string('payment_status')->default('pending');
            $table->boolean('used_first_event_free')->default(false);
            $table->foreignId('event_promo_code_id')->nullable()->constrained('event_promo_codes')->nullOnDelete();
            $table->string('stripe_checkout_session_id')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('event_promo_codes');
        Schema::dropIfExists('events');
        Schema::dropIfExists('interest_options');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['is_admin', 'phone', 'interest_option_id', 'hear_about_us']);
        });
    }
};
