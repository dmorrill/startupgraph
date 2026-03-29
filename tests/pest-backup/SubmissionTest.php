<?php

use App\Models\User;

test('submission form loads', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/submit');
    $response->assertStatus(200);
});

test('user can submit a company', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/submit', [
        'name' => 'New Startup',
        'url' => 'https://newstartup.com',
        'description' => 'A cool new startup',
    ]);

    $response->assertRedirect();
});
