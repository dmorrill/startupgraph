<?php

use App\Models\OpenSourceProject;
use App\Models\Company;

test('open source project belongs to a company', function () {
    $company = Company::factory()->create();
    $project = OpenSourceProject::factory()->create(['company_id' => $company->id]);
    expect($project->company)->toBeInstanceOf(Company::class);
});

test('open source project has github url', function () {
    $project = OpenSourceProject::factory()->create(['github_url' => 'https://github.com/test/repo']);
    expect($project->github_url)->toBe('https://github.com/test/repo');
});
