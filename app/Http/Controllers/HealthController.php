<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::connection()->getPdo();
            $dbOk = true;
        } catch (\Exception $e) {
            $dbOk = false;
        }

        return response()->json([
            'status' => $dbOk ? 'healthy' : 'degraded',
            'database' => $dbOk ? 'connected' : 'disconnected',
            'timestamp' => now()->toIso8601String(),
        ], $dbOk ? 200 : 503);
    }
}
