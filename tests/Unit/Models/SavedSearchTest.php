<?php

namespace Tests\Unit\Models;

use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_saved_search(): void
    {
        $search = SavedSearch::factory()->create();

        $this->assertDatabaseHas('saved_searches', ['id' => $search->id]);
    }

    public function test_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $search = SavedSearch::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $search->user->id);
    }

    public function test_display_name_with_name(): void
    {
        $search = SavedSearch::factory()->create(['name' => 'My Search']);

        $this->assertEquals('My Search', $search->display_name);
    }

    public function test_display_name_from_query_and_filters(): void
    {
        $search = SavedSearch::factory()->create([
            'name' => null,
            'query' => 'ai startups',
            'filters_json' => ['category' => 'ai_ml', 'country' => 'US'],
        ]);

        $this->assertStringContainsString('ai startups', $search->display_name);
        $this->assertStringContainsString('ai_ml', $search->display_name);
    }

    public function test_display_name_default(): void
    {
        $search = SavedSearch::factory()->create([
            'name' => null,
            'query' => null,
            'filters_json' => null,
        ]);

        $this->assertEquals('All companies', $search->display_name);
    }

    public function test_casts(): void
    {
        $search = SavedSearch::factory()->create([
            'notify_on_new' => true,
            'filters_json' => ['category' => 'fintech'],
        ]);

        $this->assertIsBool($search->notify_on_new);
        $this->assertIsArray($search->filters_json);
    }

    public function test_search_url_attribute(): void
    {
        $search = SavedSearch::factory()->create([
            'query' => 'test',
            'filters_json' => ['category' => 'ai_ml'],
        ]);

        $url = $search->search_url;
        $this->assertStringContainsString('search=test', $url);
        $this->assertStringContainsString('category=ai_ml', $url);
    }
}
