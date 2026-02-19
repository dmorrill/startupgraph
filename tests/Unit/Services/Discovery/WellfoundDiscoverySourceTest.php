<?php

namespace Tests\Unit\Services\Discovery;

use App\Services\Discovery\WellfoundDiscoverySource;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WellfoundDiscoverySourceTest extends TestCase
{
    private WellfoundDiscoverySource $source;

    protected function setUp(): void
    {
        parent::setUp();
        $this->source = new WellfoundDiscoverySource();
    }

    public function test_name_returns_wellfound(): void
    {
        $this->assertEquals('wellfound', $this->source->name());
    }

    public function test_discover_via_graphql(): void
    {
        Http::fake([
            'wellfound.com/graphql' => Http::response([
                'data' => [
                    'startups' => [
                        'edges' => [
                            [
                                'node' => [
                                    'name' => 'WellCo',
                                    'highConcept' => 'AI for good',
                                    'companyUrl' => 'https://wellco.com',
                                    'slug' => 'wellco',
                                    'locationTags' => [['displayName' => 'San Francisco']],
                                    'totalRaised' => ['amount' => '2000000', 'currency' => 'USD'],
                                    'lastRoundType' => 'Seed',
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $results = $this->source->discover();

        $this->assertCount(1, $results);
        $this->assertEquals('WellCo', $results[0]['name']);
        $this->assertEquals('AI for good', $results[0]['description']);
        $this->assertEquals('https://wellco.com', $results[0]['website']);
        $this->assertEquals('https://wellfound.com/company/wellco', $results[0]['source_url']);
        $this->assertEquals('San Francisco', $results[0]['location']);
        $this->assertEquals(2000000.0, $results[0]['funding_amount']);
        $this->assertEquals('Seed', $results[0]['funding_round']);
    }

    public function test_discover_falls_back_to_scrape(): void
    {
        $nextData = json_encode([
            'props' => [
                'pageProps' => [
                    'startups' => [
                        [
                            'name' => 'ScrapedStartup',
                            'high_concept' => 'From HTML',
                            'company_url' => 'https://scraped.com',
                            'slug' => 'scraped',
                            'total_raised' => 1000000,
                        ],
                    ],
                ],
            ],
        ]);

        Http::fake([
            'wellfound.com/graphql' => Http::response('', 500),
            'wellfound.com/startups/trending' => Http::response(
                "<html><script id=\"__NEXT_DATA__\" type=\"application/json\">{$nextData}</script></html>"
            ),
        ]);

        $results = $this->source->discover();

        $this->assertCount(1, $results);
        $this->assertEquals('ScrapedStartup', $results[0]['name']);
        $this->assertEquals(1000000.0, $results[0]['funding_amount']);
    }

    public function test_discover_skips_entries_without_name(): void
    {
        Http::fake([
            'wellfound.com/graphql' => Http::response([
                'data' => [
                    'startups' => [
                        'edges' => [
                            ['node' => ['highConcept' => 'No name here']],
                            ['node' => ['name' => 'HasName', 'slug' => 'hasname']],
                        ],
                    ],
                ],
            ]),
        ]);

        $results = $this->source->discover();

        $this->assertCount(1, $results);
        $this->assertEquals('HasName', $results[0]['name']);
    }

    public function test_discover_returns_empty_when_all_fail(): void
    {
        Http::fake([
            'wellfound.com/graphql' => Http::response('', 500),
            'wellfound.com/startups/trending' => Http::response('', 500),
            'wellfound.com/startups' => Http::response('', 500),
        ]);

        $results = $this->source->discover();

        $this->assertEmpty($results);
    }
}
