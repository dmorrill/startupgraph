<?php

namespace App\Services\Discovery;

use App\Contracts\CompanyDiscoverySource;
use App\Services\TechCrunchService;
use Illuminate\Support\Facades\Log;

class TechCrunchDiscoverySource implements CompanyDiscoverySource
{
    private const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    public function __construct(
        private TechCrunchService $techCrunchService,
    ) {}

    public function name(): string
    {
        return 'techcrunch';
    }

    public function discover(int $days = 7): array
    {
        $result = $this->techCrunchService->scrapeFundraisingArticles();

        if (! $result['success']) {
            Log::warning("TechCrunch discovery failed: {$result['error']}");

            return [];
        }

        $companies = [];

        foreach ($result['articles'] as $article) {
            $extracted = $this->extractCompanyFromArticle($article);
            if ($extracted) {
                $companies[] = $extracted;
            }
        }

        return $companies;
    }

    private function extractCompanyFromArticle(array $article): ?array
    {
        $title = $article['title'] ?? '';

        // Common patterns: "CompanyName raises $X million in Series Y"
        // "CompanyName lands $X seed round"
        // "CompanyName nabs $X to do something"
        $patterns = [
            '/^(.+?)\s+raises?\s+\$/i',
            '/^(.+?)\s+lands?\s+\$/i',
            '/^(.+?)\s+nabs?\s+\$/i',
            '/^(.+?)\s+secures?\s+\$/i',
            '/^(.+?)\s+closes?\s+\$/i',
            '/^(.+?)\s+gets?\s+\$/i',
            '/^(.+?)\s+bags?\s+\$/i',
            '/^(.+?)\s+grabs?\s+\$/i',
            '/^(.+?)\s+hauls?\s+in\s+\$/i',
            '/^(.+?)\s+snags?\s+\$/i',
        ];

        $companyName = null;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $title, $match)) {
                $companyName = trim($match[1]);
                // Clean up common prefixes
                $companyName = preg_replace('/^(Exclusive:\s*|Report:\s*)/i', '', $companyName);
                $companyName = trim($companyName);
                break;
            }
        }

        if (! $companyName || strlen($companyName) < 2 || strlen($companyName) > 100) {
            return null;
        }

        // Skip names that are clearly not company names (too many words = sentence fragment)
        if (str_word_count($companyName) > 5) {
            return null;
        }

        // Strip leading common noise words
        $companyName = preg_replace('/^(AI\s+\w+\s+company\s+|AI\s+company\s+)/i', '', $companyName);
        $companyName = trim($companyName);

        // Extract funding info from title
        $fundingInfo = $this->techCrunchService->extractFundingInfo($title);

        $company = [
            'name' => $companyName,
            'source_url' => $article['url'] ?? null,
            'description' => $article['excerpt'] ?? null,
        ];

        if ($fundingInfo) {
            if (isset($fundingInfo['amount'])) {
                $company['funding_amount'] = $fundingInfo['amount'];
            }
            if (isset($fundingInfo['round_type'])) {
                $company['funding_round'] = $fundingInfo['round_type'];
            }
        }

        return $company;
    }
}
