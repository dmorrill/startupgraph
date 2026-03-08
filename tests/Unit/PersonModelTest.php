<?php

use App\Models\Person;
use App\Models\Company;

test('person belongs to a company', function () {
    $company = Company::factory()->create();
    $person = Person::factory()->create(['company_id' => $company->id]);
    expect($person->company)->toBeInstanceOf(Company::class);
});

test('person has a name', function () {
    $person = Person::factory()->create(['name' => 'Jane Doe']);
    expect($person->name)->toBe('Jane Doe');
});
