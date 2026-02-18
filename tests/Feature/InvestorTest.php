<?php

use App\Models\Investor;
use App\Models\User;

test('investor index page loads', function () {
    $user = User::factory()->create();
    Investor::factory()->count(3)->create();

    $response = $this->actingAs($user)->get('/investors');

    $response->assertStatus(200);
});
