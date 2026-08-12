<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\NewsMention;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class NewsMentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_news_mention(): void
    {
        $mention = NewsMention::factory()->create();

        $this->assertDatabaseHas('news_mentions', ['id' => $mention->id]);
    }

    public function test_belongs_to_company(): void
    {
        $company = Company::factory()->create();
        $mention = NewsMention::factory()->create(['company_id' => $company->id]);

        $this->assertEquals($company->id, $mention->company->id);
    }

    public function test_published_date_is_cast_to_date(): void
    {
        $mention = NewsMention::factory()->create();

        $this->assertInstanceOf(Carbon::class, $mention->published_date);
    }
}
