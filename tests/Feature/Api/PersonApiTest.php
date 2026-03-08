<?php

namespace Tests\Feature\Api;

use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class PersonApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_person_show(): void
    {
        $person = Person::factory()->create();

        $response = $this->getJson("/api/people/{$person->slug}");

        $response->assertStatus(200);
    }
}
