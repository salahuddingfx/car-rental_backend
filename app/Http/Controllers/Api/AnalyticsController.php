<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\User;
use App\Models\Car;
use App\Models\Booking;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function overview(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $from = now()->subDays($days);

        $totalRequests = AnalyticsEvent::where('created_at', '>=', $from)->count();
        $totalPageViews = AnalyticsEvent::where('event_type', 'page_view')->where('created_at', '>=', $from)->count();
        $totalErrors = AnalyticsEvent::where('event_type', 'error')->where('created_at', '>=', $from)->count();
        $uniqueIPs = AnalyticsEvent::where('created_at', '>=', $from)->distinct('ip_address')->count('ip_address');
        $uniqueUsers = AnalyticsEvent::where('user_id', '>', 0)->where('created_at', '>=', $from)->distinct('user_id')->count('user_id');

        $todayRequests = AnalyticsEvent::where('created_at', '>=', now()->startOfDay())->count();
        $todayPageViews = AnalyticsEvent::where('event_type', 'page_view')->where('created_at', '>=', now()->startOfDay())->count();
        $todayErrors = AnalyticsEvent::where('event_type', 'error')->where('created_at', '>=', now()->startOfDay())->count();
        $todayUniqueIPs = AnalyticsEvent::where('created_at', '>=', now()->startOfDay())->distinct('ip_address')->count('ip_address');

        $avgResponseTime = AnalyticsEvent::where('created_at', '>=', $from)
            ->whereNotNull('response_time_ms')
            ->avg('response_time_ms');

        $requestsPerDay = AnalyticsEvent::where('created_at', '>=', $from)
            ->selectRaw("DATE(created_at) as date, COUNT(*) as requests, SUM(CASE WHEN event_type = 'error' THEN 1 ELSE 0 END) as errors")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topPages = AnalyticsEvent::where('created_at', '>=', $from)
            ->selectRaw('path, COUNT(*) as views, COUNT(DISTINCT ip_address) as unique_visitors')
            ->groupBy('path')
            ->orderByDesc('views')
            ->take(15)
            ->get();

        $topEndpoints = AnalyticsEvent::where('event_type', 'api_request')
            ->where('created_at', '>=', $from)
            ->selectRaw('CONCAT(method, " ", path) as endpoint, COUNT(*) as hits, AVG(response_time_ms) as avg_time')
            ->groupBy('endpoint')
            ->orderByDesc('hits')
            ->take(15)
            ->get();

        $devices = AnalyticsEvent::where('created_at', '>=', $from)
            ->selectRaw('device_type, COUNT(*) as count')
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->get();

        $browsers = AnalyticsEvent::where('created_at', '>=', $from)
            ->selectRaw('browser, COUNT(*) as count')
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('count')
            ->get();

        $operatingSystems = AnalyticsEvent::where('created_at', '>=', $from)
            ->selectRaw('os, COUNT(*) as count')
            ->whereNotNull('os')
            ->groupBy('os')
            ->orderByDesc('count')
            ->get();

        $hourlyTraffic = AnalyticsEvent::where('created_at', '>=', now()->subDay())
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as requests')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $errorPaths = AnalyticsEvent::where('event_type', 'error')
            ->where('created_at', '>=', $from)
            ->selectRaw('path, status_code, COUNT(*) as count')
            ->groupBy('path', 'status_code')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        $slowRequests = AnalyticsEvent::where('created_at', '>=', $from)
            ->whereNotNull('response_time_ms')
            ->where('response_time_ms', '>', 1000)
            ->selectRaw('path, method, AVG(response_time_ms) as avg_time, COUNT(*) as count')
            ->groupBy('path', 'method')
            ->orderByDesc('avg_time')
            ->take(10)
            ->get();

        $dailyActiveUsers = AnalyticsEvent::where('user_id', '>', 0)
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as date, COUNT(DISTINCT user_id) as active_users')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topReferrers = AnalyticsEvent::where('created_at', '>=', $from)
            ->whereNotNull('referer')
            ->where('referer', '!=', '')
            ->selectRaw('referer, COUNT(*) as count')
            ->groupBy('referer')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        $platformTotals = [
            'total_users' => User::count(),
            'total_cars' => Car::count(),
            'total_bookings' => Booking::count(),
            'total_revenue' => Booking::where('status', 'Completed')->sum('total_price'),
        ];

        return response()->json([
            'period_days' => $days,
            'summary' => [
                'total_requests' => $totalRequests,
                'total_page_views' => $totalPageViews,
                'total_errors' => $totalErrors,
                'unique_visitors' => $uniqueIPs,
                'unique_users' => $uniqueUsers,
                'avg_response_time_ms' => round($avgResponseTime ?? 0),
                'error_rate' => $totalRequests > 0 ? round(($totalErrors / $totalRequests) * 100, 2) : 0,
            ],
            'today' => [
                'requests' => $todayRequests,
                'page_views' => $todayPageViews,
                'errors' => $todayErrors,
                'unique_visitors' => $todayUniqueIPs,
            ],
            'requests_per_day' => $requestsPerDay,
            'top_pages' => $topPages,
            'top_endpoints' => $topEndpoints,
            'devices' => $devices,
            'browsers' => $browsers,
            'operating_systems' => $operatingSystems,
            'hourly_traffic' => $hourlyTraffic,
            'error_paths' => $errorPaths,
            'slow_requests' => $slowRequests,
            'daily_active_users' => $dailyActiveUsers,
            'top_referrers' => $topReferrers,
            'platform' => $platformTotals,
        ]);
    }

    public function bookingTrends(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $from = now()->subDays($days);

        $bookingsPerDay = Booking::where('created_at', '>=', $from)
            ->selectRaw("DATE(created_at) as date, COUNT(*) as bookings, SUM(total_price) as revenue, SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed, SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $bookingsByStatus = Booking::selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->get();

        $monthlyRevenue = Booking::where('status', 'Completed')
            ->where('created_at', '>=', $from)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_price) as revenue, COUNT(*) as bookings")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $avgBookingValue = Booking::where('status', 'Completed')
            ->where('created_at', '>=', $from)
            ->avg('total_price');

        $avgRentalDuration = Booking::where('created_at', '>=', $from)
            ->avg('total_days');

        $topCars = Booking::where('created_at', '>=', $from)
            ->where('status', 'Completed')
            ->join('cars', 'bookings.car_id', '=', 'cars.id')
            ->selectRaw('cars.id, cars.name, cars.brand, cars.image, COUNT(*) as bookings, SUM(bookings.total_price) as revenue')
            ->groupBy('cars.id', 'cars.name', 'cars.brand', 'cars.image')
            ->orderByDesc('bookings')
            ->take(10)
            ->get();

        $peakHours = Booking::where('created_at', '>=', $from)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as bookings')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return response()->json([
            'bookings_per_day' => $bookingsPerDay,
            'bookings_by_status' => $bookingsByStatus,
            'monthly_revenue' => $monthlyRevenue,
            'avg_booking_value' => round($avgBookingValue ?? 0, 2),
            'avg_rental_duration' => round($avgRentalDuration ?? 0, 1),
            'top_cars' => $topCars,
            'peak_hours' => $peakHours,
        ]);
    }

    public function userGrowth(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $from = now()->subDays($days);

        $registrationsPerDay = User::where('created_at', '>=', $from)
            ->selectRaw("DATE(created_at) as date, COUNT(*) as registrations")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalUsers = User::count();
        $newUsersThisPeriod = User::where('created_at', '>=', $from)->count();
        $activeUsers = User::where('last_login_at', '>=', $from)->count();

        $usersByRole = User::selectRaw("role, COUNT(*) as count")
            ->groupBy('role')
            ->get();

        $verifiedVsUnverified = User::selectRaw("CASE WHEN email_verified_at IS NOT NULL THEN 'verified' ELSE 'unverified' END as status, COUNT(*) as count")
            ->groupBy('status')
            ->get();

        return response()->json([
            'registrations_per_day' => $registrationsPerDay,
            'total_users' => $totalUsers,
            'new_users_this_period' => $newUsersThisPeriod,
            'active_users' => $activeUsers,
            'users_by_role' => $usersByRole,
            'verified_vs_unverified' => $verifiedVsUnverified,
        ]);
    }

    public function carUtilization(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $from = now()->subDays($days);

        $totalCars = Car::count();
        $activeCars = Car::where('is_available', true)->count();

        $carsWithBookings = Booking::where('created_at', '>=', $from)
            ->where('status', '!=', 'Cancelled')
            ->distinct('car_id')
            ->count('car_id');

        $utilizationRate = $totalCars > 0 ? round(($carsWithBookings / $totalCars) * 100, 1) : 0;

        $carPerformance = Car::withCount(['bookings' => function ($query) use ($from) {
            $query->where('created_at', '>=', $from)->where('status', '!=', 'Cancelled');
        }])
        ->withSum(['bookings' => function ($query) use ($from) {
            $query->where('created_at', '>=', $from)->where('status', 'Completed');
        }], 'total_price')
        ->orderByDesc('bookings_count')
        ->take(15)
        ->get()
        ->map(fn ($car) => [
            'id' => $car->id,
            'name' => $car->name,
            'brand' => $car->brand,
            'image' => $car->image,
            'bookings_count' => $car->bookings_count,
            'total_revenue' => $car->bookings_sum_total_price ?? 0,
            'rating' => $car->rating,
        ]);

        $categoryPerformance = Car::selectRaw("category, COUNT(*) as total_cars, AVG(price) as avg_price, AVG(rating) as avg_rating")
            ->groupBy('category')
            ->orderByDesc('total_cars')
            ->get();

        $brandPerformance = Car::selectRaw("brand, COUNT(*) as total_cars, AVG(price) as avg_price, AVG(rating) as avg_rating")
            ->groupBy('brand')
            ->orderByDesc('total_cars')
            ->take(10)
            ->get();

        return response()->json([
            'total_cars' => $totalCars,
            'active_cars' => $activeCars,
            'cars_with_bookings' => $carsWithBookings,
            'utilization_rate' => $utilizationRate,
            'car_performance' => $carPerformance,
            'category_performance' => $categoryPerformance,
            'brand_performance' => $brandPerformance,
        ]);
    }
}
