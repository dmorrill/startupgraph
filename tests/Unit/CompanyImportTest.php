<?php

use App\Models\CompanyImport;

test('company import has source', function () {
    $import = CompanyImport::factory()->create(['source' => 'wikipedia']);
    expect($import->source)->toBe('wikipedia');
});

test('company import tracks processed count', function () {
    $import = CompanyImport::factory()->create(['total_processed' => 500]);
    expect($import->total_processed)->toBe(500);
});
