<?php

namespace Tests\Feature;

use App\Models\OpenSourceProject;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OpenSourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_open_source_index_loads(): void
    {
        $user = User::factory()->create();
        OpenSourceProject::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/open-source');
        $response->assertStatus(200);
    }
}
