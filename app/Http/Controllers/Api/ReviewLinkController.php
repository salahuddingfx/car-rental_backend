<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReviewLink;
use App\Models\Review;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReviewLinkController extends Controller
{
    // Admin: generate a review link for a booking
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $booking = Booking::with('car')->findOrFail($validated['booking_id']);

        // Check if a valid unused link already exists
        $existing = ReviewLink::where('booking_id', $booking->id)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            return response()->json([
                'token' => $existing->token,
                'url' => '/review/' . $existing->token,
                'expires_at' => $existing->expires_at,
            ]);
        }

        $reviewLink = ReviewLink::create([
            'booking_id' => $booking->id,
            'car_id' => $booking->car_id,
            'user_id' => $booking->user_id,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(30),
        ]);

        return response()->json([
            'token' => $reviewLink->token,
            'url' => '/review/' . $reviewLink->token,
            'expires_at' => $reviewLink->expires_at,
        ], 201);
    }

    // Admin: list all review links
    public function index()
    {
        $links = ReviewLink::with(['booking', 'car', 'user'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($links);
    }

    // Public: verify a review link token
    public function verify($token)
    {
        $link = ReviewLink::where('token', $token)->first();

        if (!$link) {
            abort(404, 'Review link not found.');
        }
        if ($link->used) {
            return response()->json(['message' => 'This review link has already been used.', 'used' => true]);
        }
        if ($link->expires_at && $link->expires_at->isPast()) {
            return response()->json(['message' => 'This review link has expired.', 'expired' => true]);
        }

        return response()->json([
            'valid' => true,
            'car' => $link->car->only(['id', 'name', 'brand', 'image']),
            'booking_id' => $link->booking_id,
            'booking_ref' => $link->booking->booking_ref,
        ]);
    }

    // Public: submit review via token (no auth needed)
    public function submit(Request $request, $token)
    {
        $link = ReviewLink::where('token', $token)->first();

        if (!$link) {
            abort(404, 'Review link not found.');
        }
        if ($link->used) {
            abort(422, 'This review link has already been used.');
        }
        if ($link->expires_at && $link->expires_at->isPast()) {
            abort(422, 'This review link has expired.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'car_condition' => 'nullable|numeric|min:1|max:5',
            'driver_rating' => 'nullable|numeric|min:1|max:5',
            'value_rating' => 'nullable|numeric|min:1|max:5',
            'cleanliness' => 'nullable|numeric|min:1|max:5',
            'text' => 'nullable|string|max:2000',
        ]);

        // Check if already reviewed this booking
        $existing = Review::where('booking_id', $link->booking_id)->first();
        if ($existing) {
            abort(422, 'A review already exists for this booking.');
        }

        $review = Review::create([
            ...$validated,
            'booking_id' => $link->booking_id,
            'car_id' => $link->car_id,
            'user_id' => $link->user_id,
            'source' => 'apexride',
            'date' => now()->toDateString(),
            'is_verified' => true,
        ]);

        // Mark link as used
        $link->update(['used' => true, 'used_at' => now()]);

        // Update car rating
        $stats = Review::where('car_id', $link->car_id)->whereNotNull('car_id')
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as count')
            ->first();
        if ($stats && $stats->count > 0) {
            \App\Models\Car::where('id', $link->car_id)->update([
                'rating' => round($stats->avg_rating, 2),
                'reviews_count' => $stats->count,
            ]);
        }

        return response()->json([
            'message' => 'Review submitted successfully!',
            'review' => $review,
        ], 201);
    }
}
