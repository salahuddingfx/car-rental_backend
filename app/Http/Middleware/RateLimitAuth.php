<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->input('email') ?? $request->ip();

        if (RateLimiter::tooManyAttempts('auth:' . $key, 5)) {
            $seconds = RateLimiter::availableIn('auth:' . $key);

            return response()->json([
                'message' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.',
            ], 429);
        }

        RateLimiter::hit('auth:' . $key, 60);

        return $next($request);
    }
}
