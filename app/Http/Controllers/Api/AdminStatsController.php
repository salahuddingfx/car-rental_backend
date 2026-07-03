<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Car;
use App\Models\Booking;
use Illuminate\Http\Request;

class AdminStatsController extends Controller
{
    public function index(Request $request)
    {
        $totalUsers = User::count();
        $totalCars = Car::count();
        $totalBookings = Booking::count();
        $activeBookings = Booking::whereIn('status', ['Upcoming', 'Active'])->count();
        $completedBookings = Booking::where('status', 'Completed')->count();
        $cancelledBookings = Booking::where('status', 'Cancelled')->count();

        $totalRevenue = Booking::where('status', 'Completed')->sum('total_price');
        $avgBookingValue = $totalBookings > 0 ? $totalRevenue / $completedBookings : 0;

        // Monthly revenue for last 12 months
        $monthlyRevenue = Booking::where('status', 'Completed')
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_price) as revenue, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Bookings by status
        $bookingsByStatus = [
            'Upcoming' => Booking::where('status', 'Upcoming')->count(),
            'Active' => Booking::where('status', 'Active')->count(),
            'Completed' => $completedBookings,
            'Cancelled' => $cancelledBookings,
        ];

        // Top cars by bookings
        $topCars = Car::withCount('bookings')
            ->withSum('bookings', 'total_price')
            ->orderByDesc('bookings_count')
            ->take(5)
            ->get()
            ->map(fn($car) => [
                'id' => $car->id,
                'name' => $car->name,
                'brand' => $car->brand,
                'category' => $car->category,
                'image' => $car->image,
                'rating' => $car->rating,
                'bookings_count' => $car->bookings_count,
                'total_revenue' => $car->bookings_sum_total_price ?? 0,
            ]);

        // Cars by category
        $categories = Car::selectRaw('category, COUNT(*) as count, SUM(price) as total_price')
            ->groupBy('category')
            ->get();

        // Recent bookings
        $recentBookings = Booking::with(['car:id,name,brand,image', 'user:id,name'])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return response()->json([
            'total_users' => $totalUsers,
            'total_cars' => $totalCars,
            'total_bookings' => $totalBookings,
            'active_bookings' => $activeBookings,
            'completed_bookings' => $completedBookings,
            'cancelled_bookings' => $cancelledBookings,
            'total_revenue' => $totalRevenue,
            'avg_booking_value' => round($avgBookingValue, 2),
            'monthly_revenue' => $monthlyRevenue,
            'bookings_by_status' => $bookingsByStatus,
            'top_cars' => $topCars,
            'categories' => $categories,
            'recent_bookings' => $recentBookings,
        ]);
    }
}
