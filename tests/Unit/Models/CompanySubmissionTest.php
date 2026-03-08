<?php

namespace Tests\Unit\Models;

use App\Models\CompanySubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_submission(): void
    {
        $submission = CompanySubmission::factory()->create();

        $this->assertDatabaseHas('company_submissions', ['id' => $submission->id]);
    }

    public function test_fillable_attributes(): void
    {
        $submission = CompanySubmission::factory()->create([
            'name' => 'Test Corp',
            'status' => 'pending',
            'submitter_email' => 'test@example.com',
        ]);

        $this->assertEquals('Test Corp', $submission->name);
        $this->assertEquals('pending', $submission->status);
        $this->assertEquals('test@example.com', $submission->submitter_email);
    }

    public function test_default_status_is_pending(): void
    {
        $submission = CompanySubmission::factory()->create();

        $this->assertEquals('pending', $submission->status);
    }
}
