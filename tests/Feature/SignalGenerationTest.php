<?php

use App\Models\Company;
use App\Models\FundingRound;
use App\Models\HeadcountSnapshot;
use App\Models\Signal;
use App\Models\User;

test('new funding round signals followers only', function () {
    $company = Company::factory()->create(['name' => 'Acme']);
    $follower = User::factory()->create();
    $bystander = User::factory()->create();
    $follower->followedCompanies()->attach($company->id);

    FundingRound::factory()->create([
        'company_id' => $company->id,
        'amount' => 10_000_000,
        'round_type' => 'Series A',
    ]);

    expect($follower->signals()->where('type', Signal::TYPE_FUNDING_ROUND)->count())->toBe(1)
        ->and($bystander->signals()->count())->toBe(0);

    $signal = $follower->signals()->first();
    expect($signal->title)->toContain('Acme')
        ->and($signal->title)->toContain('Series A');
});

test('big headcount change signals followers, small wobble does not', function () {
    $company = Company::factory()->create();
    $follower = User::factory()->create();
    $follower->followedCompanies()->attach($company->id);

    HeadcountSnapshot::factory()->create([
        'company_id' => $company->id,
        'headcount' => 100,
        'recorded_date' => now()->subMonths(2),
    ]);

    // +2% — under the 5% threshold, no signal
    HeadcountSnapshot::factory()->create([
        'company_id' => $company->id,
        'headcount' => 102,
        'recorded_date' => now()->subMonth(),
    ]);

    expect($follower->signals()->where('type', Signal::TYPE_HEADCOUNT_CHANGE)->count())->toBe(0);

    // +47% — signal
    HeadcountSnapshot::factory()->create([
        'company_id' => $company->id,
        'headcount' => 150,
        'recorded_date' => now(),
    ]);

    expect($follower->signals()->where('type', Signal::TYPE_HEADCOUNT_CHANGE)->count())->toBe(1);
});

test('api token command issues a working token', function () {
    $user = User::factory()->create(['email' => 'elle@example.com']);

    $this->artisan('api:token', ['email' => 'elle@example.com', '--name' => 'cli-agent'])
        ->assertSuccessful();

    expect($user->tokens()->where('name', 'cli-agent')->count())->toBe(1);
});

test('api token command fails for unknown email', function () {
    $this->artisan('api:token', ['email' => 'nobody@example.com'])->assertFailed();
});
