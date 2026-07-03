<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::orderByDesc('created_at')->paginate(20));
    }

    public function show(User $user)
    {
        return response()->json($user->loadCount(['cars', 'bookings']));
    }

    public function update(Request $request, User $user)
    {
        if (!$request->user()->can('update', $user)) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'role' => 'sometimes|in:user,host,driver,company',
            'balance' => 'sometimes|numeric|min:0',
            'license_verified' => 'sometimes|boolean',
            'license_number' => 'nullable',
            'license_expiry' => 'nullable',
            'license_country' => 'nullable',
            'license_image' => 'nullable',
        ]);

        $safeFields = collect($validated)->only(['name', 'email', 'phone'])->toArray();
        $user->update($safeFields);

        if (isset($validated['role'])) {
            $user->forceFill(['role' => $validated['role']])->save();
        }
        if (isset($validated['balance'])) {
            $user->forceFill(['balance' => $validated['balance']])->save();
        }
        if (array_key_exists('license_verified', $validated)) {
            $licenseFields = collect($validated)->only(['license_number', 'license_expiry', 'license_country', 'license_image', 'license_verified'])->toArray();
            $user->update($licenseFields);
        }

        return response()->json($user);
    }

    public function destroy(Request $request, User $user)
    {
        if (!$request->user()->can('delete', $user)) {
            abort(403, 'Unauthorized.');
        }

        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => [
                'required', 'string', 'min:8', 'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
                'errors' => ['current_password' => ['The current password is incorrect.']],
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => 'Password updated successfully.']);
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $request->user()->update($validated);
        return response()->json($request->user());
    }

    public function updateLicense(Request $request)
    {
        $validated = $request->validate([
            'license_number' => 'required|string|max:50',
            'license_expiry' => 'required|date|after:today',
            'license_country' => 'required|string|max:100',
            'license_image' => 'nullable|string|max:500',
        ]);

        $request->user()->update([...$validated, 'license_verified' => false]);
        return response()->json([
            'message' => 'License submitted for verification.',
            'user' => $request->user(),
        ]);
    }
}
