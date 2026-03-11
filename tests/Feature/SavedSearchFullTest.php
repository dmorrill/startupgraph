<?php

use App\Models\SavedSearch;
use App\Models\User;

test('saved search store requires auth', function () {
    $response = $this->post('/saved-searches', ['search' => 'AI startups']);
    $response->assertRedirect('/login');
});

test('authenticated user can save a search', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/saved-searches', [
        'search' => 'AI startups',
        'name' => 'My AI Search',
    ]);

    $response->assertRedirect();
    expect(SavedSearch::where('user_id', $user->id)->count())->toBe(1);
});

test('saved search is created with filters', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/saved-searches', [
        'search' => 'fintech',
        'category' => 'fintech',
        'country' => 'US',
    ]);

    $savedSearch = SavedSearch::where('user_id', $user->id)->first();
    expect($savedSearch->query)->toBe('fintech');
    expect($savedSearch->filters_json)->toBe(['category' => 'fintech', 'country' => 'US']);
});

test('saved search store returns json when requested', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/saved-searches', [
        'search' => 'climate startups',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['id', 'message']);
});

test('user can update their saved search name', function () {
    $user = User::factory()->create();
    $savedSearch = SavedSearch::factory()->create(['user_id' => $user->id, 'name' => 'Old Name']);

    $response = $this->actingAs($user)->patch("/saved-searches/{$savedSearch->id}", [
        'name' => 'New Name',
    ]);

    $response->assertRedirect();
    expect($savedSearch->fresh()->name)->toBe('New Name');
});

test('user cannot update another user saved search', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $savedSearch = SavedSearch::factory()->create(['user_id' => $other->id]);

    $response = $this->actingAs($user)->patch("/saved-searches/{$savedSearch->id}", [
        'name' => 'Hacked',
    ]);

    $response->assertForbidden();
});

test('user can delete their saved search', function () {
    $user = User::factory()->create();
    $savedSearch = SavedSearch::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->delete("/saved-searches/{$savedSearch->id}");

    $response->assertRedirect();
    expect(SavedSearch::find($savedSearch->id))->toBeNull();
});

test('user cannot delete another user saved search', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $savedSearch = SavedSearch::factory()->create(['user_id' => $other->id]);

    $response = $this->actingAs($user)->delete("/saved-searches/{$savedSearch->id}");

    $response->assertForbidden();
    expect(SavedSearch::find($savedSearch->id))->not->toBeNull();
});

test('delete saved search returns json when requested', function () {
    $user = User::factory()->create();
    $savedSearch = SavedSearch::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson("/saved-searches/{$savedSearch->id}");

    $response->assertStatus(200)->assertJson(['message' => 'Deleted']);
});
