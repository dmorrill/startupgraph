<?php

namespace Tests\Unit\Services\Discovery;

use App\Services\Discovery\TechCrunchDiscoverySource;
use App\Services\TechCrunchService;
use Tests\TestCase;

class TechCrunchDiscoverySourceTest extends TestCase
{
    public function test_name_returns_techcrunch(): void
    {
        $service = $this->createMock(TechCrunchService::class);
        $source = new TechCrunchDiscoverySource($service);

        $this->assertEquals('techcrunch', $source->name());
    }

    public function test_discover_extracts_companies_from_articles(): void
    {
        $service = $this->createMock(TechCrunchService::class);
        $service->method('scrapeFundraisingArticles')->willReturn([
            'success' => true,
            'articles' => [
                [
                    'title' => 'Acme raises $50 million in Series A funding',
                    'url' => 'https://techcrunch.com/2026/01/15/acme-raises',
                    'excerpt' => 'Acme is building cool stuff',
                ],
                [
                    'title' => 'StartupX lands $10M in seed round',
                    'url' => 'https://techcrunch.com/2026/01/15/startupx-lands',
                    'excerpt' => 'StartupX does things',
                ],
            ],
        ]);
        $service->method('extractFundingInfo')->willReturnCallback(function ($text) {
            if (str_contains($text, '$50 million')) {
                return ['amount' => 50_000_000, 'round_type' => 'series_a'];
            }
            if (str_contains($text, '$10M')) {
                return ['amount' => 10_000_000, 'round_type' => 'seed'];
            }
            return null;
        });

        $source = new TechCrunchDiscoverySource($service);
        $results = $source->discover();

        $this->assertCount(2, $results);
        $this->assertEquals('Acme', $results[0]['name']);
        $this->assertEquals(50_000_000, $results[0]['funding_amount']);
        $this->assertEquals('series_a', $results[0]['funding_round']);
        $this->assertEquals('StartupX', $results[1]['name']);
    }

    public function test_discover_returns_empty_on_scrape_failure(): void
    {
        $service = $this->createMock(TechCrunchService::class);
        $service->method('scrapeFundraisingArticles')->willReturn([
            'success' => false,
            'articles' => [],
            'error' => 'HTTP 500',
        ]);

        $source = new TechCrunchDiscoverySource($service);
        $results = $source->discover();

        $this->assertEmpty($results);
    }

    public function test_discover_skips_non_matching_titles(): void
    {
        $service = $this->createMock(TechCrunchService::class);
        $service->method('scrapeFundraisingArticles')->willReturn([
            'success' => true,
            'articles' => [
                [
                    'title' => 'The future of AI in healthcare',
                    'url' => 'https://techcrunch.com/2026/01/15/ai-healthcare',
                    'excerpt' => 'Analysis piece',
                ],
            ],
        ]);

        $source = new TechCrunchDiscoverySource($service);
        $results = $source->discover();

        $this->assertEmpty($results);
    }

    public function test_discover_strips_exclusive_prefix(): void
    {
        $service = $this->createMock(TechCrunchService::class);
        $service->method('scrapeFundraisingArticles')->willReturn([
            'success' => true,
            'articles' => [
                [
                    'title' => 'Exclusive: SecretCo raises $20 million in Series B',
                    'url' => 'https://techcrunch.com/2026/01/15/secretco',
                    'excerpt' => '',
                ],
            ],
        ]);
        $service->method('extractFundingInfo')->willReturn(['amount' => 20_000_000, 'round_type' => 'series_b']);

        $source = new TechCrunchDiscoverySource($service);
        $results = $source->discover();

        $this->assertCount(1, $results);
        $this->assertEquals('SecretCo', $results[0]['name']);
    }

    public function test_discover_skips_too_long_names(): void
    {
        $service = $this->createMock(TechCrunchService::class);
        $service->method('scrapeFundraisingArticles')->willReturn([
            'success' => true,
            'articles' => [
                [
                    'title' => 'This is a very long company name that is really a sentence raises $5M',
                    'url' => 'https://techcrunch.com/2026/01/15/long',
                    'excerpt' => '',
                ],
            ],
        ]);

        $source = new TechCrunchDiscoverySource($service);
        $results = $source->discover();

        $this->assertEmpty($results);
    }
}
