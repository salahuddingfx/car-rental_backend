<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Notifications\VerifyEmail;

class EmailVerificationController extends Controller
{
    public function send(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 200);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent.']);
    }

    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (! URL::hasValidSignature($request)) {
            abort(403, 'Invalid verification link.');
        }

        if (! hash_equals((string) $user->getKey(), (string) $id)) {
            abort(403, 'Invalid verification link.');
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
            abort(403, 'Invalid verification link.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect(config('app.client_url', '/dashboard'));
        }

        $user->markEmailAsVerified();

        return redirect(config('app.client_url', '/dashboard') . '/email-verified');
    }

    public function check(Request $request)
    {
        return response()->json([
            'verified' => $request->user()->hasVerifiedEmail(),
        ]);
    }
}
