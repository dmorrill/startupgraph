<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_submit_feedback(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->post('/feedback', [
            'message' => 'This is a test feedback message',
            'page_url' => 'https://example.com/test-page',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Thanks for your feedback!');
        
        $this->assertDatabaseHas('feedback', [
            'user_id' => $user->id,
            'message' => 'This is a test feedback message',
            'page_url' => 'https://example.com/test-page',
        ]);
    }

    public function test_guest_user_can_submit_feedback(): void
    {
        $response = $this->post('/feedback', [
            'message' => 'Anonymous feedback message',
            'page_url' => 'https://example.com/test-page',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Thanks for your feedback!');
        
        $this->assertDatabaseHas('feedback', [
            'user_id' => null,
            'message' => 'Anonymous feedback message',
            'page_url' => 'https://example.com/test-page',
        ]);
    }

    public function test_feedback_without_page_url_uses_referer(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->withHeader('referer', 'https://example.com/referrer-page')
            ->post('/feedback', [
                'message' => 'Feedback without explicit page URL',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('feedback', [
            'user_id' => $user->id,
            'message' => 'Feedback without explicit page URL',
            'page_url' => 'https://example.com/referrer-page',
        ]);
    }

    public function test_feedback_requires_message(): void
    {
        $response = $this->post('/feedback', [
            'page_url' => 'https://example.com/test-page',
        ]);

        $response->assertSessionHasErrors(['message']);
        $this->assertDatabaseEmpty('feedback');
    }

    public function test_feedback_message_has_max_length(): void
    {
        $longMessage = str_repeat('a', 2001); // Exceeds 2000 character limit
        
        $response = $this->post('/feedback', [
            'message' => $longMessage,
            'page_url' => 'https://example.com/test-page',
        ]);

        $response->assertSessionHasErrors(['message']);
        $this->assertDatabaseEmpty('feedback');
    }

    public function test_page_url_has_max_length(): void
    {
        $longUrl = 'https://example.com/' . str_repeat('a', 500); // Exceeds 500 character limit
        
        $response = $this->post('/feedback', [
            'message' => 'Valid feedback message',
            'page_url' => $longUrl,
        ]);

        $response->assertSessionHasErrors(['page_url']);
        $this->assertDatabaseEmpty('feedback');
    }

    public function test_api_request_returns_json_response(): void
    {
        $response = $this->postJson('/feedback', [
            'message' => 'API feedback message',
            'page_url' => 'https://example.com/test-page',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['status' => 'ok']);
        
        $this->assertDatabaseHas('feedback', [
            'message' => 'API feedback message',
        ]);
    }

    public function test_api_request_validation_errors_return_json(): void
    {
        $response = $this->postJson('/feedback', [
            'page_url' => 'https://example.com/test-page',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message']);
        $this->assertDatabaseEmpty('feedback');
    }

    public function test_feedback_message_can_contain_special_characters(): void
    {
        $specialMessage = 'Feedback with special chars: @#$%^&*()_+-=[]{}|;\':",./<>?';
        
        $response = $this->post('/feedback', [
            'message' => $specialMessage,
            'page_url' => 'https://example.com/test-page',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Thanks for your feedback!');
        
        $this->assertDatabaseHas('feedback', [
            'message' => $specialMessage,
        ]);
    }
}