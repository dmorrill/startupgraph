<?php

use App\Models\Company;
use App\Models\Person;

test('api person show returns person data', function () {
    $person = Person::factory()->create();

    $response = $this->getJson("/api/people/{$person->slug}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['name', 'slug'],
            'meta' => ['source', 'version'],
        ]);
    expect($response->json('data.slug'))->toBe($person->slug);
});

test('api person show returns 404 for unknown slug', function () {
    $response = $this->getJson('/api/people/nonexistent-person');

    $response->assertStatus(404);
});

test('api person show includes companies', function () {
    $person = Person::factory()->create();
    $company = Company::factory()->create();
    $company->people()->attach($person->id, ['role' => 'CTO', 'is_current' => true]);

    $response = $this->getJson("/api/people/{$person->slug}");

    $response->assertStatus(200);
    expect($response->json('data.companies'))->not->toBeEmpty();
});
