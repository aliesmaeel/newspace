<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('event_occurrences')) {
            Schema::create('event_occurrences', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('event_id')->constrained()->cascadeOnDelete();
                $table->string('label')->nullable();
                $table->string('location_type')->default('physical');
                $table->string('address')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('virtual_link')->nullable();
                $table->unsignedInteger('price_cents')->default(0);
                $table->string('stripe_product_id')->nullable();
                $table->string('stripe_price_id')->nullable();
                $table->dateTime('starts_at');
                $table->dateTime('ends_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        $occurrenceByEventId = DB::table('event_occurrences')
            ->pluck('id', 'event_id')
            ->mapWithKeys(fn ($id, $eventId) => [(int) $eventId => (int) $id])
            ->all();

        if ($occurrenceByEventId === []) {
            foreach (DB::table('events')->orderBy('id')->get() as $event) {
                $occurrenceId = DB::table('event_occurrences')->insertGetId([
                    'event_id' => $event->id,
                    'label' => null,
                    'location_type' => $event->location_type ?? 'physical',
                    'address' => $event->address,
                    'latitude' => $event->latitude,
                    'longitude' => $event->longitude,
                    'virtual_link' => $event->virtual_link,
                    'price_cents' => (int) ($event->price_cents ?? 0),
                    'stripe_product_id' => $event->stripe_product_id,
                    'stripe_price_id' => $event->stripe_price_id,
                    'starts_at' => $event->starts_at,
                    'ends_at' => $event->ends_at,
                    'is_active' => (bool) ($event->is_active ?? true),
                    'sort_order' => (int) ($event->sort_order ?? 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $occurrenceByEventId[(int) $event->id] = $occurrenceId;
            }
        }

        if (! Schema::hasColumn('event_promo_codes', 'event_occurrence_id')) {
            Schema::table('event_promo_codes', function (Blueprint $table): void {
                $table->foreignId('event_occurrence_id')->nullable()->after('event_id')->constrained('event_occurrences')->cascadeOnDelete();
            });
        }

        foreach (DB::table('event_promo_codes')->whereNull('event_occurrence_id')->get() as $promo) {
            $occurrenceId = $occurrenceByEventId[(int) $promo->event_id] ?? null;
            if ($occurrenceId) {
                DB::table('event_promo_codes')->where('id', $promo->id)->update([
                    'event_occurrence_id' => $occurrenceId,
                ]);
            }
        }

        $promoIndexes = collect(DB::select('SHOW INDEX FROM event_promo_codes'))
            ->pluck('Key_name')
            ->unique()
            ->all();

        if (in_array('event_promo_codes_event_id_code_unique', $promoIndexes, true)) {
            Schema::table('event_promo_codes', function (Blueprint $table): void {
                $table->index('event_id');
            });
            Schema::table('event_promo_codes', function (Blueprint $table): void {
                $table->dropUnique(['event_id', 'code']);
            });
        }

        if (! in_array('event_promo_codes_event_occurrence_id_code_unique', $promoIndexes, true)) {
            Schema::table('event_promo_codes', function (Blueprint $table): void {
                $table->unique(['event_occurrence_id', 'code']);
            });
        }

        if (! Schema::hasColumn('event_registrations', 'event_occurrence_id')) {
            Schema::table('event_registrations', function (Blueprint $table): void {
                $table->foreignId('event_occurrence_id')->nullable()->after('event_id')->constrained('event_occurrences')->cascadeOnDelete();
            });
        }

        foreach (DB::table('event_registrations')->whereNull('event_occurrence_id')->get() as $registration) {
            $occurrenceId = $occurrenceByEventId[(int) $registration->event_id] ?? null;
            if ($occurrenceId) {
                DB::table('event_registrations')->where('id', $registration->id)->update([
                    'event_occurrence_id' => $occurrenceId,
                ]);
            }
        }

        $regIndexes = collect(DB::select('SHOW INDEX FROM event_registrations'))
            ->pluck('Key_name')
            ->unique()
            ->all();

        if (in_array('event_registrations_event_id_user_id_unique', $regIndexes, true)) {
            Schema::table('event_registrations', function (Blueprint $table): void {
                $table->index('event_id');
            });
            Schema::table('event_registrations', function (Blueprint $table): void {
                $table->dropUnique(['event_id', 'user_id']);
            });
        }

        if (! in_array('event_registrations_event_occurrence_id_user_id_unique', $regIndexes, true)) {
            Schema::table('event_registrations', function (Blueprint $table): void {
                $table->unique(['event_occurrence_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table): void {
            $table->dropUnique(['event_occurrence_id', 'user_id']);
        });

        Schema::table('event_registrations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('event_occurrence_id');
            $table->unique(['event_id', 'user_id']);
        });

        Schema::table('event_promo_codes', function (Blueprint $table): void {
            $table->dropUnique(['event_occurrence_id', 'code']);
        });

        Schema::table('event_promo_codes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('event_occurrence_id');
            $table->unique(['event_id', 'code']);
        });

        Schema::dropIfExists('event_occurrences');
    }
};
