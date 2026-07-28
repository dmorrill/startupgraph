<?php

use App\Models\Company;
use App\Models\OpenSourceProject;

test('open source project can be linked to a company', function () {
    $company = Company::factory()->create();
    $project = OpenSourceProject::factory()->create();
    $project->companies()->attach($company->id, ['relationship_type' => 'alternative_to']);

    expect($project->companies->first())->toBeInstanceOf(Company::class);
});

test('open source project has github url', function () {
    $project = OpenSourceProject::factory()->create(['github_url' => 'https://github.com/test/repo']);
    expect($project->github_url)->toBe('https://github.com/test/repo');
});
