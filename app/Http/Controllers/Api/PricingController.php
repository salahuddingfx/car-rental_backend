<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\PricingRule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'car_id' => 'required|exists:cars,id',
            'pickup_date' => 'required|date',
            'return_date' => 'required|date|after_or_equal:pickup_date',
        ]);

        $car = Car::find($validated['car_id']);
        $pickupDate = Carbon::parse($validated['pickup_date']);
        $returnDate = Carbon::parse($validated['return_date']);
        $days = max(1, $pickupDate->diffInDays($returnDate));

        $basePrice = (float) $car->price;
        $totalMultiplier = 1.0;
        $appliedRules = [];
        $breakdown = [];

        // Get active pricing rules
        $rules = PricingRule::where('is_active', true)->get();

        // Check each day in the booking period
        $currentDate = $pickupDate->copy();
        $dailyMultipliers = [];

        while ($currentDate->lte($returnDate)) {
            $dayMultiplier = 1.0;
            $dayRules = [];

            foreach ($rules as $rule) {
                $result = $rule->applyMultiplier($basePrice, $currentDate->toDateTimeString());
                if ($result['applied']) {
                    $dayMultiplier *= $result['rule']['multiplier'];
                    $dayRules[] = $result['rule'];
                }
            }

            $dailyMultipliers[] = [
                'date' => $currentDate->format('Y-m-d'),
                'multiplier' => $dayMultiplier,
                'rules' => $dayRules,
            ];

            // Track highest multiplier
            if ($dayMultiplier > $totalMultiplier) {
                $totalMultiplier = $dayMultiplier;
                $appliedRules = $dayRules;
            }

            $currentDate->addDay();
        }

        // Calculate pricing
        $rentalPrice = $basePrice * $days;
        $adjustedRentalPrice = $rentalPrice * $totalMultiplier;
        $tripFee = $adjustedRentalPrice * 0.12;
        $tax = $adjustedRentalPrice * 0.08;
        $totalPrice = $adjustedRentalPrice + $tripFee + $tax;

        $breakdown = [
            'base_daily_rate' => $basePrice,
            'days' => $days,
            'base_total' => $rentalPrice,
            'multiplier' => $totalMultiplier,
            'adjusted_total' => $adjustedRentalPrice,
            'trip_fee' => round($tripFee, 2),
            'tax' => round($tax, 2),
            'total' => round($totalPrice, 2),
            'currency' => 'BDT',
        ];

        return response()->json([
            'car_id' => $car->id,
            'car_name' => "{$car->brand} {$car->name}",
            'pickup_date' => $pickupDate->format('Y-m-d'),
            'return_date' => $returnDate->format('Y-m-d'),
            'days' => $days,
            'base_price' => $basePrice,
            'applied_rules' => $appliedRules,
            'daily_breakdown' => $dailyMultipliers,
            'pricing' => $breakdown,
        ]);
    }

    public function rules()
    {
        $rules = PricingRule::orderByDesc('created_at')->get();
        return response()->json($rules);
    }

    public function storeRule(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:peak_hour,weekend,holiday,seasonal',
            'multiplier' => 'required|numeric|min:1|max:10',
            'start_time' => 'nullable|date_format:H:i:s',
            'end_time' => 'nullable|date_format:H:i:s',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|between:0,6',
            'is_active' => 'boolean',
        ]);

        $rule = PricingRule::create($validated);

        return response()->json($rule, 201);
    }

    public function updateRule(Request $request, PricingRule $rule)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:peak_hour,weekend,holiday,seasonal',
            'multiplier' => 'sometimes|numeric|min:1|max:10',
            'start_time' => 'nullable|date_format:H:i:s',
            'end_time' => 'nullable|date_format:H:i:s',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|between:0,6',
            'is_active' => 'boolean',
        ]);

        $rule->update($validated);

        return response()->json($rule);
    }

    public function deleteRule(PricingRule $rule)
    {
        $rule->delete();
        return response()->json(['message' => 'Pricing rule deleted']);
    }
}
