<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Booking;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // Public: get reviews for a car
    public function byCar($carId)
    {
        $reviews = Review::where('car_id', $carId)
            ->whereNotNull('car_id')
            ->with('user:id,name,avatar')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($reviews);
    }

    // Public: all reviews (for /reviews page)
    public function index(Request $request)
    {
        $query = Review::with('user:id,name,avatar');

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }

        $sort = $request->get('sort', 'recent');
        match ($sort) {
            'helpful' => $query->orderByDesc('helpful_count'),
            'rating' => $query->orderByDesc('rating'),
            default => $query->orderByDesc('created_at'),
        };

        return response()->json($query->paginate(20));
    }

    // Authenticated: submit a review (linked to booking)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'car_id' => 'required|exists:cars,id',
            'rating' => 'required|integer|min:1|max:5',
            'car_condition' => 'nullable|numeric|min:1|max:5',
            'driver_rating' => 'nullable|numeric|min:1|max:5',
            'value_rating' => 'nullable|numeric|min:1|max:5',
            'cleanliness' => 'nullable|numeric|min:1|max:5',
            'text' => 'nullable|string|max:2000',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'string|max:500',
        ]);

        // Verify booking belongs to user and is completed
        $booking = Booking::where('id', $validated['booking_id'])
            ->where('user_id', $request->user()->id)
            ->where('status', 'Completed')
            ->first();

        if (!$booking) {
            abort(422, 'You can only review completed bookings.');
        }

        // Check if already reviewed
        $existing = Review::where('user_id', $request->user()->id)
            ->where('booking_id', $validated['booking_id'])
            ->first();

        if ($existing) {
            abort(422, 'You have already reviewed this booking.');
        }

        $review = Review::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'name' => $request->user()->name,
            'avatar' => $request->user()->avatar,
            'source' => 'apexride',
            'date' => now()->toDateString(),
            'is_verified' => true,
        ]);

        // Update car's average rating
        $this->updateCarRating($validated['car_id']);

        return response()->json($review->load('user'), 201);
    }

    // Public: mark review as helpful
    public function helpful(Review $review)
    {
        $review->increment('helpful_count');
        return response()->json(['helpful_count' => $review->helpful_count]);
    }

    // Host/Admin: respond to review
    public function respond(Request $request, Review $review)
    {
        $request->validate(['host_response' => 'required|string|max:1000']);

        $review->update(['host_response' => $request->host_response]);

        return response()->json($review);
    }

    // Admin: delete review
    public function destroy(Request $request, Review $review)
    {
        $carId = $review->car_id;
        $review->delete();
        if ($carId) {
            $this->updateCarRating($carId);
        }
        return response()->json(['message' => 'Review deleted']);
    }

    private function updateCarRating($carId)
    {
        $stats = Review::where('car_id', $carId)->whereNotNull('car_id')
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as count')
            ->first();

        if ($stats && $stats->count > 0) {
            \App\Models\Car::where('id', $carId)->update([
                'rating' => round($stats->avg_rating, 2),
                'reviews_count' => $stats->count,
            ]);
        }
    }
}
