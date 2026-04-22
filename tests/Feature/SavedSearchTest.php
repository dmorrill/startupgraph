<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SavedSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_saved_search_requires_auth(): void
    {
        $response = $this->get('/saved-searches');
        $response->assertRedirect('/login');
    }
}
