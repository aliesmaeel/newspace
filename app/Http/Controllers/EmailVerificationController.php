<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = User::query()->findOrFail($id);

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return redirect('/?verified=invalid');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect('/?verified=already');
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return redirect('/?verified=1');
    }
}
