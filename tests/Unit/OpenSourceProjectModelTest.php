<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\OpenSourceProject;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OpenSourceProjectModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_open_source_project_has_github_url(): void
    {
        $project = OpenSourceProject::factory()->create(['github_url' => 'https://github.com/test/repo']);
        $this->assertEquals('https://github.com/test/repo', $project->github_url);
    }

    public function test_open_source_project_has_stars(): void
    {
        $project = OpenSourceProject::factory()->create(['stars' => 1500]);
        $this->assertEquals(1500, $project->stars);
    }

    public function test_open_source_project_can_be_self_hostable(): void
    {
        $project = OpenSourceProject::factory()->create(['self_hostable' => true]);
        $this->assertTrue($project->self_hostable);
    }
}
