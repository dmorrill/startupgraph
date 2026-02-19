<?php

namespace Tests\Unit\Services\Discovery;

use App\Services\LinkedInService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LinkedInServiceTest extends TestCase
{
    private LinkedInService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LinkedInService();
    }

    public function test_fetch_headcount_from_json_ld(): void
    {
        Http::fake([
            'linkedin.com/*' => Http::response(
                '<html><body>"numberOfEmployees":{"value":250}</body></html>'
            ),
        ]);

        $result = $this->service->fetchHeadcount('https://linkedin.com/company/test');

        $this->assertTrue($result['success']);
        $this->assertEquals(250, $result['headcount']);
        $this->assertNull($result['error']);
    }

    public function test_fetch_headcount_from_employees_pattern(): void
    {
        Http::fake([
            'linkedin.com/*' => Http::response(
                '<html><body><span>1,500 employees</span></body></html>'
            ),
        ]);

        $result = $this->service->fetchHeadcount('https://linkedin.com/company/test');

        $this->assertTrue($result['success']);
        $this->assertEquals(1500, $result['headcount']);
    }

    public function test_fetch_headcount_returns_failure_on_http_error(): void
    {
        Http::fake([
            'linkedin.com/*' => Http::response('', 403),
        ]);

        $result = $this->service->fetchHeadcount('https://linkedin.com/company/test');

        $this->assertFalse($result['success']);
        $this->assertNull($result['headcount']);
        $this->assertEquals('HTTP 403', $result['error']);
    }

    public function test_fetch_headcount_returns_failure_when_no_data_found(): void
    {
        Http::fake([
            'linkedin.com/*' => Http::response('<html><body>No headcount info here</body></html>'),
        ]);

        $result = $this->service->fetchHeadcount('https://linkedin.com/company/test');

        $this->assertFalse($result['success']);
        $this->assertNull($result['headcount']);
        $this->assertEquals('Could not extract headcount from page', $result['error']);
    }

    public function test_fetch_headcount_handles_exception(): void
    {
        Http::fake([
            'linkedin.com/*' => function () {
                throw new \Exception('Connection refused');
            },
        ]);

        $result = $this->service->fetchHeadcount('https://linkedin.com/company/test');

        $this->assertFalse($result['success']);
        $this->assertNull($result['headcount']);
        $this->assertEquals('Connection refused', $result['error']);
    }
}
