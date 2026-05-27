<?php

use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\ZohoOAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/zoho/oauth/redirect', [ZohoOAuthController::class, 'redirect'])->name('zoho.oauth.redirect');
    Route::get('/zoho/oauth/callback', [ZohoOAuthController::class, 'callback'])->name('zoho.oauth.callback');
});


Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::view('/{any?}', 'app')->where('any', '.*');
