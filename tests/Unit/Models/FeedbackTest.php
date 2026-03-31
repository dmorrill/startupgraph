<?php

namespace Tests\Unit\Models;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_feedback(): void
    {
        $user = User::factory()->create();
        $feedback = Feedback::create([
            'user_id' => $user->id,
            'message' => 'This is test feedback',
            'page_url' => 'https://example.com/page',
        ]);

        $this->assertInstanceOf(Feedback::class, $feedback);
        $this->assertEquals('This is test feedback', $feedback->message);
        $this->assertEquals('https://example.com/page', $feedback->page_url);
        $this->assertEquals($user->id, $feedback->user_id);
        $this->assertDatabaseHas('feedback', [
            'message' => 'This is test feedback',
            'user_id' => $user->id,
        ]);
    }

    public function test_can_create_feedback_without_user(): void
    {
        $feedback = Feedback::create([
            'user_id' => null,
            'message' => 'Anonymous feedback',
            'page_url' => 'https://example.com/page',
        ]);

        $this->assertInstanceOf(Feedback::class, $feedback);
        $this->assertEquals('Anonymous feedback', $feedback->message);
        $this->assertNull($feedback->user_id);
        $this->assertDatabaseHas('feedback', [
            'message' => 'Anonymous feedback',
            'user_id' => null,
        ]);
    }

    public function test_feedback_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $feedback = Feedback::create([
            'user_id' => $user->id,
            'message' => 'User feedback',
            'page_url' => 'https://example.com/page',
        ]);

        $this->assertInstanceOf(User::class, $feedback->user);
        $this->assertEquals($user->id, $feedback->user->id);
    }

    public function test_feedback_can_have_null_user(): void
    {
        $feedback = Feedback::create([
            'user_id' => null,
            'message' => 'Anonymous feedback',
            'page_url' => 'https://example.com/page',
        ]);

        $this->assertNull($feedback->user);
    }

    public function test_feedback_fillable_attributes(): void
    {
        $feedback = new Feedback();
        $expectedFillable = ['user_id', 'page_url', 'message'];

        $this->assertEquals($expectedFillable, $feedback->getFillable());
    }

    public function test_feedback_created_at_timestamp(): void
    {
        $feedback = Feedback::create([
            'message' => 'Test feedback',
            'page_url' => 'https://example.com/page',
        ]);

        $this->assertNotNull($feedback->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $feedback->created_at);
    }
}