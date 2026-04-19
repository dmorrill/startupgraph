<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait LogsErrors
{
    /**
     * Log an error with standardized context.
     */
    protected function logError(string $message, \Throwable $exception = null, array $context = []): void
    {
        $logContext = array_merge([
            'class' => static::class,
            'method' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? 'unknown',
        ], $context);

        if ($exception) {
            $logContext['exception'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
            ];
        }

        Log::error($message, $logContext);
    }

    /**
     * Log a warning with standardized context.
     */
    protected function logWarning(string $message, array $context = []): void
    {
        $logContext = array_merge([
            'class' => static::class,
            'method' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? 'unknown',
        ], $context);

        Log::warning($message, $logContext);
    }

    /**
     * Log info with standardized context.
     */
    protected function logInfo(string $message, array $context = []): void
    {
        $logContext = array_merge([
            'class' => static::class,
            'method' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? 'unknown',
        ], $context);

        Log::info($message, $logContext);
    }
}