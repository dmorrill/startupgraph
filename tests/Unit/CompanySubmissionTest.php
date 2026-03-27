<?php

namespace Tests\Unit;

use App\Models\CompanySubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_has_required_attributes(): void
    {
        $submission = CompanySubmission::factory()->create([
            'name' => 'Test Company',
            'submitter_email' => 'test@example.com',
            'status' => 'pending'
        ]);
        
        $this->assertEquals('Test Company', $submission->name);
        $this->assertEquals('test@example.com', $submission->submitter_email);
        $this->assertEquals('pending', $submission->status);
    }
}
