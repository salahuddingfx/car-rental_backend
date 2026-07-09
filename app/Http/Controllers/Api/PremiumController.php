<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\PremiumOrder;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PremiumController extends Controller
{
    public function plans()
    {
        return response()->json([
            'plans' => [
                [
                    'name' => 'monthly',
                    'label' => 'Monthly',
                    'duration' => '1 Month',
                    'price' => 500,
                    'description' => 'Boost your listing for 1 month',
                ],
                [
                    'name' => 'quarterly',
                    'label' => 'Quarterly',
                    'duration' => '3 Months',
                    'price' => 1200,
                    'description' => 'Boost your listing for 3 months (Save 20%)',
                ],
                [
                    'name' => 'yearly',
                    'label' => 'Yearly',
                    'duration' => '12 Months',
                    'price' => 4000,
                    'description' => 'Boost your listing for 1 year (Save 33%)',
                ],
            ],
        ]);
    }

    public function purchase(Request $request)
    {
        $validated = $request->validate([
            'car_id' => 'required|exists:cars,id',
            'plan' => 'required|in:monthly,quarterly,yearly',
        ]);

        $car = Car::find($validated['car_id']);

        // Check ownership
        if ($car->user_id !== $request->user()->id) {
            abort(403, 'You can only upgrade your own cars.');
        }

        // Check if already active premium
        $existing = PremiumOrder::where('car_id', $car->id)
            ->where('status', 'active')
            ->where('ends_at', '>', Carbon::now())
            ->first();

        if ($existing) {
            abort(422, 'This car already has an active premium listing.');
        }

        // Calculate duration and amount
        $plans = [
            'monthly' => ['days' => 30, 'amount' => 500],
            'quarterly' => ['days' => 90, 'amount' => 1200],
            'yearly' => ['days' => 365, 'amount' => 4000],
        ];

        $planData = $plans[$validated['plan']];
        $startsAt = Carbon::now();
        $endsAt = $startsAt->copy()->addDays($planData['days']);

        // Create premium order
        $order = PremiumOrder::create([
            'car_id' => $car->id,
            'user_id' => $request->user()->id,
            'plan' => $validated['plan'],
            'amount' => $planData['amount'],
            'status' => 'active',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        // Update car premium status
        $car->update([
            'is_premium' => true,
            'premium_expires_at' => $endsAt,
            'premium_priority' => 10,
        ]);

        return response()->json([
            'message' => 'Premium listing activated successfully',
            'order' => $order->load('car'),
        ], 201);
    }

    public function myPremiumCars(Request $request)
    {
        $cars = Car::where('user_id', $request->user()->id)
            ->where('is_premium', true)
            ->with(['premiumOrders' => function ($q) {
                $q->where('status', 'active')->latest();
            }])
            ->get();

        return response()->json($cars);
    }
}
