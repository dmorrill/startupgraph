<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinkedInService
{
    private const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    public function fetchHeadcount(string $linkedinUrl): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => self::USER_AGENT,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
            ])->timeout(30)->get($linkedinUrl);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'headcount' => null,
                    'error' => "HTTP {$response->status()}",
                ];
            }

            $html = $response->body();
            $headcount = $this->parseHeadcount($html);

            if ($headcount === null) {
                return [
                    'success' => false,
                    'headcount' => null,
                    'error' => 'Could not extract headcount from page',
                ];
            }

            return [
                'success' => true,
                'headcount' => $headcount,
                'error' => null,
            ];
        } catch (\Exception $e) {
            Log::warning("LinkedIn fetch error for {$linkedinUrl}: {$e->getMessage()}");

            return [
                'success' => false,
                'headcount' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function parseHeadcount(string $html): ?int
    {
        // Look for JSON-LD schema with numberOfEmployees
        if (preg_match('/"numberOfEmployees"\s*:\s*\{\s*"value"\s*:\s*(\d+)/', $html, $matches)) {
            return (int) $matches[1];
        }

        // Fallback: look for "X employees" pattern
        if (preg_match('/(\d{1,3}(?:,\d{3})*)\s+employees/i', $html, $matches)) {
            return (int) str_replace(',', '', $matches[1]);
        }

        return null;
    }
}
