<?php

namespace Tests\Unit;

use App\Services\TechCrunchService;
use PHPUnit\Framework\TestCase;

class TechCrunchServiceTest extends TestCase
{
    private TechCrunchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TechCrunchService();
    }

    public function test_extract_funding_info_with_million_amount(): void
    {
        $text = 'Acme raises $50 million in Series A funding';
        $info = $this->service->extractFundingInfo($text);

        $this->assertNotNull($info);
        $this->assertEquals(50_000_000, $info['amount']);
        $this->assertEquals('series_a', $info['round_type']);
    }

    public function test_extract_funding_info_with_billion_amount(): void
    {
        $text = 'MegaCorp secures $1.5 billion Series D';
        $info = $this->service->extractFundingInfo($text);

        $this->assertNotNull($info);
        $this->assertEquals(1_500_000_000, $info['amount']);
        $this->assertEquals('series_d', $info['round_type']);
    }

    public function test_extract_funding_info_with_seed_round(): void
    {
        $text = 'Startup lands $5M in seed funding from YC';
        $info = $this->service->extractFundingInfo($text);

        $this->assertNotNull($info);
        $this->assertEquals(5_000_000, $info['amount']);
        $this->assertEquals('seed', $info['round_type']);
    }

    public function test_extract_funding_info_with_pre_seed(): void
    {
        $text = 'NewCo raises $2 million in pre-seed round';
        $info = $this->service->extractFundingInfo($text);

        $this->assertNotNull($info);
        $this->assertEquals(2_000_000, $info['amount']);
        $this->assertEquals('pre_seed', $info['round_type']);
    }

    public function test_extract_funding_info_returns_null_for_no_funding_data(): void
    {
        $text = 'The weather is nice today in San Francisco';
        $info = $this->service->extractFundingInfo($text);

        $this->assertNull($info);
    }

    public function test_extract_funding_info_amount_only(): void
    {
        $text = 'Company announces $100 million investment';
        $info = $this->service->extractFundingInfo($text);

        $this->assertNotNull($info);
        $this->assertEquals(100_000_000, $info['amount']);
        $this->assertArrayNotHasKey('round_type', $info);
    }

    public function test_extract_funding_info_with_b_abbreviation(): void
    {
        $text = 'Valued at $3.2B after latest round';
        $info = $this->service->extractFundingInfo($text);

        $this->assertNotNull($info);
        $this->assertEquals(3_200_000_000, $info['amount']);
    }
}
