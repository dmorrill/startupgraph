<?php

namespace App\Http\Controllers;

use App\Traits\LogsErrors;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    use LogsErrors;

    /**
     * Health check endpoint for monitoring system status.
     */
    public function __invoke(): JsonResponse
    {
        try {
            DB::connection()->getPdo();
            $dbOk = true;
        } catch (\Exception $e) {
            $this->logError('Database health check failed', $e, [
                'database_connection' => config('database.default'),
            ]);
            $dbOk = false;
        }

        return response()->json([
            'status' => $dbOk ? 'healthy' : 'degraded',
            'database' => $dbOk ? 'connected' : 'disconnected',
            'timestamp' => now()->toIso8601String(),
        ], $dbOk ? 200 : 503);
    }
}
