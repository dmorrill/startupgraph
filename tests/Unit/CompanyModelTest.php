<?php

use App\Models\Company;

test('company has many funding rounds', function () {
    $company = Company::factory()->create();
    expect($company->fundingRounds())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('company has many people', function () {
    $company = Company::factory()->create();
    expect($company->people())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
});

test('company has many news mentions', function () {
    $company = Company::factory()->create();
    expect($company->newsMentions())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('company name is required', function () {
    expect(fn () => Company::factory()->create(['name' => null]))
        ->toThrow(\Illuminate\Database\QueryException::class);
});
