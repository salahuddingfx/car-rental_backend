<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return response()->json([
            'message' => $status === Password::RESET_LINK_SENT
                ? 'Password reset link has been sent to your email.'
                : 'If an account with that email exists, a password reset link has been sent.',
        ]);
    }
}
