<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TechCrunchService
{
    private const FUNDRAISING_URL = 'https://techcrunch.com/tag/fundraising/';
    private const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    public function scrapeFundraisingArticles(): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => self::USER_AGENT,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])->timeout(30)->get(self::FUNDRAISING_URL);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'articles' => [],
                    'error' => "HTTP {$response->status()}",
                ];
            }

            $html = $response->body();
            $articles = $this->parseArticles($html);

            return [
                'success' => true,
                'articles' => $articles,
                'error' => null,
            ];
        } catch (\Exception $e) {
            Log::warning("TechCrunch scrape error: {$e->getMessage()}");

            return [
                'success' => false,
                'articles' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    public function matchArticlesToCompanies(array $articles): Collection
    {
        $companies = Company::pluck('name', 'id')->toArray();
        $matches = collect();

        foreach ($articles as $article) {
            $title = $article['title'] ?? '';
            $content = $article['excerpt'] ?? '';
            $text = $title . ' ' . $content;

            foreach ($companies as $companyId => $companyName) {
                // Check if company name appears in article
                if (stripos($text, $companyName) !== false) {
                    $fundingInfo = $this->extractFundingInfo($text);

                    if ($fundingInfo) {
                        $matches->push([
                            'company_id' => $companyId,
                            'company_name' => $companyName,
                            'article_title' => $title,
                            'article_url' => $article['url'] ?? null,
                            'funding_info' => $fundingInfo,
                        ]);
                    }
                }
            }
        }

        return $matches;
    }

    private function parseArticles(string $html): array
    {
        $articles = [];

        // Look for article links and titles in TechCrunch's HTML structure
        // Pattern matches article cards with titles and URLs
        preg_match_all(
            '/<a[^>]*href="(https:\/\/techcrunch\.com\/\d{4}\/\d{2}\/\d{2}\/[^"]+)"[^>]*>([^<]+)<\/a>/i',
            $html,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $url = $match[1];
            $title = html_entity_decode(trim($match[2]));

            // Filter to funding-related articles
            if ($this->isFundingArticle($title)) {
                $articles[] = [
                    'url' => $url,
                    'title' => $title,
                    'excerpt' => '',
                ];
            }
        }

        // Deduplicate by URL
        $seen = [];
        $unique = [];
        foreach ($articles as $article) {
            if (!isset($seen[$article['url']])) {
                $seen[$article['url']] = true;
                $unique[] = $article;
            }
        }

        return array_slice($unique, 0, 20); // Limit to 20 most recent
    }

    private function isFundingArticle(string $title): bool
    {
        $keywords = [
            'raises', 'raised', 'funding', 'series a', 'series b', 'series c',
            'series d', 'series e', 'series f', 'seed', 'million', 'billion',
            'valuation', 'investment', 'investors', 'round',
        ];

        $titleLower = strtolower($title);

        foreach ($keywords as $keyword) {
            if (stripos($titleLower, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    public function extractFundingInfo(string $text): ?array
    {
        $info = [];

        // Extract amount (e.g., "$50 million", "$1.5 billion")
        if (preg_match('/\$(\d+(?:\.\d+)?)\s*(million|billion|M|B)/i', $text, $amountMatch)) {
            $value = (float) $amountMatch[1];
            $unit = strtolower($amountMatch[2]);

            if ($unit === 'billion' || $unit === 'b') {
                $info['amount'] = $value * 1_000_000_000;
            } else {
                $info['amount'] = $value * 1_000_000;
            }
        }

        // Extract round type
        $roundPatterns = [
            '/series\s+([a-h])/i' => 'series_',
            '/seed\s+(round|funding)/i' => 'seed',
            '/pre-seed/i' => 'pre_seed',
        ];

        foreach ($roundPatterns as $pattern => $prefix) {
            if (preg_match($pattern, $text, $roundMatch)) {
                if ($prefix === 'series_') {
                    $info['round_type'] = 'series_' . strtolower($roundMatch[1]);
                } else {
                    $info['round_type'] = $prefix;
                }
                break;
            }
        }

        return !empty($info) ? $info : null;
    }
}
