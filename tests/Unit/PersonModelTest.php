<?php

use App\Models\Person;
use App\Models\Company;

test('person belongs to many companies', function () {
    $person = Person::factory()->create();
    $company = Company::factory()->create();
    $person->companies()->attach($company->id, ['role' => 'CEO', 'is_current' => true]);
    expect($person->companies->first())->toBeInstanceOf(Company::class);
});

test('person has a name', function () {
    $person = Person::factory()->create(['name' => 'Jane Doe']);
    expect($person->name)->toBe('Jane Doe');
});
