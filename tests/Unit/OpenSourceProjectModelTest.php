<?php

namespace Tests\Unit;

use App\Models\OpenSourceProject;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpenSourceProjectModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_open_source_project_can_be_associated_with_companies(): void
    {
        $company = Company::factory()->create();
        $project = OpenSourceProject::factory()->create();
        $project->companies()->attach($company, ['relationship_type' => 'alternative_to', 'notes' => 'Similar functionality']);
        
        $this->assertTrue($project->companies->contains($company));
        $this->assertEquals('alternative_to', $project->companies->first()->pivot->relationship_type);
    }

    public function test_open_source_project_has_github_url(): void
    {
        $project = OpenSourceProject::factory()->create(['github_url' => 'https://github.com/test/repo']);
        $this->assertEquals('https://github.com/test/repo', $project->github_url);
    }
}
