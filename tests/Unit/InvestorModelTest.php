<?php

use App\Models\Investor;

test('investor has a name', function () {
    $investor = Investor::factory()->create(['name' => 'Sequoia Capital']);
    expect($investor->name)->toBe('Sequoia Capital');
});

test('investor has many funding rounds', function () {
    $investor = Investor::factory()->create();
    expect($investor->fundingRounds())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class);
});
