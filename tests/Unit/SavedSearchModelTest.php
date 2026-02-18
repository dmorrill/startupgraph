<?php

use App\Models\SavedSearch;
use App\Models\User;

test('saved search belongs to a user', function () {
    $user = User::factory()->create();
    $search = SavedSearch::factory()->create(['user_id' => $user->id]);
    expect($search->user)->toBeInstanceOf(User::class);
});
