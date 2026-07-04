<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GuestBooking;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class GuestBookingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'car_id' => 'required|exists:cars,id',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'required|string|max:50',
            'guest_country' => 'nullable|string|max:100',
            'guest_location' => 'nullable|string|max:100',
            'license_number' => 'nullable|string|max:100',
            'license_expiry' => 'nullable|string|max:50',
            'pickup_date' => 'required|date|after:today',
            'return_date' => 'required|date|after:pickup_date',
        ]);

        $car = Car::find($validated['car_id']);
        $days = max(1, \Carbon\Carbon::parse($validated['pickup_date'])->diffInDays($validated['return_date']));
        $totalPrice = $days * $car->price;

        $guestBooking = GuestBooking::create([
            ...$validated,
            'booking_ref' => 'AR-' . strtoupper(substr(uniqid(), -8)),
            'total_days' => $days,
            'total_price' => $totalPrice,
            'status' => 'Upcoming',
            'driver_info' => [
                'fullName' => $validated['guest_name'],
                'email' => $validated['guest_email'],
                'phone' => $validated['guest_phone'],
                'licenseNumber' => $validated['license_number'] ?? 'N/A',
                'licenseExpiry' => $validated['license_expiry'] ?? 'N/A',
            ],
        ]);

        return response()->json([
            'booking' => $guestBooking->load('car'),
            'message' => 'Guest booking created successfully.',
        ], 201);
    }

    public function show(Request $request, GuestBooking $guestBooking)
    {
        return response()->json([
            'booking' => $guestBooking->load('car'),
        ]);
    }

    public function lookup(Request $request)
    {
        if (RateLimiter::tooManyAttempts('guest-booking-lookup:' . $request->ip(), 10)) {
            $seconds = RateLimiter::availableIn('guest-booking-lookup:' . $request->ip());
            return response()->json([
                'message' => 'Too many lookup attempts. Try again in ' . $seconds . ' seconds.',
            ], 429);
        }
        RateLimiter::hit('guest-booking-lookup:' . $request->ip(), 60);

        $request->validate([
            'booking_ref' => 'required|string',
            'guest_email' => 'required|email',
        ]);

        $booking = GuestBooking::where('booking_ref', $request->booking_ref)
            ->where('guest_email', $request->guest_email)
            ->with('car:id,name,brand,image,price')
            ->firstOrFail();

        return response()->json([
            'booking' => $booking,
        ]);
    }
}
