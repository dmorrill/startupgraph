<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonTest extends TestCase
{
    use RefreshDatabase;

    public function test_person_can_be_created_with_factory(): void
    {
        $person = Person::factory()->create();
        $this->assertDatabaseHas('people', ['id' => $person->id]);
    }

    public function test_fillable_attributes(): void
    {
        $person = Person::factory()->create([
            'name' => 'Jane Doe',
            'slug' => 'jane-doe',
            'bio' => 'A great leader',
            'linkedin_url' => 'https://linkedin.com/in/janedoe',
            'twitter_url' => 'https://twitter.com/janedoe',
        ]);

        $this->assertEquals('Jane Doe', $person->name);
        $this->assertEquals('jane-doe', $person->slug);
        $this->assertEquals('A great leader', $person->bio);
    }

    public function test_table_name_is_people(): void
    {
        $person = new Person();
        $this->assertEquals('people', $person->getTable());
    }

    public function test_route_key_name_is_slug(): void
    {
        $person = new Person();
        $this->assertEquals('slug', $person->getRouteKeyName());
    }

    public function test_belongs_to_many_companies(): void
    {
        $person = Person::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $person->companies()->attach($company1, ['role' => 'CEO', 'is_current' => true]);
        $person->companies()->attach($company2, ['role' => 'Advisor', 'is_current' => false]);

        $this->assertCount(2, $person->companies);
    }

    public function test_pivot_data_accessible(): void
    {
        $person = Person::factory()->create();
        $company = Company::factory()->create();
        $person->companies()->attach($company, [
            'role' => 'CTO',
            'is_current' => true,
            'started_at' => '2022-01-01',
        ]);

        $pivot = $person->companies->first()->pivot;
        $this->assertEquals('CTO', $pivot->role);
        $this->assertTrue((bool) $pivot->is_current);
    }
}
