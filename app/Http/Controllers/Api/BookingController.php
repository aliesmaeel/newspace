<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\BookingUnavailability;
use App\Models\Transaction;
use App\Support\BookingTimeSlots;
use App\Mail\AdminBookingCreatedMail;
use App\Mail\CustomerBookingApprovedMail;
use App\Services\IntegrationSettingsService;
use App\Services\StripeCheckoutService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    private const PAID_PROGRAMS = ['twelve-weeks', 'six-months', 'one-year'];

    public function availability(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $date = Carbon::createFromFormat('Y-m-d', $validated['date']);
        $slots = BookingTimeSlots::labels();

        $bookedTimes = Appointment::query()
            ->whereDate('appointment_date', $date->toDateString())
            ->where(function ($query): void {
                $query
                    ->whereIn('status', ['pending', 'approved', 'passed'])
                    ->orWhere(function ($q): void {
                        $q->where('status', 'pending_payment')
                            ->where('created_at', '>=', now()->subMinutes(30));
                    });
            })
            ->pluck('appointment_time')
            ->map(fn ($time) => Carbon::parse($time)->format('g:i A'))
            ->values()
            ->all();

        $unavailableTimes = BookingUnavailability::blockedSlotLabelsForDate($date->toDateString());

        $takenOrBlocked = array_values(array_unique([...$bookedTimes, ...$unavailableTimes]));
        $available = array_values(array_diff($slots, $takenOrBlocked));

        return response()->json([
            'slots' => $slots,
            'booked' => $bookedTimes,
            'unavailable' => $unavailableTimes,
            'available' => $available,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:2000'],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'string'],
            'program_plan_key' => ['nullable', 'string'],
        ]);

        if (! in_array($validated['time'], BookingTimeSlots::labels(), true)) {
            throw ValidationException::withMessages([
                'time' => 'Selected time slot is invalid.',
            ]);
        }

        $appointmentAt = Carbon::createFromFormat('Y-m-d g:i A', "{$validated['date']} {$validated['time']}");

        $this->ensureSlotNotAdminBlocked($validated['date'], $validated['time']);

        if (in_array($appointmentAt->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY], true)) {
            throw ValidationException::withMessages([
                'date' => 'Saturday and Sunday are not available.',
            ]);
        }

        $this->ensureAppointmentNotInPast($appointmentAt);

        $alreadyBooked = Appointment::query()
            ->where('appointment_at', $appointmentAt)
            ->where(function ($query): void {
                $query
                    ->whereIn('status', ['pending', 'approved', 'passed'])
                    ->orWhere(function ($q): void {
                        $q->where('status', 'pending_payment')
                            ->where('created_at', '>=', now()->subMinutes(30));
                    });
            })
            ->exists();

        if ($alreadyBooked) {
            return response()->json([
                'message' => 'This time slot is already booked.',
            ], 409);
        }

        $planKey = $validated['program_plan_key'] ?? null;
        $requiresPayment = in_array($planKey, self::PAID_PROGRAMS, true);

        $appointment = Appointment::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'message' => $validated['message'] ?? null,
            'appointment_at' => $appointmentAt,
            'appointment_date' => $appointmentAt->toDateString(),
            'appointment_time' => $appointmentAt->format('H:i:s'),
            'program_plan_key' => $planKey,
            'program_plan_name' => $this->programPlanName($planKey),
            'requires_payment' => $requiresPayment,
            'payment_status' => $requiresPayment ? 'pending' : 'unpaid',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        if ($requiresPayment) {
            return response()->json([
                'message' => 'Payment is required for this program. Use checkout endpoint.',
                'appointment_id' => $appointment->id,
            ], 422);
        }

        $adminEmail = app(IntegrationSettingsService::class)->mailAdminAddress();
        if ($adminEmail) {
            $dashboardUrl = rtrim(config('app.url'), '/') . "/admin/appointments/{$appointment->id}/edit";
            try {
                Mail::to($adminEmail)->send(
                    new AdminBookingCreatedMail($appointment, $dashboardUrl, 'New free appointment booked')
                );
            } catch (Throwable $e) {
                report($e);
            }
        }

        try {
            Mail::to($appointment->email)->send(new CustomerBookingApprovedMail($appointment));
        } catch (Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => 'Booking created successfully.',
            'appointment_id' => $appointment->id,
        ], 201);
    }

    public function checkoutSession(Request $request, StripeCheckoutService $stripe)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:2000'],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'string'],
            'program_plan_key' => ['required', 'in:twelve-weeks,six-months,one-year'],
            'return_base_url' => ['nullable', 'url'],
        ]);

        if (! in_array($validated['time'], BookingTimeSlots::labels(), true)) {
            throw ValidationException::withMessages([
                'time' => 'Selected time slot is invalid.',
            ]);
        }

        $appointmentAt = Carbon::createFromFormat('Y-m-d g:i A', "{$validated['date']} {$validated['time']}");

        $this->ensureSlotNotAdminBlocked($validated['date'], $validated['time']);

        $this->ensureAppointmentNotInPast($appointmentAt);

        $alreadyBooked = Appointment::query()
            ->where('appointment_at', $appointmentAt)
            ->where(function ($query): void {
                $query
                    ->whereIn('status', ['pending', 'approved', 'passed'])
                    ->orWhere(function ($q): void {
                        $q->where('status', 'pending_payment')
                            ->where('created_at', '>=', now()->subMinutes(30));
                    });
            })
            ->exists();

        if ($alreadyBooked) {
            return response()->json([
                'message' => 'This time slot is already booked.',
            ], 409);
        }

        $appointment = Appointment::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'message' => $validated['message'] ?? null,
            'appointment_at' => $appointmentAt,
            'appointment_date' => $appointmentAt->toDateString(),
            'appointment_time' => $appointmentAt->format('H:i:s'),
            'program_plan_key' => $validated['program_plan_key'],
            'program_plan_name' => $this->programPlanName($validated['program_plan_key']),
            'requires_payment' => true,
            'payment_status' => 'pending',
            'status' => 'pending_payment',
        ]);

        $returnBaseUrl = $validated['return_base_url'] ?? null;
        if ($returnBaseUrl && ! preg_match('/^https?:\\/\\//i', $returnBaseUrl)) {
            $returnBaseUrl = null;
        }

        $session = $stripe->createSession($appointment, $returnBaseUrl);

        $appointment->update([
            'stripe_checkout_session_id' => $session->id,
        ]);

        $catalog = $stripe->planCatalog();
        $plan = $catalog[$validated['program_plan_key']] ?? null;

        Transaction::create([
            'appointment_email' => $appointment->email,
            'gateway' => 'stripe',
            'type' => 'checkout',
            'status' => 'pending',
            'amount_cents' => (int) ($plan['amount'] ?? 0),
            'currency' => 'usd',
            'external_id' => $session->id,
            'payment_intent_id' => (string) ($session->payment_intent ?? ''),
            'payload' => [
                'checkout_url' => $session->url,
                'program_plan_key' => $validated['program_plan_key'],
            ],
        ]);

        return response()->json([
            'checkout_url' => $session->url,
            'appointment_id' => $appointment->id,
        ]);
    }

    private function programPlanName(?string $key): ?string
    {
        return match ($key) {
            'twelve-weeks' => '12 Weeks Commitment',
            'six-months' => '6 Months Commitment',
            'one-year' => '1 Year Commitment',
            default => null,
        };
    }

    private function ensureAppointmentNotInPast(Carbon $appointmentAt): void
    {
        if ($appointmentAt->lt(now())) {
            throw ValidationException::withMessages([
                'time' => 'This time slot is in the past.',
            ]);
        }
    }

    private function ensureSlotNotAdminBlocked(string $dateYmd, string $timeLabel): void
    {
        $blocked = BookingUnavailability::blockedSlotLabelsForDate($dateYmd);
        if (in_array($timeLabel, $blocked, true)) {
            throw ValidationException::withMessages([
                'time' => 'This time slot is not available for booking.',
            ]);
        }
    }
}
