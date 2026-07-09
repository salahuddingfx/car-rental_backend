<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function index(Request $request)
    {
        $query = Car::with(['user', 'provider']);

        if ($request->category && $request->category !== 'All') {
            $query->where('category', $request->category);
        }
        if ($request->fuel && $request->fuel !== 'All') {
            $query->where('fuel', $request->fuel);
        }
        if ($request->brand) {
            $query->where('brand', $request->brand);
        }
        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }
        if ($request->provider_id) {
            $query->where('provider_id', $request->provider_id);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('brand', 'like', "%{$request->search}%")
                  ->orWhere('location', 'like', "%{$request->search}%");
            });
        }

        // Geolocation filter: nearby cars within radius (km)
        if ($request->filled('lat') && $request->filled('lng')) {
            $lat = (float) $request->lat;
            $lng = (float) $request->lng;
            $radius = (float) $request->get('radius', 50); // default 50km

            $query->selectRaw("*, (
                6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                ) AS distance", [$lat, $lng, $lat])
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->having('distance', '<', $radius)
                ->orderBy('distance');
        } else {
            $sort = $request->get('sort', 'rating');
            // Always prioritize premium cars, then apply secondary sort
            $query->orderByDesc('is_premium')->orderByDesc('premium_priority');

            match ($sort) {
                'price_low' => $query->orderBy('price', 'asc'),
                'price_high' => $query->orderBy('price', 'desc'),
                'name' => $query->orderBy('name', 'asc'),
                default => $query->orderByDesc('rating')->orderByDesc('reviews_count'),
            };
        }

        $cars = $query->paginate($request->get('per_page', 12));

        return response()->json($cars);
    }

    public function show(Car $car)
    {
        return response()->json($car->load(['user', 'provider', 'assignedDriver.user', 'reviews' => function ($q) use ($car) {
            $q->whereNull('car_id')->orWhere('car_id', $car->id);
            $q->latest()->limit(10);
        }]));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'seats' => 'required|integer|min:1',
            'transmission' => 'required|string|max:50',
            'fuel' => 'required|string|max:50',
            'power' => 'nullable|string|max:50',
            'speed' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:5000',
            'features' => 'nullable|array',
            'image' => 'nullable|string|max:500',
            'images' => 'nullable|array',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'year' => 'nullable|string|max:4',
        ]);

        $car = $request->user()->cars()->create($validated);

        // Link to provider if user has one
        $provider = $request->user()->provider;
        if ($provider) {
            $car->update(['provider_id' => $provider->id]);
            $provider->increment('total_cars');
        }

        return response()->json($car->load('provider'), 201);
    }

    public function update(Request $request, Car $car)
    {
        if (!$request->user()->can('update', $car)) {
            abort(403, 'You can only update your own cars.');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'brand' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:50',
            'price' => 'sometimes|numeric|min:0',
            'seats' => 'sometimes|integer|min:1',
            'transmission' => 'sometimes|string|max:50',
            'fuel' => 'sometimes|string|max:50',
            'power' => 'nullable|string|max:50',
            'speed' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:5000',
            'features' => 'nullable|array',
            'image' => 'nullable|string|max:500',
            'images' => 'nullable|array',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'year' => 'nullable|string|max:4',
            'is_available' => 'sometimes|boolean',
        ]);

        $car->update($validated);

        return response()->json($car);
    }

    public function destroy(Request $request, Car $car)
    {
        if (!$request->user()->can('delete', $car)) {
            abort(403, 'You can only delete your own cars.');
        }

        $car->delete();
        return response()->json(['message' => 'Car deleted']);
    }

    public function nearby(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:500',
        ]);

        $lat = (float) $request->lat;
        $lng = (float) $request->lng;
        $radius = (float) $request->get('radius', 50);

        $cars = Car::selectRaw("*, (
            6371 * acos(
                cos(radians(?)) * cos(radians(latitude)) *
                cos(radians(longitude) - radians(?)) +
                sin(radians(?)) * sin(radians(latitude))
            ) AS distance", [$lat, $lng, $lat])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('is_available', true)
            ->having('distance', '<', $radius)
            ->orderBy('distance')
            ->with(['user', 'provider'])
            ->paginate($request->get('per_page', 12));

        return response()->json($cars);
    }
}
