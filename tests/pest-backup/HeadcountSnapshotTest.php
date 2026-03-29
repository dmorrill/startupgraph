<?php

use App\Models\HeadcountSnapshot;
use App\Models\Company;

test('headcount snapshot belongs to company', function () {
    $company = Company::factory()->create();
    $snapshot = HeadcountSnapshot::factory()->create(['company_id' => $company->id]);
    expect($snapshot->company)->toBeInstanceOf(Company::class);
});

test('headcount snapshot has count', function () {
    $snapshot = HeadcountSnapshot::factory()->create(['headcount' => 150]);
    expect($snapshot->headcount)->toBe(150);
});
