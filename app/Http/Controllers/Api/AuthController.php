<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => [
                'required', 'string', 'min:8', 'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'referral_code' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
        ]);

        // Process referral if code provided
        if (!empty($validated['referral_code'])) {
            $referrer = User::where('referral_code', $validated['referral_code'])->first();
            if ($referrer && $referrer->id !== $user->id) {
                Referral::create([
                    'referrer_id' => $referrer->id,
                    'referee_id' => $user->id,
                    'code' => $validated['referral_code'],
                    'status' => 'pending',
                ]);
            }
        }

        // Send verification email
        $user->sendEmailVerificationNotification();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'email_verified' => $user->hasVerifiedEmail(),
                'loyalty_points' => $user->loyalty_points,
                'tier' => $user->tier,
            ],
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required_without:phone|email',
            'phone' => 'required_without:email|string',
            'password' => 'required',
        ]);

        $user = User::where(function ($query) use ($request) {
            $query->where('email', $request->email);
            if ($request->filled('phone')) {
                $query->orWhere('phone', $request->phone);
            }
        })->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'avatar' => $user->avatar,
                'email_verified' => $user->hasVerifiedEmail(),
                'license_number' => $user->license_number,
                'license_expiry' => $user->license_expiry,
                'license_country' => $user->license_country,
                'license_image' => $user->license_image,
                'license_verified' => $user->license_verified,
                'loyalty_points' => $user->loyalty_points,
                'tier' => $user->tier,
            ],
            'token' => $token,
        ]);
    }

    public function adminLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->where('role', 'admin')->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid admin credentials.'],
            ]);
        }

        $token = $user->createToken('admin-token', ['admin'])->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'avatar' => $user->avatar,
                'email_verified' => $user->hasVerifiedEmail(),
            ],
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'avatar' => $user->avatar,
            'balance' => $user->balance,
            'license_verified' => $user->license_verified,
            'license_image' => $user->license_image,
            'email_verified' => $user->hasVerifiedEmail(),
            'loyalty_points' => $user->loyalty_points,
            'referral_code' => $user->referral_code,
            'tier' => $user->tier,
        ]);
    }
}
