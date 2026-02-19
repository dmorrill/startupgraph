<?php

namespace Tests\Unit\Services\Discovery;

use App\Services\Discovery\HackerNewsDiscoverySource;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HackerNewsDiscoverySourceTest extends TestCase
{
    private HackerNewsDiscoverySource $source;

    protected function setUp(): void
    {
        parent::setUp();
        $this->source = new HackerNewsDiscoverySource();
    }

    public function test_name_returns_hackernews(): void
    {
        $this->assertEquals('hackernews', $this->source->name());
    }

    public function test_discover_extracts_show_hn_companies(): void
    {
        $recentTimestamp = now()->subDays(1)->timestamp;

        Http::fake([
            'hacker-news.firebaseio.com/v0/topstories.json' => Http::response([101, 102]),
            'hacker-news.firebaseio.com/v0/newstories.json' => Http::response([]),
            'hacker-news.firebaseio.com/v0/item/101.json' => Http::response([
                'id' => 101,
                'title' => 'Show HN: MyCoolApp – A better way to code',
                'url' => 'https://mycoolapp.com',
                'time' => $recentTimestamp,
            ]),
            'hacker-news.firebaseio.com/v0/item/102.json' => Http::response([
                'id' => 102,
                'title' => 'Regular post about programming',
                'url' => 'https://example.com',
                'time' => $recentTimestamp,
            ]),
        ]);

        $results = $this->source->discover();

        $this->assertCount(1, $results);
        $this->assertEquals('MyCoolApp', $results[0]['name']);
        $this->assertEquals('https://mycoolapp.com', $results[0]['website']);
        $this->assertEquals('https://news.ycombinator.com/item?id=101', $results[0]['source_url']);
    }

    public function test_discover_extracts_launch_hn_posts(): void
    {
        $recentTimestamp = now()->subDays(1)->timestamp;

        Http::fake([
            'hacker-news.firebaseio.com/v0/topstories.json' => Http::response([201]),
            'hacker-news.firebaseio.com/v0/newstories.json' => Http::response([]),
            'hacker-news.firebaseio.com/v0/item/201.json' => Http::response([
                'id' => 201,
                'title' => 'Launch HN: LaunchPad – Ship faster',
                'url' => 'https://launchpad.io',
                'time' => $recentTimestamp,
            ]),
        ]);

        $results = $this->source->discover();

        $this->assertCount(1, $results);
        $this->assertEquals('LaunchPad', $results[0]['name']);
    }

    public function test_discover_skips_old_items(): void
    {
        $oldTimestamp = now()->subDays(30)->timestamp;

        Http::fake([
            'hacker-news.firebaseio.com/v0/topstories.json' => Http::response([301]),
            'hacker-news.firebaseio.com/v0/newstories.json' => Http::response([]),
            'hacker-news.firebaseio.com/v0/item/301.json' => Http::response([
                'id' => 301,
                'title' => 'Show HN: OldApp – Old stuff',
                'url' => 'https://oldapp.com',
                'time' => $oldTimestamp,
            ]),
        ]);

        $results = $this->source->discover();

        $this->assertEmpty($results);
    }

    public function test_discover_skips_long_descriptive_titles(): void
    {
        $recentTimestamp = now()->subDays(1)->timestamp;

        Http::fake([
            'hacker-news.firebaseio.com/v0/topstories.json' => Http::response([401]),
            'hacker-news.firebaseio.com/v0/newstories.json' => Http::response([]),
            'hacker-news.firebaseio.com/v0/item/401.json' => Http::response([
                'id' => 401,
                'title' => 'Show HN: I built a tool that helps you find the best restaurants in your area using AI',
                'url' => 'https://example.com',
                'time' => $recentTimestamp,
            ]),
        ]);

        $results = $this->source->discover();

        $this->assertEmpty($results);
    }

    public function test_discover_deduplicates_across_feeds(): void
    {
        $recentTimestamp = now()->subDays(1)->timestamp;
        $item = [
            'id' => 501,
            'title' => 'Show HN: DupeCo – Same in both feeds',
            'url' => 'https://dupeco.com',
            'time' => $recentTimestamp,
        ];

        Http::fake([
            'hacker-news.firebaseio.com/v0/topstories.json' => Http::response([501]),
            'hacker-news.firebaseio.com/v0/newstories.json' => Http::response([501]),
            'hacker-news.firebaseio.com/v0/item/501.json' => Http::response($item),
        ]);

        $results = $this->source->discover();

        $this->assertCount(1, $results);
    }

    public function test_discover_handles_api_failure_gracefully(): void
    {
        Http::fake([
            'hacker-news.firebaseio.com/v0/topstories.json' => Http::response('', 500),
            'hacker-news.firebaseio.com/v0/newstories.json' => Http::response('', 500),
        ]);

        $results = $this->source->discover();

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }
}
