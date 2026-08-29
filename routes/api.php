<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ContactInquiryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\InterestOptionController;
use App\Http\Controllers\Api\LegalContentController;
use App\Http\Controllers\Api\PageContentController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\Api\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/page-content', [PageContentController::class, 'show']);
Route::get('/legal-content', [LegalContentController::class, 'show']);
Route::get('/programs', [ProgramController::class, 'index']);
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{slug}', [EventController::class, 'show']);
Route::get('/interest-options', [InterestOptionController::class, 'index']);

Route::get('/bookings/availability', [BookingController::class, 'availability']);
Route::post('/bookings', [BookingController::class, 'store']);
Route::post('/bookings/checkout-session', [BookingController::class, 'checkoutSession']);
Route::post('/contact-inquiries', [ContactInquiryController::class, 'store']);
Route::post('/stripe/webhook', StripeWebhookController::class);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/user', [AuthController::class, 'user']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/email/resend-verification', [AuthController::class, 'resendVerificationEmail']);
    Route::post('/events/{slug}/register', [EventController::class, 'register']);
});
