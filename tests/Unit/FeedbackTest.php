<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_feedback_can_have_a_message(): void
    {
        $feedback = Feedback::factory()->create(['message' => 'Great app!']);
        $this->assertEquals('Great app!', $feedback->message);
    }

    public function test_feedback_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $feedback = Feedback::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(User::class, $feedback->user);
        $this->assertEquals($user->id, $feedback->user->id);
    }

    public function test_feedback_can_be_anonymous(): void
    {
        $feedback = Feedback::factory()->anonymous()->create();
        $this->assertNull($feedback->user_id);
        $this->assertNull($feedback->user);
    }

    public function test_feedback_can_have_page_url(): void
    {
        $url = 'https://example.com/page';
        $feedback = Feedback::factory()->create(['page_url' => $url]);
        $this->assertEquals($url, $feedback->page_url);
    }
}