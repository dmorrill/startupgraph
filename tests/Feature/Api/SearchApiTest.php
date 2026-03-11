<?php

use App\Models\Company;
use App\Models\Person;

test('api search returns empty for short query', function () {
    $response = $this->getJson('/api/search?q=a');

    $response->assertStatus(200)
        ->assertJsonPath('data.companies', [])
        ->assertJsonPath('data.people', []);
    expect($response->json('meta.message'))->toContain('at least 2 characters');
});

test('api search returns companies and people', function () {
    Company::factory()->create(['name' => 'Searchable Corp']);
    Person::factory()->create(['name' => 'Searchable Person']);

    $response = $this->getJson('/api/search?q=Searchable');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['companies', 'people'],
            'meta' => ['query', 'companies_count', 'people_count'],
        ]);
    expect($response->json('meta.companies_count'))->toBeGreaterThan(0);
});

test('api search respects limit parameter', function () {
    Company::factory()->count(10)->create(['name' => 'Limit Test Corp']);

    $response = $this->getJson('/api/search?q=Limit+Test&limit=3');

    $response->assertStatus(200);
    expect(count($response->json('data.companies')))->toBeLessThanOrEqual(3);
});

test('api search returns meta with query string', function () {
    $response = $this->getJson('/api/search?q=myquery');

    $response->assertStatus(200);
    expect($response->json('meta.query'))->toBe('myquery');
});
