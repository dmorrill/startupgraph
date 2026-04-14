<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\NewsMention;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NewsMentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_mention_belongs_to_company(): void
    {
        $company = Company::factory()->create();
        $mention = NewsMention::factory()->create(['company_id' => $company->id]);
        $this->assertInstanceOf(Company::class, $mention->company);
    }

    public function test_news_mention_has_url(): void
    {
        $mention = NewsMention::factory()->create(['url' => 'https://techcrunch.com/article']);
        $this->assertEquals('https://techcrunch.com/article', $mention->url);
    }
}
