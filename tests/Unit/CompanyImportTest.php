<?php

use App\Models\CompanyImport;

test('company import has source', function () {
    $import = CompanyImport::factory()->create(['source' => 'wikipedia']);
    expect($import->source)->toBe('wikipedia');
});

test('company import tracks count', function () {
    $import = CompanyImport::factory()->create(['count' => 500]);
    expect($import->count)->toBe(500);
});
