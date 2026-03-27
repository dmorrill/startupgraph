<?php

namespace Tests\Unit;

use App\Models\Person;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_person_can_be_attached_to_companies(): void
    {
        $company = Company::factory()->create();
        $person = Person::factory()->create();
        $person->companies()->attach($company, ['role' => 'CEO', 'is_current' => true]);
        
        $this->assertTrue($person->companies->contains($company));
        $this->assertEquals('CEO', $person->companies->first()->pivot->role);
    }

    public function test_person_has_a_name(): void
    {
        $person = Person::factory()->create(['name' => 'Jane Doe']);
        $this->assertEquals('Jane Doe', $person->name);
    }
}
