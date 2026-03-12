<?php

use App\Models\OpenSourceProject;
use App\Models\Company;

test('open source project belongs to many companies', function () {
    $project = OpenSourceProject::factory()->create();
    $company = Company::factory()->create();
    $project->companies()->attach($company->id, ['relationship_type' => 'alternative_to']);
    expect($project->companies->first())->toBeInstanceOf(Company::class);
});

test('open source project has github url', function () {
    $project = OpenSourceProject::factory()->create(['github_url' => 'https://github.com/test/repo']);
    expect($project->github_url)->toBe('https://github.com/test/repo');
});
