<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyPoint;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoyaltyController extends Controller
{
    // Tier thresholds
    private const TIERS = [
        'bronze' => ['min' => 0, 'next' => 'silver', 'multiplier' => 1, 'discount' => 0],
        'silver' => ['min' => 1000, 'next' => 'gold', 'multiplier' => 1.2, 'discount' => 5],
        'gold' => ['min' => 5000, 'next' => 'platinum', 'multiplier' => 1.5, 'discount' => 10],
        'platinum' => ['min' => 20000, 'next' => null, 'multiplier' => 2, 'discount' => 15],
    ];

    public function balance(Request $request)
    {
        $user = $request->user();
        $tier = self::TIERS[$user->tier] ?? self::TIERS['bronze'];

        return response()->json([
            'points' => $user->loyalty_points,
            'tier' => $user->tier,
            'tier_benefits' => [
                'multiplier' => $tier['multiplier'],
                'discount' => $tier['discount'] . '%',
            ],
            'next_tier' => $tier['next'],
            'points_to_next_tier' => $tier['next'] ? self::TIERS[$tier['next']]['min'] - $user->loyalty_points : 0,
        ]);
    }

    public function history(Request $request)
    {
        $points = LoyaltyPoint::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($points);
    }

    public function tier(Request $request)
    {
        $user = $request->user();
        $currentTier = self::TIERS[$user->tier] ?? self::TIERS['bronze'];
        $nextTier = $currentTier['next'] ? self::TIERS[$currentTier['next']] : null;

        return response()->json([
            'current_tier' => $user->tier,
            'points' => $user->loyalty_points,
            'benefits' => [
                'points_multiplier' => $currentTier['multiplier'] . 'x',
                'discount' => $currentTier['discount'] . '%',
            ],
            'next_tier' => $currentTier['next'],
            'next_tier_benefits' => $nextTier ? [
                'points_multiplier' => $nextTier['multiplier'] . 'x',
                'discount' => $nextTier['discount'] . '%',
            ] : null,
            'points_to_next_tier' => $currentTier['next'] ? self::TIERS[$currentTier['next']]['min'] - $user->loyalty_points : 0,
        ]);
    }

    // Referral: generate code
    public function generateReferral(Request $request)
    {
        $user = $request->user();

        if ($user->referral_code) {
            return response()->json(['code' => $user->referral_code]);
        }

        $code = strtoupper(substr($user->name, 0, 3) . Str::random(4) . rand(10, 99));
        $user->update(['referral_code' => $code]);

        return response()->json(['code' => $code]);
    }

    // Referral: stats
    public function referralStats(Request $request)
    {
        $user = $request->user();
        $totalReferrals = Referral::where('referrer_id', $user->id)->count();
        $completedReferrals = Referral::where('referrer_id', $user->id)->where('status', 'completed')->count();
        $totalEarned = LoyaltyPoint::where('user_id', $user->id)->where('type', 'referral')->sum('points');

        return response()->json([
            'code' => $user->referral_code,
            'total_referrals' => $totalReferrals,
            'completed_referrals' => $completedReferrals,
            'total_points_earned' => $totalEarned,
            'referral_bonus' => 500,
        ]);
    }

    // Referral: apply code (during registration or after)
    public function applyReferral(Request $request)
    {
        $request->validate(['code' => 'required|string|max:20']);

        $referrer = User::where('referral_code', $request->code)->first();

        if (!$referrer || $referrer->id === $request->user()->id) {
            abort(422, 'Invalid referral code.');
        }

        $existing = Referral::where('referrer_id', $referrer->id)
            ->where('referee_id', $request->user()->id)
            ->exists();

        if ($existing) {
            abort(422, 'You have already been referred by this user.');
        }

        Referral::create([
            'referrer_id' => $referrer->id,
            'referee_id' => $request->user()->id,
            'code' => $request->code,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Referral applied! You will receive 500 bonus points after your first booking.']);
    }

    // Award points (called internally after booking completion)
    public static function awardPoints($userId, $points, $type, $description, $bookingId = null)
    {
        $user = User::find($userId);
        if (!$user) return;

        // Apply tier multiplier
        $tier = self::TIERS[$user->tier] ?? self::TIERS['bronze'];
        $actualPoints = (int) ($points * $tier['multiplier']);

        LoyaltyPoint::create([
            'user_id' => $userId,
            'points' => $actualPoints,
            'type' => $type,
            'description' => $description,
            'booking_id' => $bookingId,
        ]);

        $user->increment('loyalty_points', $actualPoints);

        // Update tier
        self::updateTier($user);

        return $actualPoints;
    }

    // Process referral bonus after first booking
    public static function processReferralBonus($userId, $bookingId)
    {
        $referral = Referral::where('referee_id', $userId)->where('status', 'pending')->first();
        if (!$referral) return;

        $referral->update(['status' => 'completed', 'completed_at' => now()]);

        // Award 500 points to both referrer and referee
        self::awardPoints($referral->referrer_id, 500, 'referral', 'Referral bonus for referring a friend', $bookingId);
        self::awardPoints($userId, 500, 'referral', 'Welcome bonus for being referred', $bookingId);
    }

    private static function updateTier(User $user)
    {
        $newTier = 'bronze';
        if ($user->loyalty_points >= 20000) $newTier = 'platinum';
        elseif ($user->loyalty_points >= 5000) $newTier = 'gold';
        elseif ($user->loyalty_points >= 1000) $newTier = 'silver';

        if ($user->tier !== $newTier) {
            $user->update(['tier' => $newTier]);
        }
    }

    // Admin: leaderboard
    public function leaderboard()
    {
        $leaders = User::orderByDesc('loyalty_points')
            ->select('id', 'name', 'avatar', 'loyalty_points', 'tier')
            ->limit(20)
            ->get();

        return response()->json($leaders);
    }
}
