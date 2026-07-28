<?php

use App\Models\Company;
use App\Models\Signal;
use App\Models\User;

function agentHeaders(User $user, string $tokenName = 'test-agent'): array
{
    return ['Authorization' => 'Bearer '.$user->createToken($tokenName)->plainTextToken];
}

test('research endpoints require authentication', function () {
    $this->getJson('/api/lists')->assertStatus(401);
    $this->postJson('/api/lists', ['name' => 'x'])->assertStatus(401);
    $this->getJson('/api/signals')->assertStatus(401);
    $this->getJson('/api/me')->assertStatus(401);
});

test('me identifies the token', function () {
    $user = User::factory()->create();

    $this->getJson('/api/me', agentHeaders($user, 'investing-agent'))
        ->assertStatus(200)
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonPath('data.token_name', 'investing-agent');
});

test('agent can create a list and add companies with rationale', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $headers = agentHeaders($user);

    $listId = $this->postJson('/api/lists', ['name' => 'AI watchlist'], $headers)
        ->assertStatus(201)
        ->assertJsonPath('data.created_via', 'test-agent')
        ->json('data.id');

    $this->postJson("/api/lists/{$listId}/companies", [
        'company' => $company->slug,
        'rationale' => 'Strong team, fast growth',
    ], $headers)->assertStatus(201);

    $this->getJson("/api/lists/{$listId}", $headers)
        ->assertStatus(200)
        ->assertJsonPath('data.entries.0.company.slug', $company->slug)
        ->assertJsonPath('data.entries.0.rationale', 'Strong team, fast growth');

    $this->deleteJson("/api/lists/{$listId}/companies/{$company->slug}", [], $headers)
        ->assertStatus(200);

    expect($user->lists()->first()->entries()->count())->toBe(0);
});

test('creating a list twice is idempotent', function () {
    $user = User::factory()->create();
    $headers = agentHeaders($user);

    $this->postJson('/api/lists', ['name' => 'Same name'], $headers)->assertStatus(201);
    $this->postJson('/api/lists', ['name' => 'Same name'], $headers)->assertStatus(200);

    expect($user->lists()->count())->toBe(1);
});

test('users cannot see each other\'s research', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $listId = $this->postJson('/api/lists', ['name' => 'Private'], agentHeaders($owner))
        ->json('data.id');

    // Drop the cached guard so the next request authenticates as $other
    app('auth')->forgetGuards();

    $this->getJson("/api/lists/{$listId}", agentHeaders($other))->assertStatus(404);
    $this->getJson('/api/lists', agentHeaders($other))->assertJsonCount(0, 'data');
});

test('agent can save and list notes on a company', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $headers = agentHeaders($user, 'research-agent');

    $this->postJson('/api/notes', [
        'company' => $company->slug,
        'title' => 'Deep dive',
        'body' => '## Thesis\nLooks promising.',
    ], $headers)
        ->assertStatus(201)
        ->assertJsonPath('data.created_via', 'research-agent');

    $this->getJson('/api/notes?company='.$company->slug, $headers)
        ->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Deep dive');
});

test('screens store a result snapshot and refresh', function () {
    $user = User::factory()->create();
    Company::factory()->create(['category' => 'ai_ml', 'name' => 'Acme AI']);
    Company::factory()->create(['category' => 'fintech', 'name' => 'MoneyCo']);
    $headers = agentHeaders($user);

    $screenId = $this->postJson('/api/screens', [
        'name' => 'AI companies',
        'criteria' => ['category' => 'ai_ml'],
    ], $headers)
        ->assertStatus(201)
        ->assertJsonPath('data.result_count', 1)
        ->json('data.id');

    Company::factory()->create(['category' => 'ai_ml', 'name' => 'NewAI']);

    $this->postJson("/api/screens/{$screenId}/refresh", [], $headers)
        ->assertStatus(200)
        ->assertJsonPath('data.result_count', 2);

    $results = $this->getJson("/api/screens/{$screenId}", $headers)
        ->assertStatus(200)
        ->json('data.results');

    expect(collect($results)->pluck('name')->all())->toContain('Acme AI', 'NewAI');
});

test('screens reject unknown sort fields', function () {
    $user = User::factory()->create();

    $this->postJson('/api/screens', [
        'name' => 'Bad sort',
        'criteria' => ['sort' => 'evil_column'],
    ], agentHeaders($user))->assertStatus(422);
});

test('agent can log a custom signal and mark it read', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $headers = agentHeaders($user);

    $signalId = $this->postJson('/api/signals', [
        'title' => 'Competitor launched a similar product',
        'company' => $company->slug,
    ], $headers)
        ->assertStatus(201)
        ->json('data.id');

    $this->getJson('/api/signals?unread=1', $headers)->assertJsonCount(1, 'data');

    $this->postJson("/api/signals/{$signalId}/read", [], $headers)->assertStatus(200);

    $this->getJson('/api/signals?unread=1', $headers)->assertJsonCount(0, 'data');
    expect(Signal::find($signalId)->read_at)->not->toBeNull();
});
