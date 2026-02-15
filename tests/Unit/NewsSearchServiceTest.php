<?php

namespace Tests\Unit;

use App\Services\NewsSearchService;
use PHPUnit\Framework\TestCase;

class NewsSearchServiceTest extends TestCase
{
    private NewsSearchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NewsSearchService();
    }

    public function test_extract_date_from_valid_url(): void
    {
        $method = new \ReflectionMethod(NewsSearchService::class, 'extractDateFromUrl');

        $result = $method->invoke($this->service, 'https://techcrunch.com/2025/06/15/some-article/');
        $this->assertEquals('2025-06-15', $result);
    }

    public function test_extract_date_from_invalid_date_url(): void
    {
        $method = new \ReflectionMethod(NewsSearchService::class, 'extractDateFromUrl');

        // February 30 doesn't exist
        $result = $method->invoke($this->service, 'https://techcrunch.com/2025/02/30/some-article/');
        $this->assertNull($result);
    }

    public function test_extract_date_from_non_matching_url(): void
    {
        $method = new \ReflectionMethod(NewsSearchService::class, 'extractDateFromUrl');

        $result = $method->invoke($this->service, 'https://example.com/article');
        $this->assertNull($result);
    }

    public function test_extract_date_from_invalid_month(): void
    {
        $method = new \ReflectionMethod(NewsSearchService::class, 'extractDateFromUrl');

        // Month 13 doesn't exist
        $result = $method->invoke($this->service, 'https://techcrunch.com/2025/13/01/some-article/');
        $this->assertNull($result);
    }

    public function test_title_mentions_company_with_normal_name(): void
    {
        $method = new \ReflectionMethod(NewsSearchService::class, 'titleMentionsCompany');

        $this->assertTrue(
            $method->invoke($this->service, 'OpenAI raises $6.6B in massive funding round', 'OpenAI')
        );
    }

    public function test_title_mentions_company_rejects_short_names(): void
    {
        $method = new \ReflectionMethod(NewsSearchService::class, 'titleMentionsCompany');

        // Names shorter than 3 chars should be rejected
        $this->assertFalse(
            $method->invoke($this->service, 'AI is the future', 'AI')
        );
    }

    public function test_title_mentions_company_ambiguous_name_without_context(): void
    {
        $method = new \ReflectionMethod(NewsSearchService::class, 'titleMentionsCompany');

        // "Open" is ambiguous - needs funding context
        $this->assertFalse(
            $method->invoke($this->service, 'Open source is great for developers', 'Open')
        );
    }

    public function test_title_mentions_company_ambiguous_name_with_funding_context(): void
    {
        $method = new \ReflectionMethod(NewsSearchService::class, 'titleMentionsCompany');

        // "Notion" is ambiguous but has funding context
        $this->assertTrue(
            $method->invoke($this->service, 'Notion raises $100 million in new funding', 'Notion')
        );
    }
}
