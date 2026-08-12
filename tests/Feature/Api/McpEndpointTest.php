<?php

use App\Models\Company;
use App\Models\User;

function mcpHeaders(User $user, string $tokenName = 'mcp-agent'): array
{
    return ['Authorization' => 'Bearer '.$user->createToken($tokenName)->plainTextToken];
}

function mcpCall(object $test, array $headers, string $tool, array $args = [])
{
    $response = $test->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => ['name' => $tool, 'arguments' => $args],
    ], $headers)->assertStatus(200);

    return json_decode($response->json('result.content.0.text'), true);
}

test('mcp endpoint requires authentication', function () {
    $this->postJson('/mcp', ['jsonrpc' => '2.0', 'id' => 1, 'method' => 'initialize'])
        ->assertStatus(401);
});

test('mcp initialize and tools/list expose read and write tools', function () {
    $user = User::factory()->create();
    $headers = mcpHeaders($user);

    $this->postJson('/mcp', ['jsonrpc' => '2.0', 'id' => 1, 'method' => 'initialize'], $headers)
        ->assertStatus(200)
        ->assertJsonPath('result.serverInfo.name', 'startupgraph');

    $tools = collect($this->postJson('/mcp', [
        'jsonrpc' => '2.0', 'id' => 2, 'method' => 'tools/list',
    ], $headers)->json('result.tools'))->pluck('name');

    expect($tools)->toContain('search_companies', 'get_company', 'create_list', 'save_note', 'create_screen', 'log_signal');
});

test('mcp agent can search then build a list with rationales', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create(['category' => 'ai_ml']);
    $headers = mcpHeaders($user, 'investing-agent');

    $found = mcpCall($this, $headers, 'search_companies', ['category' => 'ai_ml']);
    expect($found[0]['slug'])->toBe($company->slug);

    $added = mcpCall($this, $headers, 'add_to_list', [
        'list' => 'AI infra watchlist',
        'company' => $company->slug,
        'rationale' => 'Category leader',
    ]);

    expect($added['added'])->toBeTrue();

    $list = $user->lists()->where('name', 'AI infra watchlist')->first();
    expect($list)->not->toBeNull()
        ->and($list->created_via)->toBe('mcp:investing-agent')
        ->and($list->entries()->count())->toBe(1);
});

test('mcp agent can create and refresh a screen', function () {
    $user = User::factory()->create();
    Company::factory()->count(2)->create(['category' => 'fintech']);
    $headers = mcpHeaders($user);

    $result = mcpCall($this, $headers, 'create_screen', [
        'name' => 'Fintech scan',
        'criteria' => ['category' => 'fintech'],
    ]);

    expect($result['result_count'])->toBe(2);

    Company::factory()->create(['category' => 'fintech']);

    $refreshed = mcpCall($this, $headers, 'refresh_screen', ['name' => 'Fintech scan']);
    expect($refreshed['result_count'])->toBe(3);
});

test('mcp agent can save a note and see it in research summary', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $headers = mcpHeaders($user);

    mcpCall($this, $headers, 'save_note', [
        'company' => $company->slug,
        'title' => 'Thesis',
        'body' => 'Strong signal.',
    ]);

    $summary = mcpCall($this, $headers, 'list_my_research');
    expect($summary['recent_notes'][0]['company'])->toBe($company->slug);
});

test('mcp unknown tool returns an error payload', function () {
    $user = User::factory()->create();

    $result = mcpCall($this, mcpHeaders($user), 'not_a_tool');
    expect($result['error'])->toContain('Unknown tool');
});
