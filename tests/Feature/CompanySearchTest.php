<?php

use App\Models\Company;
use App\Models\User;

test('company index page loads', function () {
    $user = User::factory()->create();
    Company::factory()->count(3)->create();

    $response = $this->actingAs($user)->get('/companies');

    $response->assertStatus(200);
});

test('company search returns results', function () {
    $user = User::factory()->create();
    Company::factory()->create(['name' => 'TestCorp']);

    $response = $this->actingAs($user)->get('/companies?search=TestCorp');

    $response->assertStatus(200);
});
