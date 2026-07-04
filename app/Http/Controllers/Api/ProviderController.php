<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function index(Request $request)
    {
        $query = Provider::query()->verified()->active();

        if ($request->type) {
            $query->byType($request->type);
        }

        if ($request->city) {
            $query->where('city', $request->city);
        }

        if ($request->lat && $request->lng) {
            $query->nearby($request->lat, $request->lng, $request->radius ?? 50);
        }

        $providers = $query->withCount(['cars', 'drivers'])
            ->orderBy($request->sort ?? 'rating', 'desc')
            ->paginate(12);

        return response()->json($providers);
    }

    public function show(Provider $provider)
    {
        if (!$provider->isVerified() || !$provider->is_active) {
            abort(404);
        }

        return response()->json(
            $provider->loadCount(['cars', 'drivers', 'bookings'])
                ->load(['user:id,name,avatar', 'verifications' => function ($q) {
                    $q->where('status', 'approved');
                }])
        );
    }

    public function providerCars(Provider $provider, Request $request)
    {
        $cars = $provider->cars()
            ->where('is_available', true)
            ->when($request->category, fn($q, $c) => $q->where('category', $c))
            ->when($request->min_price, fn($q, $p) => $q->where('price', '>=', $p))
            ->when($request->max_price, fn($q, $p) => $q->where('price', '<=', $p))
            ->orderBy($request->sort ?? 'created_at', 'desc')
            ->paginate(12);

        return response()->json($cars);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:individual,agency,company',
            'description' => 'nullable|string|max:1000',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);

        if ($request->user()->provider) {
            abort(422, 'You already have a provider profile.');
        }

        $provider = Provider::create([
            ...$request->only(['name', 'type', 'description', 'contact_phone', 'contact_email', 'address', 'city', 'state', 'country']),
            'user_id' => $request->user()->id,
            'verification_status' => 'pending',
        ]);

        return response()->json(['provider' => $provider], 201);
    }

    public function mine(Request $request)
    {
        $provider = $request->user()->provider;

        if (!$provider) {
            abort(404, 'No provider profile found.');
        }

        return response()->json(
            $provider->loadCount(['cars', 'drivers', 'bookings', 'members'])
        );
    }

    public function updateMine(Request $request)
    {
        $provider = $request->user()->provider;

        if (!$provider) {
            abort(404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
        ]);

        $provider->update($request->only([
            'name', 'description', 'contact_phone', 'contact_email', 'website',
            'address', 'city', 'state', 'country',
        ]));

        return response()->json(['provider' => $provider]);
    }

    public function myCars(Request $request)
    {
        $provider = $request->user()->provider;

        if (!$provider) {
            abort(404);
        }

        $cars = $provider->cars()
            ->with('assignedDriver.user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($cars);
    }

    public function myStats(Request $request)
    {
        $provider = $request->user()->provider;

        if (!$provider) {
            abort(404);
        }

        $stats = [
            'total_cars' => $provider->total_cars,
            'total_bookings' => $provider->total_bookings,
            'total_members' => $provider->members()->count(),
            'active_members' => $provider->activeMembers()->count(),
            'rating' => $provider->rating,
            'total_reviews' => $provider->total_reviews,
            'recent_bookings' => $provider->bookings()
                ->with('car', 'user')
                ->latest()
                ->take(10)
                ->get(),
            'monthly_revenue' => $provider->bookings()
                ->where('status', 'Completed')
                ->where('created_at', '>=', now()->startOfMonth())
                ->sum('total_price'),
        ];

        return response()->json($stats);
    }

    public function assignDriver(Request $request, \App\Models\Booking $booking)
    {
        $provider = $request->user()->provider;

        if (!$provider) {
            abort(404);
        }

        if ($booking->provider_id !== $provider->id) {
            abort(403, 'This booking does not belong to your provider.');
        }

        $request->validate([
            'member_id' => 'required|exists:provider_members,id',
        ]);

        $member = $provider->members()->findOrFail($request->member_id);

        if (!$member->isAvailable()) {
            abort(422, 'This driver is not available.');
        }

        $booking->update([
            'assigned_driver_id' => $member->id,
            'driver_info' => [
                'name' => $member->user->name,
                'phone' => $member->user->phone,
                'license_number' => $member->license_number,
            ],
        ]);

        return response()->json([
            'message' => 'Driver assigned successfully.',
            'booking' => $booking->load('assignedDriver.user'),
        ]);
    }

    // === ADMIN ROUTES ===

    public function adminIndex(Request $request)
    {
        $query = Provider::query()->withCount(['cars', 'drivers', 'bookings']);

        if ($request->status) {
            $query->where('verification_status', $request->status);
        }

        if ($request->type) {
            $query->byType($request->type);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('email', 'like', "%{$request->search}%"));
            });
        }

        $providers = $query->orderByDesc('created_at')->paginate(20);

        return response()->json($providers);
    }

    public function adminShow(Provider $provider)
    {
        return response()->json(
            $provider->loadCount(['cars', 'drivers', 'bookings', 'members'])
                ->load(['user:id,name,email,phone,avatar', 'verifications'])
        );
    }

    public function adminVerify(Request $request, Provider $provider)
    {
        $request->validate([
            'status' => 'required|in:verified,rejected',
            'rejection_reason' => 'required_if:status,rejected|string|max:500',
        ]);

        $provider->update([
            'verification_status' => $request->status,
            'rejection_reason' => $request->rejection_reason ?? null,
            'verified_at' => $request->status === 'verified' ? now() : null,
        ]);

        return response()->json(['provider' => $provider]);
    }

    public function adminToggleStatus(Provider $provider)
    {
        $provider->update(['is_active' => !$provider->is_active]);

        return response()->json(['provider' => $provider]);
    }

    public function adminPending(Request $request)
    {
        $providers = Provider::where('verification_status', 'pending')
            ->orwhere('verification_status', 'under_review')
            ->withCount(['cars', 'drivers'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($providers);
    }

    public function adminMembers(Provider $provider, Request $request)
    {
        $members = $provider->members()
            ->with('user:id,name,email,phone,avatar')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($members);
    }

    public function adminVerifyMember(Request $request, \App\Models\ProviderMember $member)
    {
        $request->validate([
            'license_verified' => 'required|boolean',
        ]);

        $member->update([
            'license_verified' => $request->license_verified,
        ]);

        return response()->json(['member' => $member]);
    }
}
