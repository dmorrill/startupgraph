<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\NewsMention;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class NewsMentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_mention_belongs_to_company(): void
    {
        $company = Company::factory()->create();
        $mention = NewsMention::factory()->create(['company_id' => $company->id]);

        $this->assertInstanceOf(BelongsTo::class, $mention->company());
        $this->assertEquals($company->id, $mention->company->id);
    }

    public function test_news_mention_casts_published_date_to_date(): void
    {
        $mention = NewsMention::factory()->create(['published_date' => '2024-08-20']);
        $this->assertInstanceOf(Carbon::class, $mention->published_date);
        $this->assertEquals(2024, $mention->published_date->year);
        $this->assertEquals(8, $mention->published_date->month);
        $this->assertEquals(20, $mention->published_date->day);
    }

    public function test_news_mention_stores_all_fillable_attributes(): void
    {
        $company = Company::factory()->create();
        $mention = NewsMention::factory()->create([
            'company_id' => $company->id,
            'title' => 'Startup Raises $50M Series B',
            'url' => 'https://techcrunch.com/2024/startup-raises',
            'source' => 'techcrunch',
            'published_date' => '2024-08-15',
            'summary' => 'A leading startup has raised a significant round.',
        ]);

        $this->assertEquals('Startup Raises $50M Series B', $mention->title);
        $this->assertEquals('https://techcrunch.com/2024/startup-raises', $mention->url);
        $this->assertEquals('techcrunch', $mention->source);
        $this->assertEquals('A leading startup has raised a significant round.', $mention->summary);
    }

    public function test_news_mention_summary_can_be_null(): void
    {
        $mention = NewsMention::factory()->create(['summary' => null]);
        $this->assertNull($mention->summary);
    }

    public function test_news_mention_source_can_be_null(): void
    {
        $mention = NewsMention::factory()->create(['source' => null]);
        $this->assertNull($mention->source);
    }

    public function test_company_has_many_news_mentions(): void
    {
        $company = Company::factory()->create();
        NewsMention::factory()->count(3)->create(['company_id' => $company->id]);

        $mentions = $company->newsMentions;
        $this->assertCount(3, $mentions);
        foreach ($mentions as $mention) {
            $this->assertEquals($company->id, $mention->company_id);
        }
    }

    public function test_deleting_company_cascades_to_news_mentions(): void
    {
        $company = Company::factory()->create();
        NewsMention::factory()->count(2)->create(['company_id' => $company->id]);

        $this->assertEquals(2, NewsMention::where('company_id', $company->id)->count());

        $company->delete();

        $this->assertEquals(0, NewsMention::where('company_id', $company->id)->count());
    }

    public function test_news_mentions_from_different_companies_are_independent(): void
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        NewsMention::factory()->count(2)->create(['company_id' => $company1->id]);
        NewsMention::factory()->count(3)->create(['company_id' => $company2->id]);

        $this->assertCount(2, $company1->newsMentions);
        $this->assertCount(3, $company2->newsMentions);
    }

    public function test_news_mention_published_date_can_be_null(): void
    {
        $mention = NewsMention::factory()->create(['published_date' => null]);
        $this->assertNull($mention->published_date);
    }
}
