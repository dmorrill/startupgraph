<?php

use App\Http\Controllers\Api\McpController;
use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\ApiCors;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // The hosted MCP endpoint lives at the root path (startupgraph.dev/mcp)
            // with API middleware + Sanctum auth — agents connect with URL + token.
            Route::middleware(['api', 'auth:sanctum'])
                ->post('/mcp', [McpController::class, 'handle']);
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.auth' => AdminAuth::class,
        ]);

        // Add CORS and rate limiting to all API routes
        $middleware->api(prepend: [
            ApiCors::class,
            ThrottleRequests::class.':api',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
