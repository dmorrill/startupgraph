<?php

namespace App\Services\BulkImport;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenCorporatesBulkImporter extends BaseBulkImporter
{
    private const BASE_URL = 'https://api.opencorporates.com/v0.4';

    private const KEYWORDS = [
        'technology',
        'software',
        'artificial intelligence',
        'machine learning',
        'saas',
        'cloud computing',
        'fintech',
        'biotech',
        'startup',
    ];

    private const JURISDICTIONS = ['us_de', 'us_ca', 'us_ny', 'us_wa'];

    // Free tier: 500 requests/day with API key, 50 without
    private const RATE_LIMIT_SECONDS = 8.0; // ~450 requests/hour, well within limits

    private const PER_PAGE = 30;

    public function source(): string
    {
        return 'opencorporates';
    }

    public function import(array $options = []): void
    {
        $apiToken = config('services.opencorporates.api_token', env('OPENCORPORATES_API_TOKEN'));
        $maxPages = $options['max_pages'] ?? 5; // Conservative default for free tier
        $createdSince = $options['created_since'] ?? '2020-01-01';

        Log::info('OpenCorporates import starting', [
            'has_token' => ! empty($apiToken),
            'max_pages' => $maxPages,
            'created_since' => $createdSince,
        ]);

        if (empty($apiToken)) {
            Log::warning('OpenCorporates: No API token configured. Set OPENCORPORATES_API_TOKEN in .env');
            Log::warning('OpenCorporates: Get a free API key at https://opencorporates.com/users/sign_up');

            return;
        }

        foreach (self::JURISDICTIONS as $jurisdiction) {
            foreach (self::KEYWORDS as $keyword) {
                $this->searchAndImport($keyword, $jurisdiction, $createdSince, $apiToken, $maxPages);
            }
        }

        Log::info('OpenCorporates import complete', $this->getStats());
    }

    private function searchAndImport(
        string $query,
        string $jurisdiction,
        string $createdSince,
        ?string $apiToken,
        int $maxPages
    ): void {
        for ($page = 1; $page <= $maxPages; $page++) {
            $params = [
                'q' => $query,
                'jurisdiction_code' => $jurisdiction,
                'created_since' => $createdSince,
                'per_page' => self::PER_PAGE,
                'page' => $page,
                'order' => 'incorporation_date',
            ];

            if ($apiToken) {
                $params['api_token'] = $apiToken;
            }

            try {
                $response = Http::timeout(30)->get(self::BASE_URL.'/companies/search', $params);

                if ($response->status() === 401 || $response->status() === 403) {
                    Log::warning('OpenCorporates: API token required or invalid. Stopping.');

                    return;
                }

                if ($response->status() === 429) {
                    Log::warning("OpenCorporates: Rate limit hit. Stopping search for {$query}/{$jurisdiction}.");

                    return;
                }

                if (! $response->successful()) {
                    Log::warning("OpenCorporates: HTTP {$response->status()} for {$query}/{$jurisdiction} page {$page}");
                    break;
                }

                $data = $response->json();
                $companies = $data['results']['companies'] ?? [];

                if (empty($companies)) {
                    break;
                }

                foreach ($companies as $item) {
                    $company = $item['company'] ?? $item;
                    $this->importCompany($company, $jurisdiction);
                }

                $totalPages = $data['results']['total_pages'] ?? $page;
                if ($page >= $totalPages) {
                    break;
                }

                // Update checkpoint
                $this->importLog->update([
                    'last_offset' => "{$jurisdiction}:{$query}:{$page}",
                    'total_processed' => $this->processed,
                    'companies_created' => $this->created,
                ]);

                $this->rateLimitSleep(self::RATE_LIMIT_SECONDS);

            } catch (\Exception $e) {
                Log::error("OpenCorporates: Error fetching {$query}/{$jurisdiction} page {$page}: {$e->getMessage()}");
                break;
            }
        }
    }

    private function importCompany(array $company, string $jurisdiction): void
    {
        $name = $company['name'] ?? null;
        if (! $name) {
            return;
        }

        // Clean up ALL CAPS names common in corporate registries
        if ($name === strtoupper($name) && strlen($name) > 3) {
            $name = ucwords(strtolower($name));
        }

        // Remove common suffixes for cleaner names but keep original for matching
        $status = $this->mapOpenCorpStatus($company['current_status'] ?? '');

        $state = $this->jurisdictionToState($jurisdiction);
        $country = str_starts_with($jurisdiction, 'us_') ? 'US' : null;

        $foundedDate = $company['incorporation_date'] ?? null;

        $address = $company['registered_address'] ?? [];
        $city = $address['locality'] ?? null;

        $this->upsertCompany([
            'name' => trim($name),
            'founded_date' => $foundedDate,
            'city' => $city,
            'state' => $state,
            'country' => $country,
            'status' => $status,
        ]);
    }

    private function mapOpenCorpStatus(string $status): string
    {
        $status = strtolower(trim($status));

        $map = [
            'active' => 'operating',
            'good standing' => 'operating',
            'in good standing' => 'operating',
            'current' => 'operating',
            'dissolved' => 'closed',
            'inactive' => 'closed',
            'revoked' => 'closed',
            'forfeited' => 'closed',
            'cancelled' => 'closed',
            'withdrawn' => 'closed',
            'merged' => 'acquired',
            'converted' => 'acquired',
        ];

        return $map[$status] ?? 'operating';
    }

    private function jurisdictionToState(string $jurisdiction): ?string
    {
        $map = [
            'us_de' => 'DE',
            'us_ca' => 'CA',
            'us_ny' => 'NY',
            'us_wa' => 'WA',
        ];

        return $map[$jurisdiction] ?? null;
    }
}
