<?php

namespace Tests\Feature\Api;

<<<<<<< HEAD
use App\Models\Company;
=======
>>>>>>> origin/main
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

<<<<<<< HEAD
    public function test_show_returns_person_with_companies(): void
    {
        $person = Person::factory()->create();
        $company = Company::factory()->create();
        $person->companies()->attach($company, ['role' => 'CEO', 'is_current' => true]);

        $response = $this->getJson("/api/people/{$person->slug}");

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['source', 'version', 'generated_at'],
            ]);
    }

    public function test_show_404_for_missing_person(): void
    {
        $response = $this->getJson('/api/people/nonexistent-slug');
        $response->assertNotFound();
=======
    public function test_person_show(): void
    {
        $person = Person::factory()->create();

        $response = $this->getJson("/api/people/{$person->slug}");

        $response->assertStatus(200);
>>>>>>> origin/main
    }
}
