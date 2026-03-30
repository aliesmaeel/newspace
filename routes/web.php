<?php

use App\Http\Controllers\ZohoOAuthController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/zoho/oauth/redirect', [ZohoOAuthController::class, 'redirect'])->name('zoho.oauth.redirect');
    Route::get('/zoho/oauth/callback', [ZohoOAuthController::class, 'callback'])->name('zoho.oauth.callback');
});


Route::view('/{any?}', 'app')->where('any', '.*');
