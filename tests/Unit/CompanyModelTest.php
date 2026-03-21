<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\FundingRound;
use App\Models\Person;
use App\Models\NewsMention;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;

class CompanyModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_has_many_funding_rounds()
    {
        $company = Company::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $company->fundingRounds());
    }

    public function test_company_has_many_people()
    {
        $company = Company::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $company->people());
    }

    public function test_company_has_many_news_mentions()
    {
        $company = Company::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $company->newsMentions());
    }

    public function test_company_name_is_required()
    {
        $this->expectException(QueryException::class);
        Company::factory()->create(['name' => null]);
    }
}
