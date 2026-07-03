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

        // Total counts
        $totalRequests = AnalyticsEvent::where('created_at', '>=', $from)->count();
        $totalPageViews = AnalyticsEvent::where('event_type', 'page_view')->where('created_at', '>=', $from)->count();
        $totalErrors = AnalyticsEvent::where('event_type', 'error')->where('created_at', '>=', $from)->count();
        $uniqueIPs = AnalyticsEvent::where('created_at', '>=', $from)->distinct('ip_address')->count('ip_address');
        $uniqueUsers = AnalyticsEvent::where('user_id', '>', 0)->where('created_at', '>=', $from)->distinct('user_id')->count('user_id');

        // Today's stats
        $todayRequests = AnalyticsEvent::where('created_at', '>=', now()->startOfDay())->count();
        $todayPageViews = AnalyticsEvent::where('event_type', 'page_view')->where('created_at', '>=', now()->startOfDay())->count();
        $todayErrors = AnalyticsEvent::where('event_type', 'error')->where('created_at', '>=', now()->startOfDay())->count();
        $todayUniqueIPs = AnalyticsEvent::where('created_at', '>=', now()->startOfDay())->distinct('ip_address')->count('ip_address');

        // Average response time
        $avgResponseTime = AnalyticsEvent::where('created_at', '>=', $from)
            ->whereNotNull('response_time_ms')
            ->avg('response_time_ms');

        // Requests per day (last N days)
        $requestsPerDay = AnalyticsEvent::where('created_at', '>=', $from)
            ->selectRaw("DATE(created_at) as date, COUNT(*) as requests, SUM(CASE WHEN event_type = 'error' THEN 1 ELSE 0 END) as errors")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top pages
        $topPages = AnalyticsEvent::where('created_at', '>=', $from)
            ->selectRaw('path, COUNT(*) as views, COUNT(DISTINCT ip_address) as unique_visitors')
            ->groupBy('path')
            ->orderByDesc('views')
            ->take(15)
            ->get();

        // Top API endpoints
        $topEndpoints = AnalyticsEvent::where('event_type', 'api_request')
            ->where('created_at', '>=', $from)
            ->selectRaw('CONCAT(method, " ", path) as endpoint, COUNT(*) as hits, AVG(response_time_ms) as avg_time')
            ->groupBy('endpoint')
            ->orderByDesc('hits')
            ->take(15)
            ->get();

        // Device breakdown
        $devices = AnalyticsEvent::where('created_at', '>=', $from)
            ->selectRaw('device_type, COUNT(*) as count')
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->get();

        // Browser breakdown
        $browsers = AnalyticsEvent::where('created_at', '>=', $from)
            ->selectRaw('browser, COUNT(*) as count')
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('count')
            ->get();

        // OS breakdown
        $operatingSystems = AnalyticsEvent::where('created_at', '>=', $from)
            ->selectRaw('os, COUNT(*) as count')
            ->whereNotNull('os')
            ->groupBy('os')
            ->orderByDesc('count')
            ->get();

        // Traffic by hour (last 24h)
        $hourlyTraffic = AnalyticsEvent::where('created_at', '>=', now()->subDay())
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as requests')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Error breakdown by path
        $errorPaths = AnalyticsEvent::where('event_type', 'error')
            ->where('created_at', '>=', $from)
            ->selectRaw('path, status_code, COUNT(*) as count')
            ->groupBy('path', 'status_code')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        // Response time percentiles (approximate)
        $slowRequests = AnalyticsEvent::where('created_at', '>=', $from)
            ->whereNotNull('response_time_ms')
            ->where('response_time_ms', '>', 1000)
            ->selectRaw('path, method, AVG(response_time_ms) as avg_time, COUNT(*) as count')
            ->groupBy('path', 'method')
            ->orderByDesc('avg_time')
            ->take(10)
            ->get();

        // User activity (new vs returning)
        $dailyActiveUsers = AnalyticsEvent::where('user_id', '>', 0)
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as date, COUNT(DISTINCT user_id) as active_users')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Referrer breakdown
        $topReferrers = AnalyticsEvent::where('created_at', '>=', $from)
            ->whereNotNull('referer')
            ->where('referer', '!=', '')
            ->selectRaw('referer, COUNT(*) as count')
            ->groupBy('referer')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        // Platform totals (all-time for context)
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
}
