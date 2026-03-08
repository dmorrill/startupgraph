<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\FundingRound;
use App\Models\HeadcountSnapshot;
use App\Models\NewsMention;
use App\Models\Person;
<<<<<<< HEAD
=======
use App\Models\OpenSourceProject;
>>>>>>> origin/main
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

<<<<<<< HEAD
    public function test_company_can_be_created_with_factory(): void
    {
        $company = Company::factory()->create();
        $this->assertDatabaseHas('companies', ['id' => $company->id]);
    }

    public function test_fillable_attributes(): void
    {
        $company = Company::factory()->create([
            'name' => 'Test Corp',
            'slug' => 'test-corp',
            'website' => 'https://test.com',
            'category' => 'ai_ml',
            'is_indie' => true,
            'is_open_source' => false,
        ]);

        $this->assertEquals('Test Corp', $company->name);
        $this->assertEquals('test-corp', $company->slug);
        $this->assertEquals('ai_ml', $company->category);
        $this->assertTrue($company->is_indie);
        $this->assertFalse($company->is_open_source);
    }

    public function test_product_highlights_cast_to_array(): void
    {
        $highlights = ['Fast', 'Reliable', 'Scalable'];
        $company = Company::factory()->create(['product_highlights' => $highlights]);

        $this->assertIsArray($company->product_highlights);
        $this->assertEquals($highlights, $company->product_highlights);
    }

    public function test_founded_date_cast_to_date(): void
    {
        $company = Company::factory()->create(['founded_date' => '2020-06-15']);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $company->founded_date);
    }

    public function test_has_many_funding_rounds(): void
=======
    public function test_can_create_company(): void
    {
        $company = Company::factory()->create();

        $this->assertDatabaseHas('companies', ['id' => $company->id]);
    }

    public function test_category_label_attribute(): void
    {
        $company = Company::factory()->create(['category' => 'ai_ml']);
        $this->assertEquals('AI/ML', $company->category_label);

        $company2 = Company::factory()->create(['category' => null]);
        $this->assertNull($company2->category_label);
    }

    public function test_funding_rounds_relationship(): void
>>>>>>> origin/main
    {
        $company = Company::factory()->create();
        FundingRound::factory()->count(3)->create(['company_id' => $company->id]);

        $this->assertCount(3, $company->fundingRounds);
    }

    public function test_latest_funding_round_relationship(): void
    {
        $company = Company::factory()->create();
        FundingRound::factory()->create(['company_id' => $company->id, 'announced_date' => '2023-01-01']);
        $latest = FundingRound::factory()->create(['company_id' => $company->id, 'announced_date' => '2024-06-01']);

        $this->assertEquals($latest->id, $company->latestFundingRound->id);
    }

<<<<<<< HEAD
    public function test_has_many_headcount_snapshots(): void
=======
    public function test_headcount_snapshots_relationship(): void
>>>>>>> origin/main
    {
        $company = Company::factory()->create();
        HeadcountSnapshot::factory()->count(2)->create(['company_id' => $company->id]);

        $this->assertCount(2, $company->headcountSnapshots);
    }

<<<<<<< HEAD
    public function test_has_many_news_mentions(): void
=======
    public function test_news_mentions_relationship(): void
>>>>>>> origin/main
    {
        $company = Company::factory()->create();
        NewsMention::factory()->count(2)->create(['company_id' => $company->id]);

        $this->assertCount(2, $company->newsMentions);
    }

<<<<<<< HEAD
    public function test_belongs_to_many_people(): void
    {
        $company = Company::factory()->create();
        $person = Person::factory()->create();
        $company->people()->attach($person, ['role' => 'CEO', 'is_current' => true]);

        $this->assertCount(1, $company->people);
        $this->assertEquals('CEO', $company->people->first()->pivot->role);
    }

    public function test_route_key_name_is_slug(): void
    {
        $company = new Company();
        $this->assertEquals('slug', $company->getRouteKeyName());
    }

    public function test_category_label_attribute(): void
    {
        $company = Company::factory()->create(['category' => 'ai_ml']);
        $this->assertEquals('AI/ML', $company->category_label);
    }

    public function test_category_label_null_when_no_category(): void
    {
        $company = Company::factory()->create(['category' => null]);
        $this->assertNull($company->category_label);
=======
    public function test_people_relationship(): void
    {
        $company = Company::factory()->create();
        $person = Person::factory()->create();

        $company->people()->attach($person->id, ['role' => 'CTO', 'is_current' => true]);

        $this->assertCount(1, $company->people);
    }

    public function test_oss_alternatives_relationship(): void
    {
        $company = Company::factory()->create();
        $project = OpenSourceProject::factory()->create();

        $company->ossAlternatives()->attach($project->id, ['relationship_type' => 'alternative_to']);

        $this->assertCount(1, $company->ossAlternatives);
>>>>>>> origin/main
    }

    public function test_scope_funded(): void
    {
        $funded = Company::factory()->create();
        FundingRound::factory()->create(['company_id' => $funded->id]);
        $unfunded = Company::factory()->create();

        $results = Company::funded()->get();
<<<<<<< HEAD
=======

>>>>>>> origin/main
        $this->assertTrue($results->contains($funded));
        $this->assertFalse($results->contains($unfunded));
    }

<<<<<<< HEAD
    public function test_scope_founded_within(): void
    {
        $recent = Company::factory()->create(['founded_date' => now()->subYear()]);
        $old = Company::factory()->create(['founded_date' => now()->subYears(10)]);

        $results = Company::foundedWithin(3)->get();
        $this->assertTrue($results->contains($recent));
        $this->assertFalse($results->contains($old));
    }

    public function test_scope_in_category(): void
    {
        $ai = Company::factory()->create(['category' => 'ai_ml']);
        $fintech = Company::factory()->create(['category' => 'fintech']);

        $results = Company::inCategory('ai_ml')->get();
        $this->assertTrue($results->contains($ai));
        $this->assertFalse($results->contains($fintech));
    }

    public function test_categories_constant(): void
    {
        $this->assertArrayHasKey('ai_ml', Company::CATEGORIES);
        $this->assertArrayHasKey('fintech', Company::CATEGORIES);
        $this->assertCount(10, Company::CATEGORIES);
=======
    public function test_scope_in_category(): void
    {
        $ai = Company::factory()->create(['category' => 'ai_ml']);
        $fin = Company::factory()->create(['category' => 'fintech']);

        $results = Company::inCategory('ai_ml')->get();

        $this->assertTrue($results->contains($ai));
        $this->assertFalse($results->contains($fin));
    }

    public function test_route_key_name_is_slug(): void
    {
        $company = new Company();
        $this->assertEquals('slug', $company->getRouteKeyName());
    }

    public function test_casts(): void
    {
        $company = Company::factory()->create([
            'is_indie' => true,
            'is_open_source' => false,
            'product_highlights' => ['Fast', 'Reliable'],
        ]);

        $this->assertIsBool($company->is_indie);
        $this->assertIsBool($company->is_open_source);
        $this->assertIsArray($company->product_highlights);
>>>>>>> origin/main
    }
}
