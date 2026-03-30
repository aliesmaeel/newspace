<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ZohoOAuthService;
use Throwable;

class ZohoOAuthController extends Controller
{
    public function redirect(ZohoOAuthService $oauth)
    {
        return redirect()->away($oauth->authorizeUrl());
    }

    public function callback(Request $request, ZohoOAuthService $oauth)
    {
        $code = (string) $request->query('code', '');
        if ($code === '') {
            return response('Missing OAuth code from Zoho.', 422);
        }

        try {
            $oauth->exchangeCode($code);
        } catch (Throwable $e) {
            report($e);
            return response('Zoho OAuth failed: '.$e->getMessage(), 500);
        }

        return response('Zoho OAuth connected successfully. You can close this tab.', 200);
    }
}
