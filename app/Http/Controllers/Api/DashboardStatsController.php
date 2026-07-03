<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Car;
use Illuminate\Http\Request;

class DashboardStatsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $totalBookings = $user->bookings()->count();
        $upcomingBookings = $user->bookings()->where('status', 'Upcoming')->count();
        $completedBookings = $user->bookings()->where('status', 'Completed')->count();
        $cancelledBookings = $user->bookings()->where('status', 'Cancelled')->count();
        $totalSpent = $user->bookings()->where('status', 'Completed')->sum('total_price');

        // Monthly spending for last 6 months
        $monthlySpending = $user->bookings()
            ->where('status', 'Completed')
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_price) as spent, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Bookings by status
        $bookingsByStatus = [
            'Upcoming' => $upcomingBookings,
            'Completed' => $completedBookings,
            'Cancelled' => $cancelledBookings,
        ];

        // My cars stats (for drivers)
        $myCarsCount = 0;
        $myCarsRevenue = 0;
        if (in_array($user->role, ['driver', 'host'])) {
            $myCarsCount = Car::where('user_id', $user->id)->count();
            $myCarsRevenue = Booking::whereHas('car', fn($q) => $q->where('user_id', $user->id))
                ->where('status', 'Completed')
                ->sum('total_price');
        }

        // Recent bookings
        $recentBookings = $user->bookings()
            ->with('car:id,name,brand,image')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return response()->json([
            'total_bookings' => $totalBookings,
            'upcoming_bookings' => $upcomingBookings,
            'completed_bookings' => $completedBookings,
            'cancelled_bookings' => $cancelledBookings,
            'total_spent' => $totalSpent,
            'balance' => $user->balance,
            'monthly_spending' => $monthlySpending,
            'bookings_by_status' => $bookingsByStatus,
            'my_cars_count' => $myCarsCount,
            'my_cars_revenue' => $myCarsRevenue,
            'recent_bookings' => $recentBookings,
        ]);
    }
}
