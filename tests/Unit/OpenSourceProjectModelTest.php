<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\OpenSourceProject;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpenSourceProjectModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_open_source_project_has_companies_relationship(): void
    {
        $project = OpenSourceProject::factory()->create();
        $this->assertInstanceOf(BelongsToMany::class, $project->companies());
    }

    public function test_open_source_project_has_github_url(): void
    {
        $project = OpenSourceProject::factory()->create(['github_url' => 'https://github.com/test/repo']);
        $this->assertEquals('https://github.com/test/repo', $project->github_url);
    }
}
