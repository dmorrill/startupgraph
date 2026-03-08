<?php

namespace Tests\Feature\Api;

use App\Models\OpenSourceProject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class OssProjectApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_oss_projects_index(): void
    {
        OpenSourceProject::factory()->count(2)->create();

        $response = $this->getJson('/api/oss-projects');

        $response->assertStatus(200);
    }

    public function test_oss_projects_show(): void
    {
        $project = OpenSourceProject::factory()->create();

        $response = $this->getJson("/api/oss-projects/{$project->id}");

        $response->assertStatus(200);
    }
}
