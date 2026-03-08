<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\OpenSourceProject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpenSourceProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_project(): void
    {
        $project = OpenSourceProject::factory()->create();

        $this->assertDatabaseHas('open_source_projects', ['id' => $project->id]);
    }

    public function test_casts_are_correct(): void
    {
        $project = OpenSourceProject::factory()->create([
            'stars' => 1500,
            'self_hostable' => true,
            'topics' => ['devops', 'cloud'],
        ]);

        $this->assertIsInt($project->stars);
        $this->assertIsBool($project->self_hostable);
        $this->assertIsArray($project->topics);
        $this->assertEquals(['devops', 'cloud'], $project->topics);
    }

    public function test_companies_relationship(): void
    {
        $project = OpenSourceProject::factory()->create();
        $company = Company::factory()->create();

        $project->companies()->attach($company->id, [
            'relationship_type' => 'alternative_to',
            'notes' => 'OSS alternative',
        ]);

        $this->assertCount(1, $project->companies);
        $this->assertEquals('alternative_to', $project->companies->first()->pivot->relationship_type);
    }

    public function test_datetime_casts(): void
    {
        $project = OpenSourceProject::factory()->create();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $project->last_commit_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $project->github_created_at);
    }
}
