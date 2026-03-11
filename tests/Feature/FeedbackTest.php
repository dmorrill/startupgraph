<?php

use App\Models\Feedback;
use App\Models\User;

test('guests can submit feedback', function () {
    $response = $this->post('/feedback', [
        'message' => 'Great site!',
    ]);

    $response->assertRedirect();
    expect(Feedback::count())->toBe(1);
});

test('authenticated user feedback is linked to their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/feedback', [
        'message' => 'Very helpful tool.',
    ]);

    $feedback = Feedback::first();
    expect($feedback->user_id)->toBe($user->id);
});

test('feedback requires a message', function () {
    $response = $this->post('/feedback', [
        'message' => '',
    ]);

    $response->assertSessionHasErrors('message');
});

test('feedback message max length is enforced', function () {
    $response = $this->post('/feedback', [
        'message' => str_repeat('a', 2001),
    ]);

    $response->assertSessionHasErrors('message');
});

test('feedback endpoint returns json when requested', function () {
    $response = $this->postJson('/feedback', [
        'message' => 'Looks great!',
    ]);

    $response->assertStatus(201)
        ->assertJson(['status' => 'ok']);
});

test('feedback stores page url', function () {
    $this->post('/feedback', [
        'message' => 'Found a bug here.',
        'page_url' => 'https://example.com/companies/acme',
    ]);

    $feedback = Feedback::first();
    expect($feedback->page_url)->toBe('https://example.com/companies/acme');
});
