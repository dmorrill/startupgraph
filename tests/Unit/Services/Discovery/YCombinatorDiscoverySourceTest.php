<?php

namespace Tests\Unit\Services\Discovery;

use App\Services\Discovery\YCombinatorDiscoverySource;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class YCombinatorDiscoverySourceTest extends TestCase
{
    private YCombinatorDiscoverySource $source;

    protected function setUp(): void
    {
        parent::setUp();
        $this->source = new YCombinatorDiscoverySource();
    }

    public function test_name_returns_yc(): void
    {
        $this->assertEquals('yc', $this->source->name());
    }

    public function test_discover_parses_algolia_hits(): void
    {
        Http::fake([
            '45bwzj1sgc-dsn.algolia.net/*' => Http::response([
                'hits' => [
                    [
                        'name' => 'TestCo',
                        'one_liner' => 'A test company',
                        'website' => 'https://testco.com',
                        'batch' => 'W2026',
                        'slug' => 'testco',
                    ],
                    [
                        'name' => 'AnotherCo',
                        'long_description' => 'Long desc here',
                        'website' => 'https://anotherco.com',
                        'batch' => 'S2025',
                        'slug' => 'anotherco',
                    ],
                ],
            ]),
        ]);

        $results = $this->source->discover();

        $this->assertCount(2, $results);
        $this->assertEquals('TestCo', $results[0]['name']);
        $this->assertEquals('A test company', $results[0]['description']);
        $this->assertEquals('https://testco.com', $results[0]['website']);
        $this->assertEquals('W2026', $results[0]['batch']);
        $this->assertEquals('https://www.ycombinator.com/companies/testco', $results[0]['source_url']);

        $this->assertEquals('AnotherCo', $results[1]['name']);
        $this->assertEquals('Long desc here', $results[1]['description']);
    }

    public function test_discover_skips_hits_without_name(): void
    {
        Http::fake([
            '45bwzj1sgc-dsn.algolia.net/*' => Http::response([
                'hits' => [
                    ['one_liner' => 'No name here'],
                    ['name' => 'ValidCo', 'slug' => 'validco'],
                ],
            ]),
        ]);

        $results = $this->source->discover();

        $this->assertCount(1, $results);
        $this->assertEquals('ValidCo', $results[0]['name']);
    }

    public function test_discover_falls_back_to_scrape_on_api_failure(): void
    {
        Http::fake([
            '45bwzj1sgc-dsn.algolia.net/*' => Http::response('', 500),
            'www.ycombinator.com/*' => Http::response('<html><script id="__NEXT_DATA__" type="application/json">{"props":{"pageProps":{"companies":[{"name":"ScrapedCo","one_liner":"From scrape","website":"https://scraped.co","batch":"W2026","slug":"scrapedco"}]}}}</script></html>'),
        ]);

        $results = $this->source->discover();

        $this->assertCount(1, $results);
        $this->assertEquals('ScrapedCo', $results[0]['name']);
        $this->assertEquals('https://www.ycombinator.com/companies/scrapedco', $results[0]['source_url']);
    }

    public function test_discover_returns_empty_when_both_fail(): void
    {
        Http::fake([
            '45bwzj1sgc-dsn.algolia.net/*' => Http::response('', 500),
            'www.ycombinator.com/*' => Http::response('', 500),
        ]);

        $results = $this->source->discover();

        $this->assertEmpty($results);
    }

    public function test_discover_handles_exception_gracefully(): void
    {
        Http::fake([
            '45bwzj1sgc-dsn.algolia.net/*' => function () {
                throw new \Exception('Connection timeout');
            },
            'www.ycombinator.com/*' => Http::response('', 500),
        ]);

        $results = $this->source->discover();

        $this->assertIsArray($results);
    }
}
