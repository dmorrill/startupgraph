<?php

namespace Tests\Unit\Jobs;

use App\Jobs\FetchCompanyHeadcountJob;
use App\Models\Company;
use App\Services\LinkedInService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class FetchCompanyHeadcountJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_handles_successful_headcount_fetch(): void
    {
        $company = Company::factory()->create([
            'linkedin_url' => 'https://www.linkedin.com/company/example',
            'current_headcount' => 50,
        ]);

        $mockLinkedInService = Mockery::mock(LinkedInService::class);
        $mockLinkedInService->shouldReceive('fetchHeadcount')
            ->with($company->linkedin_url)
            ->andReturn([
                'success' => true,
                'headcount' => 75,
            ]);

        $job = new FetchCompanyHeadcountJob($company);
        $job->handle($mockLinkedInService);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'current_headcount' => 75,
        ]);

        $this->assertDatabaseHas('headcount_snapshots', [
            'company_id' => $company->id,
            'headcount' => 75,
        ]);

        $this->assertDatabaseHas('scheduled_task_executions', [
            'company_id' => $company->id,
            'task_type' => 'headcount_fetch',
            'status' => 'success',
        ]);
    }

    public function test_job_handles_company_without_linkedin_url(): void
    {
        $company = Company::factory()->create([
            'linkedin_url' => null,
        ]);

        $mockLinkedInService = Mockery::mock(LinkedInService::class);

        $job = new FetchCompanyHeadcountJob($company);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Company has no LinkedIn URL');

        $job->handle($mockLinkedInService);

        $this->assertDatabaseHas('scheduled_task_executions', [
            'company_id' => $company->id,
            'status' => 'failed',
            'error_message' => 'Company has no LinkedIn URL',
        ]);
    }

    public function test_job_handles_linkedin_service_error(): void
    {
        $company = Company::factory()->create([
            'linkedin_url' => 'https://www.linkedin.com/company/example',
        ]);

        $mockLinkedInService = Mockery::mock(LinkedInService::class);
        $mockLinkedInService->shouldReceive('fetchHeadcount')
            ->andReturn([
                'success' => false,
                'error' => 'Rate limited',
            ]);

        $job = new FetchCompanyHeadcountJob($company);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Rate limited');

        $job->handle($mockLinkedInService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
