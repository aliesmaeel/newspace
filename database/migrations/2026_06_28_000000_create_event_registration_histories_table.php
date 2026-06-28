<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_registration_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_registration_id')->nullable()->constrained('event_registrations')->nullOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->string('event_title');
            $table->dateTime('event_starts_at')->nullable();
            $table->string('event_location_type')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('status')->default('confirmed');
            $table->string('payment_status')->default('paid');
            $table->unsignedInteger('amount_cents')->default(0);
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();

            $table->index('event_starts_at');
            $table->index('registered_at');
        });

        // Backfill from existing confirmed registrations so past sign-ups appear immediately.
        $registrations = DB::table('event_registrations')
            ->leftJoin('events', 'events.id', '=', 'event_registrations.event_id')
            ->leftJoin('users', 'users.id', '=', 'event_registrations.user_id')
            ->where('event_registrations.status', 'confirmed')
            ->select([
                'event_registrations.id as event_registration_id',
                'event_registrations.event_id',
                'event_registrations.user_id',
                'event_registrations.status',
                'event_registrations.payment_status',
                'event_registrations.registered_at',
                'event_registrations.created_at',
                'events.title as event_title',
                'events.starts_at as event_starts_at',
                'events.location_type as event_location_type',
                'events.price_cents as amount_cents',
                'users.name as user_name',
                'users.email as user_email',
            ])
            ->get();

        $now = now();

        foreach ($registrations as $registration) {
            DB::table('event_registration_histories')->insert([
                'event_registration_id' => $registration->event_registration_id,
                'event_id' => $registration->event_id,
                'event_title' => $registration->event_title ?? 'Deleted event',
                'event_starts_at' => $registration->event_starts_at,
                'event_location_type' => $registration->event_location_type,
                'user_id' => $registration->user_id,
                'user_name' => $registration->user_name,
                'user_email' => $registration->user_email,
                'status' => $registration->status,
                'payment_status' => $registration->payment_status,
                'amount_cents' => (int) ($registration->amount_cents ?? 0),
                'registered_at' => $registration->registered_at ?? $registration->created_at,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registration_histories');
    }
};
