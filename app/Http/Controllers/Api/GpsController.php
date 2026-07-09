<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GpsLocation;
use Illuminate\Http\Request;

class GpsController extends Controller
{
    public function updateLocation(Request $request)
    {
        $validated = $request->validate([
            'car_id' => 'required|integer|exists:cars,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360',
            'booking_id' => 'nullable|integer|exists:bookings,id',
        ]);

        $location = GpsLocation::create([
            'car_id' => $validated['car_id'],
            'booking_id' => $validated['booking_id'] ?? null,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'speed' => $validated['speed'] ?? null,
            'heading' => $validated['heading'] ?? null,
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Location updated successfully.',
            'data' => $location,
        ], 201);
    }

    public function getCarLocation(string $carId)
    {
        $location = GpsLocation::where('car_id', $carId)
            ->latest('created_at')
            ->first();

        if (!$location) {
            return response()->json(['message' => 'No location data found for this car.'], 404);
        }

        return response()->json($location);
    }

    public function getBookingLocation(string $bookingId)
    {
        $booking = \App\Models\Booking::findOrFail($bookingId);

        $location = GpsLocation::where('car_id', $booking->car_id)
            ->latest('created_at')
            ->first();

        if (!$location) {
            return response()->json(['message' => 'No location data found for this booking.'], 404);
        }

        return response()->json($location);
    }

    public function trackHistory(string $bookingId)
    {
        $booking = \App\Models\Booking::findOrFail($bookingId);

        $locations = GpsLocation::where('car_id', $booking->car_id)
            ->where('created_at', '>=', $booking->pickup_date)
            ->where('created_at', '<=', $booking->return_date)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($locations);
    }
}
