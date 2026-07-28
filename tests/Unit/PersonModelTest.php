<?php

use App\Models\Company;
use App\Models\Person;

test('person can belong to a company', function () {
    $company = Company::factory()->create();
    $person = Person::factory()->create();
    $person->companies()->attach($company->id, ['role' => 'CEO', 'is_current' => true]);

    expect($person->companies->first())->toBeInstanceOf(Company::class);
});

test('person has a name', function () {
    $person = Person::factory()->create(['name' => 'Jane Doe']);
    expect($person->name)->toBe('Jane Doe');
});
