<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_person(): void
    {
        $person = Person::factory()->create();

        $this->assertDatabaseHas('people', ['id' => $person->id]);
    }

    public function test_fillable_attributes(): void
    {
        $person = Person::factory()->create([
            'name' => 'Jane Doe',
            'bio' => 'Engineer and entrepreneur',
        ]);

        $this->assertEquals('Jane Doe', $person->name);
        $this->assertEquals('Engineer and entrepreneur', $person->bio);
    }

    public function test_companies_relationship(): void
    {
        $person = Person::factory()->create();
        $company = Company::factory()->create();

        $person->companies()->attach($company->id, [
            'role' => 'CEO',
            'is_current' => true,
        ]);

        $this->assertCount(1, $person->companies);
        $this->assertEquals('CEO', $person->companies->first()->pivot->role);
        $this->assertTrue((bool) $person->companies->first()->pivot->is_current);
    }

    public function test_uses_people_table(): void
    {
        $person = new Person();
        $this->assertEquals('people', $person->getTable());
    }

    public function test_route_key_name_is_slug(): void
    {
        $person = new Person();
        $this->assertEquals('slug', $person->getRouteKeyName());
    }
}
