<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventRegistrationHistory;
use App\Services\EventRegistrationService;
use App\Services\StripeEventCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(): JsonResponse
    {
        $events = Event::query()
            ->where('is_active', true)
            ->where('starts_at', '>=', now()->subDay())
            ->orderBy('starts_at')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Event $event): array => $this->eventPayload($event));

        return response()->json(['events' => $events]);
    }

    public function show(string $slug): JsonResponse
    {
        $event = Event::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $user = request()->user();
        $registration = null;
        if ($user) {
            $registration = EventRegistration::query()
                ->where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->first();

            if ($registration) {
                app(StripeEventCheckoutService::class)->syncRegistrationPayment($registration);
                $registration->refresh();
            }
        }

        $payload = $this->eventPayload($event, true, $registration);

        if ($user) {
            $payload['user_registration'] = $registration ? [
                'status' => $registration->status,
                'payment_status' => $registration->payment_status,
            ] : null;

            $hasAttendedType = $event->event_type_id !== null
                && EventRegistrationHistory::query()
                    ->where('user_id', $user->id)
                    ->where('event_type_id', $event->event_type_id)
                    ->where('status', 'confirmed')
                    ->exists();

            $payload['has_attended_before'] = $hasAttendedType;
            $payload['first_time_free_eligible'] = (bool) $event->first_time_free && ! $hasAttendedType;
        }

        return response()->json(['event' => $payload]);
    }

    public function register(Request $request, string $slug, EventRegistrationService $registrations): JsonResponse
    {
        $event = Event::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'promo_code' => ['nullable', 'string', 'max:64'],
            'return_base_url' => ['nullable', 'url'],
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'You must register or log in first.'], 401);
        }

        try {
            $result = $registrations->register(
                $user,
                $event,
                $validated['promo_code'] ?? null,
                $validated['return_base_url'] ?? null,
            );
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result);
    }

    private function eventPayload(Event $event, bool $detailed = false, ?EventRegistration $registration = null): array
    {
        $imageUrl = $event->image_url;
        if ($imageUrl && ! str_starts_with($imageUrl, 'http') && ! str_starts_with($imageUrl, '/')) {
            $imageUrl = rtrim((string) config('app.url'), '/') . '/storage/' . ltrim($imageUrl, '/');
        }

        $data = [
            'slug' => $event->slug,
            'title' => $event->title,
            'description' => $event->description,
            'image_url' => $imageUrl,
            'location_type' => $event->location_type,
            'location_label' => $this->locationLabel($event),
            'price_cents' => (int) $event->price_cents,
            'price_label' => $event->formattedPriceLabel(),
            'starts_at' => $event->starts_at?->toIso8601String(),
            'ends_at' => $event->ends_at?->toIso8601String(),
        ];

        if ($detailed) {
            $data['address'] = $event->address;
            $data['latitude'] = $event->latitude;
            $data['longitude'] = $event->longitude;
            $data['has_virtual_meeting'] = $event->isVirtual() && filled($event->virtual_link);
            if ($event->isVirtual() && $registration && $this->canViewVirtualLink($registration)) {
                $data['virtual_link'] = $event->virtual_link;
            }
            $data['map_url'] = ($event->latitude && $event->longitude)
                ? 'https://www.google.com/maps?q=' . $event->latitude . ',' . $event->longitude
                : ($event->address ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($event->address) : null);
        }

        return $data;
    }

    private function locationLabel(Event $event): string
    {
        return match ($event->location_type) {
            'virtual' => 'Virtual',
            'physical' => 'In person',
            default => 'In person',
        };
    }

    private function canViewVirtualLink(EventRegistration $registration): bool
    {
        return $registration->status === 'confirmed'
            && $registration->payment_status === 'paid';
    }
}
