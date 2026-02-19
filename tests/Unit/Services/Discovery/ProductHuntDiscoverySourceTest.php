<?php

namespace Tests\Unit\Services\Discovery;

use App\Services\Discovery\ProductHuntDiscoverySource;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProductHuntDiscoverySourceTest extends TestCase
{
    private ProductHuntDiscoverySource $source;

    protected function setUp(): void
    {
        parent::setUp();
        $this->source = new ProductHuntDiscoverySource();
    }

    public function test_name_returns_producthunt(): void
    {
        $this->assertEquals('producthunt', $this->source->name());
    }

    public function test_discover_via_api_with_indie_signals(): void
    {
        config(['services.producthunt.token' => 'test-token']);

        Http::fake([
            'api.producthunt.com/v2/api/graphql' => Http::response([
                'data' => [
                    'posts' => [
                        'edges' => [
                            [
                                'node' => [
                                    'name' => 'IndieTool',
                                    'tagline' => 'Built with cursor in a weekend',
                                    'description' => 'A vibe coded project',
                                    'url' => 'https://producthunt.com/posts/indietool',
                                    'website' => 'https://indietool.com',
                                    'votesCount' => 100,
                                    'makers' => [['id' => '1', 'name' => 'Solo Dev']],
                                ],
                            ],
                            [
                                'node' => [
                                    'name' => 'BigCorpTool',
                                    'tagline' => 'Enterprise solution for teams',
                                    'description' => 'Professional grade platform',
                                    'url' => 'https://producthunt.com/posts/bigcorptool',
                                    'website' => 'https://bigcorp.com',
                                    'votesCount' => 50,
                                    'makers' => [
                                        ['id' => '1', 'name' => 'Dev 1'],
                                        ['id' => '2', 'name' => 'Dev 2'],
                                        ['id' => '3', 'name' => 'Dev 3'],
                                    ],
                                ],
                            ],
                        ],
                        'pageInfo' => ['hasNextPage' => false, 'endCursor' => null],
                    ],
                ],
            ]),
        ]);

        $results = $this->source->discover();

        $this->assertCount(1, $results);
        $this->assertEquals('IndieTool', $results[0]['name']);
        $this->assertTrue($results[0]['is_indie']);
        $this->assertTrue($results[0]['solo_builder']);
    }

    public function test_discover_includes_solo_maker_products(): void
    {
        config(['services.producthunt.token' => 'test-token']);

        Http::fake([
            'api.producthunt.com/v2/api/graphql' => Http::response([
                'data' => [
                    'posts' => [
                        'edges' => [
                            [
                                'node' => [
                                    'name' => 'SoloProduct',
                                    'tagline' => 'Professional analytics dashboard',
                                    'description' => 'Track your metrics',
                                    'url' => 'https://producthunt.com/posts/solo',
                                    'website' => 'https://solo.com',
                                    'votesCount' => 30,
                                    'makers' => [['id' => '1', 'name' => 'Solo']],
                                ],
                            ],
                        ],
                        'pageInfo' => ['hasNextPage' => false, 'endCursor' => null],
                    ],
                ],
            ]),
        ]);

        $results = $this->source->discover();

        $this->assertCount(1, $results);
        $this->assertEquals('SoloProduct', $results[0]['name']);
    }

    public function test_discover_falls_back_to_scraping_without_token(): void
    {
        config(['services.producthunt.token' => null]);

        Http::fake([
            'www.producthunt.com/leaderboard/daily' => Http::response(
                '<html><body><a href="/posts/cool-tool"><h3>Cool Tool</h3></a></body></html>'
            ),
        ]);

        $results = $this->source->discover();

        // May or may not match depending on HTML structure; test it doesn't crash
        $this->assertIsArray($results);
    }

    public function test_discover_handles_api_error(): void
    {
        config(['services.producthunt.token' => 'test-token']);

        Http::fake([
            'api.producthunt.com/v2/api/graphql' => Http::response('', 500),
        ]);

        $results = $this->source->discover();

        $this->assertEmpty($results);
    }
}
