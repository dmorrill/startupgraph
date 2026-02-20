<?php

use App\Models\OpenSourceProject;
use App\Models\Company;

test('open source project belongs to many companies', function () {
    $project = OpenSourceProject::factory()->create();
    expect($project->companies())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
});

test('open source project has github url', function () {
    $project = OpenSourceProject::factory()->create(['github_url' => 'https://github.com/test/repo']);
    expect($project->github_url)->toBe('https://github.com/test/repo');
});

test('open source project casts topics to array', function () {
    $project = OpenSourceProject::factory()->create(['topics' => ['web', 'api']]);
    expect($project->topics)->toBeArray();
    expect($project->topics)->toContain('web');
});

test('open source project casts booleans', function () {
    $project = OpenSourceProject::factory()->create([
        'self_hostable' => true,
        'has_commercial_version' => false,
    ]);
    expect($project->self_hostable)->toBeTrue();
    expect($project->has_commercial_version)->toBeFalse();
});

test('open source project casts integers', function () {
    $project = OpenSourceProject::factory()->create(['stars' => 1000, 'forks' => 200]);
    expect($project->stars)->toBeInt();
    expect($project->forks)->toBeInt();
});

test('open source project casts dates', function () {
    $project = OpenSourceProject::factory()->create();
    expect($project->last_commit_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    expect($project->github_created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('open source project company relationship includes pivot', function () {
    $project = OpenSourceProject::factory()->create();
    $company = Company::factory()->create();
    $project->companies()->attach($company->id, [
        'relationship_type' => 'alternative_to',
        'notes' => 'Test note',
    ]);

    $attached = $project->companies()->first();
    expect($attached->pivot->relationship_type)->toBe('alternative_to');
    expect($attached->pivot->notes)->toBe('Test note');
});
