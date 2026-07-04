<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Car;
use App\Events\BookingConfirmed;
use App\Events\BookingCancelled;
use App\Http\Controllers\Api\LoyaltyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = $request->user()->bookings()->with('car')->orderByDesc('created_at')->get();
        return response()->json($bookings);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'car_id' => 'required|exists:cars,id',
            'pickup_date' => 'required|date|after:today',
            'return_date' => 'required|date|after:pickup_date',
            'driver_info' => 'nullable|array',
        ]);

        $car = Car::with('provider')->find($validated['car_id']);
        $days = max(1, \Carbon\Carbon::parse($validated['pickup_date'])->diffInDays($validated['return_date']));
        $totalPrice = $days * $car->price;

        $booking = Booking::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'booking_ref' => 'AR-' . strtoupper(substr(uniqid(), -8)),
            'total_days' => $days,
            'total_price' => $totalPrice,
            'status' => 'Upcoming',
            'provider_id' => $car->provider_id,
        ]);

        // Increment provider total_bookings
        if ($car->provider) {
            $car->provider->increment('total_bookings');
        }

        BookingConfirmed::dispatch($booking->load(['car', 'user', 'provider']));

        return response()->json($booking->load(['car', 'provider']), 201);
    }

    public function show(Request $request, Booking $booking)
    {
        if (!$request->user()->can('view', $booking)) {
            abort(403, 'You can only view your own bookings.');
        }

        return response()->json($booking->load('car'));
    }

    public function cancel(Request $request, Booking $booking)
    {
        if (!$request->user()->can('cancel', $booking)) {
            abort(403, 'You can only cancel your own bookings.');
        }

        if (in_array($booking->status, ['Completed', 'Cancelled'])) {
            abort(422, 'This booking cannot be cancelled.');
        }

        $booking->update(['status' => 'Cancelled']);

        BookingCancelled::dispatch($booking->load(['car', 'user']));

        return response()->json($booking);
    }

    public function lookup(Request $request)
    {
        $request->validate([
            'booking_ref' => 'required|string|size:10',
        ]);

        if (RateLimiter::tooManyAttempts('booking-lookup:' . $request->ip(), 10)) {
            $seconds = RateLimiter::availableIn('booking-lookup:' . $request->ip());
            return response()->json([
                'message' => 'Too many lookup attempts. Try again in ' . $seconds . ' seconds.',
            ], 429);
        }
        RateLimiter::hit('booking-lookup:' . $request->ip(), 60);

        $booking = Booking::where('booking_ref', $request->booking_ref)
            ->with(['car:id,name,brand,image,price'])
            ->firstOrFail();

        return response()->json([
            'booking_ref' => $booking->booking_ref,
            'status' => $booking->status,
            'pickup_date' => $booking->pickup_date,
            'return_date' => $booking->return_date,
            'total_days' => $booking->total_days,
            'total_price' => $booking->total_price,
            'car' => $booking->car,
        ]);
    }

    public function all(Request $request)
    {
        $bookings = Booking::with(['car', 'user'])->orderByDesc('created_at')->paginate(20);
        return response()->json($bookings);
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate(['status' => 'required|in:Upcoming,Active,Completed,Cancelled']);
        $oldStatus = $booking->status;
        $booking->update(['status' => $request->status]);

        // Award loyalty points when booking is completed
        if ($request->status === 'Completed' && $oldStatus !== 'Completed') {
            $points = (int) ($booking->total_price * 0.1); // 10% of price as points
            LoyaltyController::awardPoints($booking->user_id, $points, 'earned', "Booking #{$booking->booking_ref} completed", $booking->id);

            // Process referral bonus on first completed booking
            LoyaltyController::processReferralBonus($booking->user_id, $booking->id);
        }

        return response()->json($booking);
    }
}
