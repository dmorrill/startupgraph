<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * Uses HTTP Basic Auth with credentials from environment variables.
     * Set ADMIN_USERNAME and ADMIN_PASSWORD in your .env file.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $username = config('admin.username');
        $password = config('admin.password');

        // If no admin credentials are configured, deny access
        if (empty($username) || empty($password)) {
            return response('Admin credentials not configured.', 503);
        }

        // Check for HTTP Basic Auth credentials
        if ($request->getUser() !== $username || $request->getPassword() !== $password) {
            return response('Unauthorized.', 401, ['WWW-Authenticate' => 'Basic realm="Admin Area"']);
        }

        return $next($request);
    }
}
