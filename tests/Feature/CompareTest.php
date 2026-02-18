<?php

use App\Models\Company;
use App\Models\User;

test('compare page loads', function () {
    $user = User::factory()->create();
    $companies = Company::factory()->count(2)->create();

    $response = $this->actingAs($user)->get('/compare?' . 
        'companies[]=' . $companies[0]->id . 
        '&companies[]=' . $companies[1]->id);

    $response->assertStatus(200);
});
