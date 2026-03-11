<?php

use App\Models\Company;
use App\Models\User;

test('following a company requires auth', function () {
    $company = Company::factory()->create();

    $response = $this->post("/companies/{$company->slug}/follow");

    $response->assertRedirect('/login');
});

test('authenticated user can follow a company', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();

    $response = $this->actingAs($user)->post("/companies/{$company->slug}/follow");

    $response->assertRedirect();
    expect($user->followedCompanies()->where('company_id', $company->id)->exists())->toBeTrue();
});

test('following the same company twice does not duplicate', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $user->followedCompanies()->attach($company->id);

    $this->actingAs($user)->post("/companies/{$company->slug}/follow");

    expect($user->followedCompanies()->where('company_id', $company->id)->count())->toBe(1);
});

test('unfollowing a company requires auth', function () {
    $company = Company::factory()->create();

    $response = $this->delete("/companies/{$company->slug}/unfollow");

    $response->assertRedirect('/login');
});

test('authenticated user can unfollow a company', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $user->followedCompanies()->attach($company->id);

    $response = $this->actingAs($user)->delete("/companies/{$company->slug}/unfollow");

    $response->assertRedirect();
    expect($user->followedCompanies()->where('company_id', $company->id)->exists())->toBeFalse();
});
