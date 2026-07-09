<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function check(Car $car, Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $start = \Carbon\Carbon::parse($request->month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $bookedDates = \DB::table('bookings')
            ->where('car_id', $car->id)
            ->where('status', '!=', 'cancelled')
            ->where('pickup_date', '<=', $end)
            ->where('return_date', '>=', $start)
            ->pluck('pickup_date', 'return_date')
            ->toArray();

        $guestBookedDates = \DB::table('guest_bookings')
            ->where('car_id', $car->id)
            ->where('status', '!=', 'cancelled')
            ->where('pickup_date', '<=', $end)
            ->where('return_date', '>=', $start)
            ->pluck('pickup_date', 'return_date')
            ->toArray();

        $booked = collect();

        foreach ($bookedDates as $returnDate => $pickupDate) {
            $period = \Carbon\CarbonPeriod::create($pickupDate, $returnDate);
            foreach ($period as $date) {
                if ($date->between($start, $end)) {
                    $booked->push($date->toDateString());
                }
            }
        }

        foreach ($guestBookedDates as $returnDate => $pickupDate) {
            $period = \Carbon\CarbonPeriod::create($pickupDate, $returnDate);
            foreach ($period as $date) {
                if ($date->between($start, $end)) {
                    $booked->push($date->toDateString());
                }
            }
        }

        $booked->unique()->values();

        return response()->json([
            'car_id' => $car->id,
            'month' => $request->month,
            'booked_dates' => $booked->values()->all(),
            'available' => $booked->isEmpty(),
        ]);
    }
}
