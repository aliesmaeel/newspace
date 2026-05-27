<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', self::emailTypoRule()],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:50'],
            'interest_option_id' => ['nullable', 'exists:interest_options,id'],
            'hear_about_us' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'phone' => $validated['phone'] ?? null,
            'interest_option_id' => $validated['interest_option_id'] ?? null,
            'hear_about_us' => $validated['hear_about_us'] ?? null,
            'is_admin' => false,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $emailSent = $this->sendWelcomeEmail($user, $verificationUrl);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'user' => $this->userPayload($user),
            'email_sent' => $emailSent,
            'message' => $emailSent
                ? 'Welcome! Please check your email to verify your account.'
                : 'Account created, but we could not send the verification email. Use “Resend” on the banner or try again shortly.',
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'user' => $this->userPayload(Auth::user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out.']);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['user' => null]);
        }

        return response()->json([
            'user' => $this->userPayload($user),
        ]);
    }

    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email is already verified.']);
        }

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        if (! $this->sendWelcomeEmail($user, $verificationUrl)) {
            return response()->json(['message' => 'Could not send verification email. Please try again later.'], 500);
        }

        return response()->json(['message' => 'Verification email sent.']);
    }

    private function sendWelcomeEmail(User $user, string $verificationUrl): bool
    {
        try {
            Mail::to($user->email)->send(new WelcomeMail($user, $verificationUrl));

            return true;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }

    /**
     * @return \Closure(string, mixed, \Closure): void
     */
    private static function emailTypoRule(): \Closure
    {
        $typos = [
            'gmial.com' => 'gmail.com',
            'gmai.com' => 'gmail.com',
            'gamil.com' => 'gmail.com',
            'gnail.com' => 'gmail.com',
            'hotmial.com' => 'hotmail.com',
            'yaho.com' => 'yahoo.com',
        ];

        return function (string $attribute, mixed $value, \Closure $fail) use ($typos): void {
            if (! is_string($value)) {
                return;
            }

            $domain = strtolower((string) substr(strrchr($value, '@'), 1));
            if (isset($typos[$domain])) {
                $suggested = preg_replace('/@'.preg_quote($domain, '/').'$/i', '@'.$typos[$domain], $value);
                $fail("Did you mean {$suggested}? Please check your email address.");
            }
        };
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'is_admin' => (bool) $user->is_admin,
            'email_verified' => $user->hasVerifiedEmail(),
        ];
    }
}
