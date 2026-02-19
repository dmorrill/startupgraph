<?php

namespace Tests\Unit\Services\Discovery;

use App\Services\Discovery\CrunchbaseDiscoverySource;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CrunchbaseDiscoverySourceTest extends TestCase
{
    private CrunchbaseDiscoverySource $source;

    protected function setUp(): void
    {
        parent::setUp();
        $this->source = new CrunchbaseDiscoverySource();
    }

    public function test_name_returns_crunchbase(): void
    {
        $this->assertEquals('crunchbase', $this->source->name());
    }

    public function test_discover_skips_when_no_api_key(): void
    {
        config(['services.crunchbase.api_key' => null]);

        $results = $this->source->discover();

        $this->assertEmpty($results);
    }

    public function test_discover_parses_entities(): void
    {
        config(['services.crunchbase.api_key' => 'test-key']);

        Http::fake([
            'api.crunchbase.com/api/v4/searches/organizations' => Http::response([
                'entities' => [
                    [
                        'properties' => [
                            'identifier' => ['value' => 'Acme Inc', 'permalink' => 'acme-inc'],
                            'short_description' => 'A cool startup',
                            'website_url' => 'https://acme.com',
                            'location_identifiers' => [
                                ['value' => 'San Francisco'],
                                ['value' => 'California'],
                            ],
                            'last_funding_total' => ['value' => 5000000],
                            'last_funding_type' => 'seed',
                        ],
                    ],
                ],
            ]),
        ]);

        $results = $this->source->discover();

        $this->assertCount(1, $results);
        $this->assertEquals('Acme Inc', $results[0]['name']);
        $this->assertEquals('A cool startup', $results[0]['description']);
        $this->assertEquals('https://acme.com', $results[0]['website']);
        $this->assertEquals('https://www.crunchbase.com/organization/acme-inc', $results[0]['source_url']);
        $this->assertEquals('San Francisco, California', $results[0]['location']);
        $this->assertEquals(5000000, $results[0]['funding_amount']);
        $this->assertEquals('seed', $results[0]['funding_round']);
    }

    public function test_discover_handles_401(): void
    {
        config(['services.crunchbase.api_key' => 'bad-key']);

        Http::fake([
            'api.crunchbase.com/api/v4/*' => Http::response('Unauthorized', 401),
        ]);

        $results = $this->source->discover();

        $this->assertEmpty($results);
    }

    public function test_discover_handles_rate_limit(): void
    {
        config(['services.crunchbase.api_key' => 'test-key']);

        Http::fake([
            'api.crunchbase.com/api/v4/*' => Http::response('Rate limited', 429),
        ]);

        $results = $this->source->discover();

        $this->assertEmpty($results);
    }

    public function test_discover_returns_empty_on_server_error(): void
    {
        config(['services.crunchbase.api_key' => 'test-key']);

        Http::fake([
            'api.crunchbase.com/*' => Http::response('', 503),
        ]);

        // retry(2) will exhaust retries and throw, caught by outer try/catch
        $results = $this->source->discover();

        $this->assertEmpty($results);
    }

    public function test_discover_skips_entities_without_name(): void
    {
        config(['services.crunchbase.api_key' => 'test-key']);

        Http::fake([
            'api.crunchbase.com/api/v4/searches/organizations' => Http::response([
                'entities' => [
                    ['properties' => ['short_description' => 'No name']],
                    ['properties' => ['identifier' => ['value' => 'HasName']]],
                ],
            ]),
        ]);

        $results = $this->source->discover();

        $this->assertCount(1, $results);
        $this->assertEquals('HasName', $results[0]['name']);
    }
}
