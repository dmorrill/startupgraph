<?php

use App\Models\OpenSourceProject;
use App\Models\User;

test('open source index loads', function () {
    $user = User::factory()->create();
    OpenSourceProject::factory()->count(3)->create();

    $response = $this->actingAs($user)->get('/open-source');
    $response->assertStatus(200);
});
