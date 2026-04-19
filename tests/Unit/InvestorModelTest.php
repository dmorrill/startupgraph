<?php

use App\Models\Investor;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

test('investor has a name', function () {
    $investor = Investor::factory()->create(['name' => 'Sequoia Capital']);
    expect($investor->name)->toBe('Sequoia Capital');
});

test('investor has many funding rounds', function () {
    $investor = Investor::factory()->create();
    expect($investor->fundingRounds())->toBeInstanceOf(BelongsToMany::class);
});
