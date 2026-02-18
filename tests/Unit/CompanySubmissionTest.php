<?php

use App\Models\CompanySubmission;
use App\Models\User;

test('submission belongs to user', function () {
    $user = User::factory()->create();
    $submission = CompanySubmission::factory()->create(['user_id' => $user->id]);
    expect($submission->user)->toBeInstanceOf(User::class);
});
