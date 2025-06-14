<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleApiInWebRoutes
{
    /**
     * Handle an incoming request.
     * 
     * This middleware detects API-like routes in web middleware group
     * and ensures they return proper JSON responses instead of Inertia responses.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if this is an API-like route in web middleware
        if ($this->isApiLikeRoute($request)) {
            // Set headers to ensure JSON response
            $request->headers->set('Accept', 'application/json');
            $request->headers->set('X-Requested-With', 'XMLHttpRequest');
            
            // Prevent Inertia from processing this request
            $request->headers->remove('X-Inertia');
            $request->headers->remove('X-Inertia-Version');
        }

        return $next($request);
    }

    /**
     * Determine if the current route is an API-like route
     */
    private function isApiLikeRoute(Request $request): bool
    {
        $path = $request->path();
        
        // API-like routes that should return JSON even in web middleware
        $apiLikePatterns = [
            'api/cart/*',
            'api/checkout/*',
            'api/coupons/*',
        ];

        foreach ($apiLikePatterns as $pattern) {
            if ($this->matchesPattern($path, $pattern)) {
                return true;
            }
        }

        // Also check if request explicitly wants JSON
        return $request->wantsJson() || $request->ajax();
    }

    /**
     * Check if path matches pattern with wildcards
     */
    private function matchesPattern(string $path, string $pattern): bool
    {
        // Convert pattern to regex
        $regex = str_replace(['*', '/'], ['[^/]*', '\/'], $pattern);
        $regex = '/^' . $regex . '$/';
        
        return preg_match($regex, $path);
    }
}