<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProviderMember;
use App\Models\User;
use Illuminate\Http\Request;

class ProviderMemberController extends Controller
{
    public function index(Request $request)
    {
        $provider = $request->user()->provider;

        if (!$provider) {
            abort(404, 'No provider profile found.');
        }

        $members = $provider->members()
            ->with('user:id,name,email,phone,avatar')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($members);
    }

    public function store(Request $request)
    {
        $provider = $request->user()->provider;

        if (!$provider || !$provider->canAddDrivers()) {
            abort(403, 'Only agencies and companies can add drivers.');
        }

        $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'sometimes|in:driver,manager,dispatcher',
            'license_number' => 'nullable|string|max:50',
            'license_expiry' => 'nullable|date|after:today',
            'license_country' => 'nullable|string|max:50',
        ]);

        $user = User::where('email', $request->email)->first();

        $existing = ProviderMember::where('provider_id', $provider->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($existing) {
            abort(422, 'This user is already a member of your provider.');
        }

        $member = ProviderMember::create([
            'provider_id' => $provider->id,
            'user_id' => $user->id,
            'role' => $request->role ?? 'driver',
            'license_number' => $request->license_number,
            'license_expiry' => $request->license_expiry,
            'license_country' => $request->license_country,
        ]);

        if ($request->role === 'driver' || !$request->role) {
            $user->update(['role' => 'driver']);
        }

        return response()->json(['member' => $member->load('user')], 201);
    }

    public function update(Request $request, ProviderMember $member)
    {
        $provider = $request->user()->provider;

        if (!$provider || $member->provider_id !== $provider->id) {
            abort(403);
        }

        $request->validate([
            'role' => 'sometimes|in:driver,manager,dispatcher',
            'status' => 'sometimes|in:active,inactive,suspended',
            'is_available' => 'sometimes|boolean',
            'suspension_reason' => 'required_if:status,suspended',
        ]);

        $member->update($request->only([
            'role', 'status', 'is_available', 'suspension_reason',
        ]));

        return response()->json(['member' => $member->load('user')]);
    }

    public function destroy(Request $request, ProviderMember $member)
    {
        $provider = $request->user()->provider;

        if (!$provider || $member->provider_id !== $provider->id) {
            abort(403);
        }

        $member->delete();

        return response()->json(['message' => 'Member removed.']);
    }

    public function submitLicense(Request $request, ProviderMember $member)
    {
        $provider = $request->user()->provider;

        if (!$provider || $member->provider_id !== $provider->id) {
            abort(403);
        }

        $request->validate([
            'license_number' => 'required|string|max:50',
            'license_expiry' => 'required|date|after:today',
            'license_country' => 'required|string|max:50',
            'license_image' => 'required|string|max:255',
        ]);

        $member->update([
            'license_number' => $request->license_number,
            'license_expiry' => $request->license_expiry,
            'license_country' => $request->license_country,
            'license_image' => $request->license_image,
            'license_verified' => false,
        ]);

        return response()->json(['message' => 'License submitted for verification.', 'member' => $member]);
    }
}
