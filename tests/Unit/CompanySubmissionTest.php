<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\CompanySubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanySubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_has_name(): void
    {
        $submission = CompanySubmission::factory()->create(['name' => 'Test Company']);
        $this->assertEquals('Test Company', $submission->name);
    }

    public function test_submission_can_have_description(): void
    {
        $description = 'This is a test company description';
        $submission = CompanySubmission::factory()->create(['description' => $description]);
        $this->assertEquals($description, $submission->description);
    }
}
