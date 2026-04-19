<?php

use App\Models\Company;
use App\Models\FundingRound;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

test('funding round belongs to a company', function () {
    $company = Company::factory()->create();
    $round = FundingRound::factory()->create(['company_id' => $company->id]);
    expect($round->company)->toBeInstanceOf(Company::class);
});

test('funding round has many investors', function () {
    $round = FundingRound::factory()->create();
    expect($round->investors())->toBeInstanceOf(BelongsToMany::class);
});

test('funding round has amount', function () {
    $round = FundingRound::factory()->create(['amount' => 5000000]);
    expect($round->amount)->toBe(5000000);
});
