<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_form_loads(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/submit');
        $response->assertStatus(200);
    }

    public function test_user_can_submit_a_company(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/submit', [
            'name' => 'New Startup',
            'url' => 'https://newstartup.com',
            'description' => 'A cool new startup',
        ]);

        $response->assertRedirect();
    }
}
