<?php

use App\Models\CompanySubmission;

test('submission defaults to pending status', function () {
    $submission = CompanySubmission::factory()->create();
    expect($submission->status)->toBe('pending');
});

test('submission stores submitter email', function () {
    $submission = CompanySubmission::factory()->create(['submitter_email' => 'builder@example.com']);
    expect($submission->submitter_email)->toBe('builder@example.com');
});
