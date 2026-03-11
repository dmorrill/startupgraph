<?php

use App\Models\OpenSourceProject;

test('api oss projects index returns paginated list', function () {
    OpenSourceProject::factory()->count(5)->create();

    $response = $this->getJson('/api/oss-projects');

    $response->assertStatus(200);
    expect($response->json('total'))->toBe(5);
});

test('api oss projects index filters by search query', function () {
    OpenSourceProject::factory()->create(['name' => 'SearchableProject', 'description' => 'test']);
    OpenSourceProject::factory()->create(['name' => 'OtherProject']);

    $response = $this->getJson('/api/oss-projects?q=SearchableProject');

    $response->assertStatus(200);
    expect($response->json('total'))->toBe(1);
});

test('api oss projects index filters by language', function () {
    OpenSourceProject::factory()->create(['primary_language' => 'PHP']);
    OpenSourceProject::factory()->create(['primary_language' => 'Go']);

    $response = $this->getJson('/api/oss-projects?language=PHP');

    $response->assertStatus(200);
    expect($response->json('total'))->toBe(1);
});

test('api oss projects show returns project detail', function () {
    $project = OpenSourceProject::factory()->create();

    $response = $this->getJson("/api/oss-projects/{$project->id}");

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['id', 'name']]);
    expect($response->json('data.id'))->toBe($project->id);
});

test('api oss projects show returns 404 for unknown project', function () {
    $response = $this->getJson('/api/oss-projects/99999');

    $response->assertStatus(404);
});
