<?php

use App\Models\Person;
use App\Models\User;

test('person page loads', function () {
    $user = User::factory()->create();
    $person = Person::factory()->create();

    $response = $this->actingAs($user)->get("/people/{$person->id}");
    $response->assertStatus(200);
});
