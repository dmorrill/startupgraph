<?php

use App\Models\Company;
use App\Models\FundingRound;
use App\Models\HeadcountSnapshot;
use App\Models\Person;

test('api companies index returns json', function () {
    Company::factory()->count(3)->create();

    $response = $this->getJson('/api/companies');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'meta' => ['source', 'version', 'generated_at'],
            'pagination' => ['total', 'per_page', 'current_page', 'last_page'],
        ]);
});

test('api companies index returns all companies', function () {
    Company::factory()->count(5)->create();

    $response = $this->getJson('/api/companies');

    $response->assertStatus(200);
    expect($response->json('pagination.total'))->toBe(5);
});

test('api companies index filters by search query', function () {
    Company::factory()->create(['name' => 'MatchCorp', 'description' => 'Matching company']);
    Company::factory()->create(['name' => 'OtherCorp']);

    $response = $this->getJson('/api/companies?q=MatchCorp');

    $response->assertStatus(200);
    expect($response->json('pagination.total'))->toBe(1);
    expect($response->json('data.0.name'))->toBe('MatchCorp');
});

test('api companies index filters by country', function () {
    Company::factory()->create(['country' => 'US']);
    Company::factory()->create(['country' => 'UK']);

    $response = $this->getJson('/api/companies?country=US');

    $response->assertStatus(200);
    expect($response->json('pagination.total'))->toBe(1);
});

test('api companies index filters by category', function () {
    Company::factory()->create(['category' => 'fintech']);
    Company::factory()->create(['category' => 'ai_ml']);

    $response = $this->getJson('/api/companies?category=fintech');

    $response->assertStatus(200);
    expect($response->json('pagination.total'))->toBe(1);
});

test('api companies index respects per_page limit', function () {
    Company::factory()->count(10)->create();

    $response = $this->getJson('/api/companies?per_page=3');

    $response->assertStatus(200);
    expect($response->json('pagination.per_page'))->toBe(3);
    expect(count($response->json('data')))->toBe(3);
});

test('api companies show returns company detail', function () {
    $company = Company::factory()->create();

    $response = $this->getJson("/api/companies/{$company->slug}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['name', 'slug'],
            'meta' => ['source', 'version'],
        ]);
    expect($response->json('data.slug'))->toBe($company->slug);
});

test('api companies show returns 404 for unknown slug', function () {
    $response = $this->getJson('/api/companies/nonexistent-company');

    $response->assertStatus(404);
});

test('api companies funding returns funding rounds', function () {
    $company = Company::factory()->create();
    FundingRound::factory()->create(['company_id' => $company->id, 'amount' => 1000000]);

    $response = $this->getJson("/api/companies/{$company->slug}/funding");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['company_slug', 'company_name', 'total_funding', 'rounds_count', 'funding_rounds'],
        ]);
    expect($response->json('data.rounds_count'))->toBe(1);
});

test('api companies people returns people list', function () {
    $company = Company::factory()->create();
    $person = Person::factory()->create();
    $company->people()->attach($person->id, ['role' => 'CEO', 'is_current' => true]);

    $response = $this->getJson("/api/companies/{$company->slug}/people");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['company_slug', 'company_name', 'total_count', 'current_count', 'current', 'former'],
        ]);
    expect($response->json('data.current_count'))->toBe(1);
});

test('api companies headcount returns snapshots', function () {
    $company = Company::factory()->create();
    HeadcountSnapshot::factory()->create(['company_id' => $company->id, 'headcount' => 100]);

    $response = $this->getJson("/api/companies/{$company->slug}/headcount");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['company_slug', 'company_name', 'snapshots_count', 'snapshots'],
        ]);
    expect($response->json('data.snapshots_count'))->toBe(1);
});
