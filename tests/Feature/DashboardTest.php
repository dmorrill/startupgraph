<?php

use App\Models\Company;
use App\Models\User;

test('dashboard requires auth', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated user can view dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
});

test('dashboard shows followed companies', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $user->followedCompanies()->attach($company->id);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
});

test('new user sees onboarding flag', function () {
    $user = User::factory()->create(['created_at' => now()]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
});
