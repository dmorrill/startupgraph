<?php

use App\Models\Company;
use App\Models\NewsMention;

test('news mention belongs to company', function () {
    $company = Company::factory()->create();
    $mention = NewsMention::factory()->create(['company_id' => $company->id]);
    expect($mention->company)->toBeInstanceOf(Company::class);
});

test('news mention has url', function () {
    $mention = NewsMention::factory()->create(['url' => 'https://techcrunch.com/article']);
    expect($mention->url)->toBe('https://techcrunch.com/article');
});
