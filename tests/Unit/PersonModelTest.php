<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Person;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PersonModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_person_has_many_companies(): void
    {
        $person = Person::factory()->create();
        $this->assertInstanceOf(BelongsToMany::class, $person->companies());
    }

    public function test_person_has_a_name(): void
    {
        $person = Person::factory()->create(['name' => 'Jane Doe']);
        $this->assertEquals('Jane Doe', $person->name);
    }

    public function test_person_can_be_attached_to_company(): void
    {
        $person = Person::factory()->create();
        $company = Company::factory()->create();
        
        $person->companies()->attach($company, ['role' => 'CEO']);
        
        $this->assertTrue($person->companies()->where('companies.id', $company->id)->exists());
    }
}
