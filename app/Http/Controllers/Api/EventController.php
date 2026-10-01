<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\EventRegistration;
use App\Models\EventRegistrationHistory;
use App\Services\EventRegistrationService;
use App\Services\StripeEventCheckoutService;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EventController extends Controller
{
    public function index(): JsonResponse
    {
        $events = Event::query()
            ->where('is_active', true)
            ->whereHas('occurrences', function ($query): void {
                $query->where('is_active', true)
                    ->where('starts_at', '>=', now()->subDay());
            })
            ->with(['occurrences' => function ($query): void {
                $query->where('is_active', true)
                    ->where('starts_at', '>=', now()->subDay())
                    ->orderBy('starts_at')
                    ->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get()
            ->sortBy(fn (Event $event) => $event->occurrences->min('starts_at'))
            ->values()
            ->map(fn (Event $event): array => $this->listPayload($event));

        return response()->json(['events' => $events]);
    }

    public function show(string $slug): JsonResponse
    {
        $event = Event::query()
            ->with(['eventType', 'occurrences' => function ($query): void {
                $query->where('is_active', true)
                    ->where('starts_at', '>=', now()->subDay())
                    ->orderBy('starts_at')
                    ->orderBy('sort_order');
            }])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $user = request()->user();
        $registrationsByOccurrence = collect();

        if ($user) {
            $registrations = EventRegistration::query()
                ->where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->get();

            foreach ($registrations as $registration) {
                try {
                    app(StripeEventCheckoutService::class)->syncRegistrationPayment($registration);
                } catch (\Throwable $e) {
                    report($e);
                }

                $registration->refresh();
            }

            $registrationsByOccurrence = $registrations->keyBy('event_occurrence_id');
        }

        $payload = $this->detailPayload($event, $registrationsByOccurrence);
        $payload['first_time_free'] = (bool) $event->first_time_free;
        $payload['event_type_name'] = $event->eventType?->name;

        if ($user) {
            $hasAttendedType = $event->event_type_id !== null
                && EventRegistrationHistory::query()
                    ->where('user_id', $user->id)
                    ->where('event_type_id', $event->event_type_id)
                    ->where('status', 'confirmed')
                    ->exists();

            $payload['has_attended_before'] = $hasAttendedType;
            $payload['first_time_free_eligible'] = (bool) $event->first_time_free && ! $hasAttendedType;
        } else {
            $payload['first_time_free_eligible'] = (bool) $event->first_time_free;
        }

        return response()->json(['event' => $payload]);
    }

    public function register(Request $request, string $slug, EventRegistrationService $registrations): JsonResponse
    {
        $event = Event::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'occurrence_id' => ['required', 'integer'],
            'promo_code' => ['nullable', 'string', 'max:64'],
            'return_base_url' => ['nullable', 'url'],
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'You must register or log in first.'], 401);
        }

        $occurrence = EventOccurrence::query()
            ->where('event_id', $event->id)
            ->whereKey($validated['occurrence_id'])
            ->where('is_active', true)
            ->firstOrFail();

        try {
            $result = $registrations->register(
                $user,
                $occurrence,
                $validated['promo_code'] ?? null,
                $validated['return_base_url'] ?? null,
            );
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result);
    }

    /**
     * @return array<string, mixed>
     */
    private function listPayload(Event $event): array
    {
        $imageUrl = $this->imageUrl($event);
        $occurrences = $event->occurrences;
        $minPrice = $occurrences->min('price_cents');
        $sessionCount = $occurrences->count();

        $summaryParts = [];
        if ($sessionCount > 0) {
            $summaryParts[] = $sessionCount === 1 ? '1 session' : "{$sessionCount} sessions";
        }
        if ($minPrice !== null) {
            $summaryParts[] = ((int) $minPrice <= 0)
                ? 'Free'
                : 'From '.Money::formatCents((int) $minPrice);
        }

        return [
            'slug' => $event->slug,
            'title' => $event->title,
            'description' => $event->description,
            'image_url' => $imageUrl,
            'session_count' => $sessionCount,
            'summary_label' => implode(' · ', $summaryParts),
        ];
    }

    /**
     * @param  Collection<int|string, EventRegistration>  $registrationsByOccurrence
     * @return array<string, mixed>
     */
    private function detailPayload(Event $event, $registrationsByOccurrence): array
    {
        return [
            'slug' => $event->slug,
            'title' => $event->title,
            'description' => $event->description,
            'image_url' => $this->imageUrl($event),
            'occurrences' => $event->occurrences->map(function (EventOccurrence $occurrence) use ($registrationsByOccurrence): array {
                $registration = $registrationsByOccurrence->get($occurrence->id);

                return $this->occurrencePayload($occurrence, $registration);
            })->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function occurrencePayload(EventOccurrence $occurrence, ?EventRegistration $registration = null): array
    {
        $data = [
            'id' => $occurrence->id,
            'label' => $occurrence->label,
            'display_label' => $occurrence->displayLabel(),
            'location_type' => $occurrence->location_type,
            'location_label' => $occurrence->locationLabel(),
            'address' => $occurrence->address,
            'latitude' => $occurrence->latitude,
            'longitude' => $occurrence->longitude,
            'map_url' => $occurrence->mapUrl(),
            'price_cents' => (int) $occurrence->price_cents,
            'price_label' => $occurrence->formattedPriceLabel(),
            'starts_at' => $occurrence->starts_at?->toIso8601String(),
            'ends_at' => $occurrence->ends_at?->toIso8601String(),
            'has_virtual_meeting' => $occurrence->isVirtual() && filled($occurrence->virtual_link),
            'user_registration' => $registration ? [
                'status' => $registration->status,
                'payment_status' => $registration->payment_status,
            ] : null,
        ];

        if ($occurrence->isVirtual() && $registration && $this->canViewVirtualLink($registration)) {
            $data['virtual_link'] = $occurrence->virtual_link;
        }

        return $data;
    }

    private function imageUrl(Event $event): ?string
    {
        $imageUrl = $event->image_url;
        if ($imageUrl && ! str_starts_with($imageUrl, 'http') && ! str_starts_with($imageUrl, '/')) {
            return rtrim((string) config('app.url'), '/').'/storage/'.ltrim($imageUrl, '/');
        }

        return $imageUrl;
    }

    private function canViewVirtualLink(EventRegistration $registration): bool
    {
        return $registration->status === 'confirmed'
            && $registration->payment_status === 'paid';
    }
}
