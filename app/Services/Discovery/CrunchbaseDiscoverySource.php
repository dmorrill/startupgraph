<?php

namespace App\Services\Discovery;

use App\Contracts\CompanyDiscoverySource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CrunchbaseDiscoverySource implements CompanyDiscoverySource
{
    private const API_BASE = 'https://api.crunchbase.com/api/v4';
    private const ODM_BASE = 'https://api.crunchbase.com/odm/v4';

    public function name(): string
    {
        return 'crunchbase';
    }

    public function discover(int $days = 7): array
    {
        $apiKey = config('services.crunchbase.api_key');

        if (empty($apiKey)) {
            Log::info('Crunchbase discovery skipped: CRUNCHBASE_API_KEY not set');
            return [];
        }

        try {
            return $this->discoverViaApi($apiKey, $days);
        } catch (\Exception $e) {
            Log::warning("Crunchbase discovery error: {$e->getMessage()}");
            return [];
        }
    }

    private function discoverViaApi(string $apiKey, int $days): array
    {
        $sinceDate = now()->subDays($days)->toDateString();

        // Use the autocomplete/search endpoint for recently funded orgs
        $response = Http::withHeaders([
            'X-cb-user-key' => $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->retry(2, 1000)->post(self::API_BASE . '/searches/organizations', [
            'field_ids' => [
                'identifier',
                'short_description',
                'location_identifiers',
                'website_url',
                'last_funding_type',
                'last_funding_total',
                'founded_on',
            ],
            'order' => [
                [
                    'field_id' => 'last_funding_at',
                    'sort' => 'desc',
                ],
            ],
            'query' => [
                [
                    'type' => 'predicate',
                    'field_id' => 'last_funding_at',
                    'operator_id' => 'gte',
                    'values' => [$sinceDate],
                ],
                [
                    'type' => 'predicate',
                    'field_id' => 'last_funding_type',
                    'operator_id' => 'includes',
                    'values' => ['seed', 'pre_seed', 'series_a', 'series_b', 'angel'],
                ],
            ],
            'limit' => 50,
        ]);

        if ($response->status() === 401 || $response->status() === 403) {
            Log::warning('Crunchbase API key invalid or insufficient permissions');
            return [];
        }

        if ($response->status() === 429) {
            Log::warning('Crunchbase API rate limit hit, backing off');
            return [];
        }

        if (!$response->successful()) {
            Log::warning("Crunchbase API error: HTTP {$response->status()}");
            return $this->discoverViaOdm($apiKey, $days);
        }

        $data = $response->json();
        $entities = $data['entities'] ?? [];

        return $this->parseEntities($entities);
    }

    /**
     * Fallback: use the ODM (Open Data Map) endpoint.
     */
    private function discoverViaOdm(string $apiKey, int $days): array
    {
        try {
            $sinceDate = now()->subDays($days)->toDateString();

            $response = Http::withHeaders([
                'X-cb-user-key' => $apiKey,
            ])->timeout(30)->get(self::ODM_BASE . '/odm-organizations', [
                'updated_after' => $sinceDate,
                'sort_order' => 'updated_at DESC',
            ]);

            if (!$response->successful()) {
                Log::warning("Crunchbase ODM error: HTTP {$response->status()}");
                return [];
            }

            $data = $response->json();
            $entities = $data['entities'] ?? [];

            return $this->parseEntities($entities);
        } catch (\Exception $e) {
            Log::warning("Crunchbase ODM error: {$e->getMessage()}");
            return [];
        }
    }

    private function parseEntities(array $entities): array
    {
        $companies = [];

        foreach ($entities as $entity) {
            $props = $entity['properties'] ?? [];
            $name = $props['identifier']['value'] ?? ($props['name'] ?? null);

            if (!$name) {
                continue;
            }

            $company = [
                'name' => $name,
                'description' => $props['short_description'] ?? null,
                'website' => $props['website_url'] ?? null,
                'source_url' => isset($props['identifier']['permalink'])
                    ? "https://www.crunchbase.com/organization/{$props['identifier']['permalink']}"
                    : null,
            ];

            // Extract location
            $locations = $props['location_identifiers'] ?? [];
            if (!empty($locations)) {
                $locationParts = array_column($locations, 'value');
                $company['location'] = implode(', ', $locationParts);
            }

            // Extract funding info
            $fundingTotal = $props['last_funding_total'] ?? null;
            if ($fundingTotal) {
                $company['funding_amount'] = $fundingTotal['value'] ?? null;
            }

            $fundingType = $props['last_funding_type'] ?? null;
            if ($fundingType) {
                $company['funding_round'] = str_replace('_', ' ', $fundingType);
            }

            $companies[] = $company;
        }

        return $companies;
    }
}
