<?php

use App\Models\Company;
use App\Models\FundingRound;
use App\Models\Person;

test('api stats returns expected structure', function () {
    $response = $this->getJson('/api/stats');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'companies_count',
                'people_count',
                'funding_rounds_count',
                'total_funding_tracked',
                'total_funding_formatted',
                'oss_projects_count',
                'categories',
                'countries',
            ],
            'meta' => ['source', 'version', 'generated_at', 'description'],
        ]);
});

test('api stats counts reflect database state', function () {
    Company::factory()->count(3)->create();
    Person::factory()->count(2)->create();

    $response = $this->getJson('/api/stats');

    $response->assertStatus(200);
    expect($response->json('data.companies_count'))->toBe(3);
    expect($response->json('data.people_count'))->toBe(2);
});

test('api stats includes categories list', function () {
    $response = $this->getJson('/api/stats');

    $response->assertStatus(200);
    expect($response->json('data.categories'))->not->toBeEmpty();
});
