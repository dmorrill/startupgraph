<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\NewsMention;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsMentionTest extends TestCase
{
    use RefreshDatabase;

<<<<<<< HEAD
    public function test_can_be_created_with_factory(): void
    {
        $mention = NewsMention::factory()->create();
=======
    public function test_can_create_news_mention(): void
    {
        $mention = NewsMention::factory()->create();

>>>>>>> origin/main
        $this->assertDatabaseHas('news_mentions', ['id' => $mention->id]);
    }

    public function test_belongs_to_company(): void
    {
        $company = Company::factory()->create();
        $mention = NewsMention::factory()->create(['company_id' => $company->id]);
<<<<<<< HEAD
        $this->assertEquals($company->id, $mention->company->id);
    }

    public function test_published_date_cast(): void
    {
        $mention = NewsMention::factory()->create(['published_date' => '2024-03-15']);
=======

        $this->assertEquals($company->id, $mention->company->id);
    }

    public function test_published_date_is_cast_to_date(): void
    {
        $mention = NewsMention::factory()->create();

>>>>>>> origin/main
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $mention->published_date);
    }
}
