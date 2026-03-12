<?php

use App\Models\FundingRound;
use App\Models\Company;
use App\Models\Investor;

test('funding round belongs to a company', function () {
    $company = Company::factory()->create();
    $round = FundingRound::factory()->create(['company_id' => $company->id]);
    expect($round->company)->toBeInstanceOf(Company::class);
});

test('funding round has many investors', function () {
    $round = FundingRound::factory()->create();
    expect($round->investors())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
});

test('funding round has amount', function () {
    $round = FundingRound::factory()->create(['amount' => 5000000]);
    expect((float) $round->amount)->toBe(5000000.0);
});
