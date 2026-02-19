<?php

namespace App\Services\BulkImport;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WikipediaListsImporter extends BaseBulkImporter
{
    private const WIKIPEDIA_API = 'https://en.wikipedia.org/w/api.php';

    /**
     * Wikipedia list articles that contain tables/lists of companies.
     * We parse the wikitext to extract company names.
     */
    private const LIST_PAGES = [
        'List_of_unicorn_startup_companies' => 'operating',
        'List_of_startup_accelerators' => 'operating',
        'List_of_largest_technology_companies_by_revenue' => 'operating',
        'List_of_largest_Internet_companies' => 'operating',
        'List_of_mergers_and_acquisitions_by_Alphabet' => 'acquired',
        'List_of_mergers_and_acquisitions_by_Meta_Platforms' => 'acquired',
        'List_of_mergers_and_acquisitions_by_Apple' => 'acquired',
        'List_of_mergers_and_acquisitions_by_Microsoft' => 'acquired',
        'List_of_mergers_and_acquisitions_by_Amazon' => 'acquired',
        'List_of_mergers_and_acquisitions_by_Oracle' => 'acquired',
        'List_of_mergers_and_acquisitions_by_Cisco_Systems' => 'acquired',
        'List_of_mergers_and_acquisitions_by_Salesforce' => 'acquired',
        'List_of_mergers_and_acquisitions_by_IBM' => 'acquired',
        'List_of_mergers_and_acquisitions_by_Intel' => 'acquired',
    ];

    private const CATEGORIES = [
        // Tech companies by decade/era
        'Category:Software_companies_of_the_United_States' => 'operating',
        'Category:Cloud_computing_providers' => 'operating',
        'Category:Artificial_intelligence_companies' => 'operating',
        'Category:Companies_listed_on_the_Nasdaq' => 'operating',
        'Category:Companies_listed_on_the_New_York_Stock_Exchange' => 'operating',
        'Category:Y_Combinator_companies' => 'operating',
        'Category:Techstars_companies' => 'operating',
        'Category:500_Startups_companies' => 'operating',
        // By year (older range than existing importer)
        'Category:Technology_companies_established_in_2010' => 'operating',
        'Category:Technology_companies_established_in_2011' => 'operating',
        'Category:Technology_companies_established_in_2012' => 'operating',
        'Category:Technology_companies_established_in_2013' => 'operating',
        'Category:Technology_companies_established_in_2014' => 'operating',
        'Category:Technology_companies_established_in_2015' => 'operating',
        'Category:Technology_companies_established_in_2016' => 'operating',
        'Category:Technology_companies_established_in_2017' => 'operating',
        'Category:Technology_companies_established_in_2018' => 'operating',
        'Category:Technology_companies_established_in_2019' => 'operating',
        // SaaS / Cloud
        'Category:Software_as_a_service' => 'operating',
        'Category:Cloud_computing' => 'operating',
        'Category:Internet_of_things_companies' => 'operating',
        'Category:Cybersecurity_companies' => 'operating',
        'Category:Fintech_companies' => 'operating',
        'Category:Cryptocurrency_companies' => 'operating',
        // Defunct
        'Category:Defunct_software_companies_of_the_United_States' => 'closed',
        'Category:Defunct_dot-com_companies' => 'closed',
    ];

    public function source(): string
    {
        return 'wikipedia-lists';
    }

    public function import(array $options = []): void
    {
        Log::info("Wikipedia Lists + Extended Categories import starting");

        // Import from list pages
        foreach (self::LIST_PAGES as $page => $status) {
            Log::info("Importing from list page: {$page}");
            $this->importListPage($page, $status);
            $this->rateLimitSleep(1.0);
        }

        // Import from categories
        foreach (self::CATEGORIES as $category => $status) {
            Log::info("Importing from category: {$category}");
            $this->importCategory($category, $status);
            $this->rateLimitSleep(1.0);
        }

        Log::info("Wikipedia Lists import complete: {$this->created} created, {$this->updated} updated");
    }

    private function importListPage(string $page, string $defaultStatus): void
    {
        // Get the wikitext and parse links from it
        $response = Http::withHeaders([
            'User-Agent' => 'StartupGraph/1.0 (https://startupgraph.com; research@startupgraph.com)',
        ])->timeout(15)->get(self::WIKIPEDIA_API, [
            'action' => 'parse',
            'page' => $page,
            'prop' => 'links',
            'format' => 'json',
        ]);

        if (!$response->successful()) return;

        $data = $response->json();
        $links = $data['parse']['links'] ?? [];

        foreach ($links as $link) {
            $ns = $link['ns'] ?? -1;
            if ($ns !== 0) continue; // Only article namespace

            $title = $link['*'] ?? '';
            if (!$title || strlen($title) < 2) continue;

            // Skip non-company pages
            if ($this->isNonCompanyPage($title)) continue;

            $this->upsertCompany([
                'name' => $title,
                'status' => $defaultStatus,
                'description' => null,
                'website' => null,
            ]);
        }
    }

    private function importCategory(string $category, string $defaultStatus): void
    {
        $cmcontinue = null;

        do {
            $params = [
                'action' => 'query',
                'list' => 'categorymembers',
                'cmtitle' => $category,
                'cmlimit' => 500,
                'cmtype' => 'page',
                'cmnamespace' => 0,
                'format' => 'json',
            ];

            if ($cmcontinue) {
                $params['cmcontinue'] = $cmcontinue;
            }

            $response = Http::withHeaders([
                'User-Agent' => 'StartupGraph/1.0 (https://startupgraph.com; research@startupgraph.com)',
            ])->timeout(15)->get(self::WIKIPEDIA_API, $params);

            if (!$response->successful()) break;

            $data = $response->json();
            $members = $data['query']['categorymembers'] ?? [];

            foreach ($members as $member) {
                $title = $member['title'] ?? '';
                if (!$title || $this->isNonCompanyPage($title)) continue;

                $this->upsertCompany([
                    'name' => $title,
                    'status' => $defaultStatus,
                    'description' => null,
                    'website' => null,
                ]);
            }

            $cmcontinue = $data['continue']['cmcontinue'] ?? null;
            $this->rateLimitSleep(0.5);

        } while ($cmcontinue);
    }

    private function isNonCompanyPage(string $title): bool
    {
        $skipPatterns = [
            '/^List of/i',
            '/^Category:/i',
            '/^Template:/i',
            '/^File:/i',
            '/^Wikipedia:/i',
            '/^Portal:/i',
            '/^Index of/i',
            '/^Comparison of/i',
            '/^History of/i',
            '/^Timeline of/i',
            '/\(disambiguation\)/i',
        ];

        foreach ($skipPatterns as $pattern) {
            if (preg_match($pattern, $title)) return true;
        }

        return false;
    }
}
