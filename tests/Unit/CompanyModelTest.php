<?php

use App\Models\Company;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;

test('company has many funding rounds', function () {
    $company = Company::factory()->create();
    expect($company->fundingRounds())->toBeInstanceOf(HasMany::class);
});

test('company has many people', function () {
    $company = Company::factory()->create();
    expect($company->people())->toBeInstanceOf(HasMany::class);
});

test('company has many news mentions', function () {
    $company = Company::factory()->create();
    expect($company->newsMentions())->toBeInstanceOf(HasMany::class);
});

test('company name is required', function () {
    expect(fn () => Company::factory()->create(['name' => null]))
        ->toThrow(QueryException::class);
});
