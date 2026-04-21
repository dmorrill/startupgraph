<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Person;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_person_has_companies_via_belongs_to_many(): void
    {
        $person = Person::factory()->create();
        $this->assertInstanceOf(BelongsToMany::class, $person->companies());
    }

    public function test_person_uses_slug_for_route_key(): void
    {
        $person = new Person;
        $this->assertEquals('slug', $person->getRouteKeyName());
    }

    public function test_person_uses_people_table(): void
    {
        $person = new Person;
        $this->assertEquals('people', $person->getTable());
    }

    public function test_person_stores_all_fillable_attributes(): void
    {
        $person = Person::factory()->create([
            'name' => 'Ada Lovelace',
            'slug' => 'ada-lovelace',
            'bio' => 'Mathematician and writer',
            'linkedin_url' => 'https://linkedin.com/in/ada-lovelace',
            'twitter_url' => 'https://twitter.com/ada',
            'photo_url' => 'https://example.com/ada.jpg',
        ]);

        $this->assertEquals('Ada Lovelace', $person->name);
        $this->assertEquals('ada-lovelace', $person->slug);
        $this->assertEquals('Mathematician and writer', $person->bio);
        $this->assertEquals('https://linkedin.com/in/ada-lovelace', $person->linkedin_url);
        $this->assertEquals('https://twitter.com/ada', $person->twitter_url);
        $this->assertEquals('https://example.com/ada.jpg', $person->photo_url);
    }

    public function test_person_slug_must_be_unique(): void
    {
        Person::factory()->create(['slug' => 'john-doe']);
        $this->expectException(QueryException::class);
        Person::factory()->create(['slug' => 'john-doe']);
    }

    public function test_person_can_be_attached_to_company_with_pivot_data(): void
    {
        $person = Person::factory()->create();
        $company = Company::factory()->create();

        $person->companies()->attach($company->id, [
            'role' => 'CEO',
            'is_current' => true,
            'started_at' => '2020-01-01',
        ]);

        $person->refresh();
        $this->assertCount(1, $person->companies);

        $pivot = $person->companies->first()->pivot;
        $this->assertEquals('CEO', $pivot->role);
        $this->assertTrue((bool) $pivot->is_current);
        $this->assertEquals('2020-01-01', $pivot->started_at);
    }

    public function test_person_can_have_roles_at_multiple_companies(): void
    {
        $person = Person::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $person->companies()->attach($company1->id, ['role' => 'CEO', 'is_current' => true]);
        $person->companies()->attach($company2->id, ['role' => 'Board Member', 'is_current' => true]);

        $person->refresh();
        $this->assertCount(2, $person->companies);
    }

    public function test_person_pivot_tracks_ended_at_for_former_roles(): void
    {
        $person = Person::factory()->create();
        $company = Company::factory()->create();

        $person->companies()->attach($company->id, [
            'role' => 'VP Engineering',
            'is_current' => false,
            'started_at' => '2018-03-01',
            'ended_at' => '2022-06-30',
        ]);

        $pivot = $person->companies->first()->pivot;
        $this->assertFalse((bool) $pivot->is_current);
        $this->assertEquals('2022-06-30', $pivot->ended_at);
    }

    public function test_person_optional_fields_can_be_null(): void
    {
        $person = Person::factory()->create([
            'bio' => null,
            'linkedin_url' => null,
            'twitter_url' => null,
            'photo_url' => null,
        ]);

        $this->assertNull($person->bio);
        $this->assertNull($person->linkedin_url);
        $this->assertNull($person->twitter_url);
        $this->assertNull($person->photo_url);
    }

    public function test_person_pivot_includes_timestamps(): void
    {
        $person = Person::factory()->create();
        $company = Company::factory()->create();

        $person->companies()->attach($company->id, ['role' => 'CTO', 'is_current' => true]);

        $pivot = $person->companies->first()->pivot;
        $this->assertNotNull($pivot->created_at);
        $this->assertNotNull($pivot->updated_at);
    }
}
