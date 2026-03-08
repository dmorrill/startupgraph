<?php

namespace App\Services\Discovery;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CompaniesHouseService
{
    private string $baseUrl = 'https://api.company-information.service.gov.uk';

    public function __construct(
        private ?string $apiKey = null,
    ) {
        $this->apiKey = $apiKey ?? config('services.companies_house.key');
    }

    /**
     * Search UK companies by name or SIC code.
     */
    public function search(string $query, int $startIndex = 0, int $itemsPerPage = 50): array
    {
        if (!$this->apiKey) {
            throw new \RuntimeException('Companies House API key not configured. Set COMPANIES_HOUSE_KEY in .env');
        }

        $response = Http::withBasicAuth($this->apiKey, '')
            ->get("{$this->baseUrl}/search/companies", [
                'q' => $query,
                'start_index' => $startIndex,
                'items_per_page' => $itemsPerPage,
            ]);

        if (!$response->successful()) {
            Log::error('Companies House API error', ['status' => $response->status()]);
            return ['companies' => [], 'total' => 0];
        }

        $data = $response->json();

        $companies = collect($data['items'] ?? [])->map(fn ($item) => [
            'name' => $item['title'],
            'description' => $item['description'] ?? null,
            'source' => 'companies_house_uk',
            'source_id' => $item['company_number'],
            'metadata' => [
                'company_number' => $item['company_number'],
                'company_status' => $item['company_status'] ?? null,
                'date_of_creation' => $item['date_of_creation'] ?? null,
                'address' => $item['address_snippet'] ?? null,
            ],
        ])->toArray();

        return ['companies' => $companies, 'total' => $data['total_results'] ?? 0];
    }
}
