<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_person_show_returns_200(): void
    {
        $person = Person::factory()->create();

        $response = $this->get("/people/{$person->slug}");

        $response->assertStatus(200);
    }

    public function test_person_show_displays_person_name(): void
    {
        $person = Person::factory()->create(['name' => 'Jane Founder']);

        $response = $this->get("/people/{$person->slug}");

        $response->assertStatus(200);
        $response->assertSee('Jane Founder');
    }

    public function test_person_show_returns_404_for_unknown_slug(): void
    {
        $response = $this->get('/people/nonexistent-person-slug-xyz');

        $response->assertStatus(404);
    }

    public function test_person_show_displays_associated_companies(): void
    {
        $person = Person::factory()->create(['name' => 'Alice Exec']);
        $company = Company::factory()->create(['name' => 'StartupXYZ']);
        $person->companies()->attach($company->id, ['role' => 'CTO', 'is_current' => true]);

        $response = $this->get("/people/{$person->slug}");

        $response->assertStatus(200);
        $response->assertSee('StartupXYZ');
    }

    public function test_person_show_with_no_companies(): void
    {
        $person = Person::factory()->create();

        $response = $this->get("/people/{$person->slug}");

        $response->assertStatus(200);
    }

    public function test_person_show_with_multiple_companies(): void
    {
        $person = Person::factory()->create();
        $current = Company::factory()->create(['name' => 'CurrentCorp']);
        $former = Company::factory()->create(['name' => 'FormerCorp']);

        $person->companies()->attach($current->id, ['role' => 'CEO', 'is_current' => true]);
        $person->companies()->attach($former->id, ['role' => 'CTO', 'is_current' => false]);

        $response = $this->get("/people/{$person->slug}");

        $response->assertStatus(200);
        $response->assertSee('CurrentCorp');
        $response->assertSee('FormerCorp');
    }

    public function test_person_show_with_bio(): void
    {
        $person = Person::factory()->create(['bio' => 'An experienced entrepreneur.']);

        $response = $this->get("/people/{$person->slug}");

        $response->assertStatus(200);
        $response->assertSee('An experienced entrepreneur.');
    }
}
