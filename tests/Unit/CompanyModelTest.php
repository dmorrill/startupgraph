<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\FundingRound;
use App\Models\Person;
use App\Models\NewsMention;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_has_many_funding_rounds(): void
    {
        $company = Company::factory()->create();
        $this->assertInstanceOf(HasMany::class, $company->fundingRounds());
    }

    public function test_company_has_many_people(): void
    {
        $company = Company::factory()->create();
        $this->assertInstanceOf(BelongsToMany::class, $company->people());
    }

    public function test_company_has_many_news_mentions(): void
    {
        $company = Company::factory()->create();
        $this->assertInstanceOf(HasMany::class, $company->newsMentions());
    }

    public function test_company_name_is_required(): void
    {
        $this->expectException(QueryException::class);
        Company::factory()->create(['name' => null]);
    }
}
