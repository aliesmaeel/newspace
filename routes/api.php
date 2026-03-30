<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/bookings/availability', [BookingController::class, 'availability']);
Route::post('/bookings', [BookingController::class, 'store']);
Route::post('/bookings/checkout-session', [BookingController::class, 'checkoutSession']);
Route::post('/stripe/webhook', StripeWebhookController::class);
