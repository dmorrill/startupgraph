<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PersonTest extends TestCase
{
    use RefreshDatabase;

    public function test_person_page_loads(): void
    {
        $user = User::factory()->create();
        $person = Person::factory()->create();

        $response = $this->actingAs($user)->get("/people/{$person->id}");
        $response->assertStatus(200);
    }
}
