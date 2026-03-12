<?php

use App\Models\CompanySubmission;

test('submission has pending status by default', function () {
    $submission = CompanySubmission::factory()->create();
    expect($submission->status)->toBe('pending');
});
