<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SavedSearchModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_saved_search_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $search = SavedSearch::factory()->create(['user_id' => $user->id]);
        $this->assertInstanceOf(User::class, $search->user);
    }
}
