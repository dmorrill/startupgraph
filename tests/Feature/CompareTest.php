<?php

use App\Models\Company;
use App\Models\User;

test('compare page loads', function () {
    $user = User::factory()->create();
    $companies = Company::factory()->count(2)->create();

    $slugs = $companies->pluck('slug')->implode(',');
    $response = $this->actingAs($user)->get('/compare?companies=' . $slugs);

    $response->assertStatus(200);
});
