<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $query = Wishlist::where('user_id', $request->user()->id)->with('car');
        $wishlists = $query->orderByDesc('created_at')->get();

        return response()->json($wishlists);
    }

    public function toggle(Request $request, string $carId)
    {
        $existing = Wishlist::where('user_id', $request->user()->id)
            ->where('car_id', $carId)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['added' => false, 'message' => 'Removed from wishlist.']);
        }

        Wishlist::create([
            'user_id' => $request->user()->id,
            'car_id' => $carId,
        ]);

        return response()->json(['added' => true, 'message' => 'Added to wishlist.'], 201);
    }

    public function destroy(Request $request, string $carId)
    {
        Wishlist::where('user_id', $request->user()->id)
            ->where('car_id', $carId)
            ->delete();

        return response()->json(['message' => 'Removed from wishlist.']);
    }

    public function check(Request $request, string $carId)
    {
        $exists = Wishlist::where('user_id', $request->user()->id)
            ->where('car_id', $carId)
            ->exists();

        return response()->json(['is_wishlisted' => $exists]);
    }
}
