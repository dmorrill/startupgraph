<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * Uses HTTP Basic Auth with credentials from environment variables.
     * Set ADMIN_USERNAME and ADMIN_PASSWORD in your .env file.
     *
     * Rate-limited to 5 failed attempts per minute per IP to prevent brute-force attacks.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $username = config('admin.username');
        $password = config('admin.password');

        // If no admin credentials are configured, deny access
        if (empty($username) || empty($password)) {
            return response('Admin credentials not configured.', 503);
        }

        // Rate limit by IP address
        $rateLimiter = app(RateLimiter::class);
        $key = 'admin-auth:'.$request->ip();

        if ($rateLimiter->tooManyAttempts($key, 5)) {
            $retryAfter = $rateLimiter->availableIn($key);

            return response('Too many authentication attempts. Please try again later.', 429, [
                'Retry-After' => $retryAfter,
            ]);
        }

        // Check for HTTP Basic Auth credentials using timing-safe comparison
        $providedUser = $request->getUser() ?? '';
        $providedPass = $request->getPassword() ?? '';

        if (! hash_equals($username, $providedUser) || ! hash_equals($password, $providedPass)) {
            $rateLimiter->hit($key, 60);

            return response('Unauthorized.', 401, ['WWW-Authenticate' => 'Basic realm="Admin Area"']);
        }

        // Clear rate limit on successful auth
        $rateLimiter->clear($key);

        return $next($request);
    }
}
