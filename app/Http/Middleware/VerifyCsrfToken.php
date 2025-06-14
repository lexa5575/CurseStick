<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Payment webhooks that come from external services
        'payment/ipn',
        // Temporary: exclude checkout for testing
        'api/checkout/*',
    ];

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     */
    protected function inExceptArray($request): bool
    {
        // First check the standard except array
        if (parent::inExceptArray($request)) {
            return true;
        }

        // For API-like routes in web middleware, we still want CSRF protection
        // but we need to ensure the token is being read correctly from headers
        $path = $request->path();
        if (str_starts_with($path, 'api/')) {
            // For AJAX requests to API endpoints, check if CSRF token is in headers
            if ($request->ajax() || $request->wantsJson()) {
                $token = $request->header('X-CSRF-TOKEN') ?: $request->header('X-XSRF-TOKEN');
                if ($token) {
                    // Let the parent middleware handle token validation
                    return false; // Don't skip CSRF, let it validate the token
                }
            }
        }

        return false;
    }
}