<?php

use App\Models\User;

test('profile page requires auth', function () {
    $response = $this->get('/profile');
    $response->assertRedirect('/login');
});

test('user can view profile', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/profile');
    $response->assertStatus(200);
});
