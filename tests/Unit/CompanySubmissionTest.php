<?php

namespace Tests\Unit;

use App\Models\CompanySubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $submission = CompanySubmission::factory()->create(['user_id' => $user->id]);
        $this->assertInstanceOf(User::class, $submission->user);
    }
}
