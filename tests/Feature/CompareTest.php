<?php

use App\Models\Company;
use App\Models\User;

test('compare page loads', function () {
    $user = User::factory()->create();
    $companies = Company::factory()->count(2)->create();

    $response = $this->actingAs($user)->get(
        '/compare?companies='.$companies[0]->slug.','.$companies[1]->slug
    );

    $response->assertStatus(200);
});

test('compare page tolerates array input', function () {
    $response = $this->get('/compare?companies[]=foo&companies[]=bar');
    $response->assertStatus(200);
});
