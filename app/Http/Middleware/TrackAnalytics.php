<?php

namespace App\Http\Middleware;

use App\Models\AnalyticsEvent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackAnalytics
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $this->track($request, $response, $startTime);

        return $response;
    }

    private function track(Request $request, Response $response, float $startTime): void
    {
        try {
            $path = $request->path();
            $method = $request->method();

            // Skip tracking for analytics itself, uploads, and health checks
            if (str_starts_with($path, 'admin/analytics') ||
                str_starts_with($path, 'upload') ||
                $path === 'up') {
                return;
            }

            $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);
            $ua = $request->userAgent() ?: '';
            $deviceType = $this->parseDeviceType($ua);
            $browser = $this->parseBrowser($ua);
            $os = $this->parseOS($ua);

            AnalyticsEvent::create([
                'event_type' => $response->getStatusCode() >= 400 ? 'error' : 'api_request',
                'path' => $path,
                'method' => $method,
                'status_code' => $response->getStatusCode(),
                'user_id' => $request->user()?->id,
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr($ua, 0, 500),
                'referer' => $request->header('referer'),
                'device_type' => $deviceType,
                'browser' => $browser,
                'os' => $os,
                'response_time_ms' => $responseTimeMs,
            ]);
        } catch (\Throwable $e) {
            // Never break the request for analytics
        }
    }

    private function parseDeviceType(string $ua): string
    {
        if (preg_match('/tablet|ipad/i', $ua)) return 'tablet';
        if (preg_match('/mobile|android|iphone|phone/i', $ua)) return 'mobile';
        return 'desktop';
    }

    private function parseBrowser(string $ua): string
    {
        if (preg_match('/Edg(e)?\//i', $ua)) return 'Edge';
        if (preg_match('/Chrome/i', $ua)) return 'Chrome';
        if (preg_match('/Firefox/i', $ua)) return 'Firefox';
        if (preg_match('/Safari/i', $ua)) return 'Safari';
        if (preg_match('/Opera|OPR/i', $ua)) return 'Opera';
        return 'Other';
    }

    private function parseOS(string $ua): string
    {
        if (preg_match('/Windows/i', $ua)) return 'Windows';
        if (preg_match('/Mac OS/i', $ua)) return 'macOS';
        if (preg_match('/Linux/i', $ua)) return 'Linux';
        if (preg_match('/Android/i', $ua)) return 'Android';
        if (preg_match('/iPhone|iPad/i', $ua)) return 'iOS';
        return 'Other';
    }
}
