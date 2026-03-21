<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\FundingRound;
use App\Services\FundingRoundDeduplicationService;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FundingRoundDeduplicationServiceTest extends TestCase
{
    use RefreshDatabase;

    private FundingRoundDeduplicationService $service;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FundingRoundDeduplicationService();
        $this->company = Company::factory()->create();
    }

    public function test_detects_duplicate_by_date_within_tolerance()
    {
        // Create existing round
        FundingRound::factory()->create([
            'company_id' => $this->company->id,
            'announced_date' => '2023-01-15',
            'amount' => 1000000.00
        ]);

        // Test round 20 days later (within 30 day tolerance)
        $duplicate = $this->service->findPotentialDuplicate(
            $this->company->id,
            '2023-02-04', // 20 days later
            1000000.00
        );

        $this->assertNotNull($duplicate);
    }

    public function test_does_not_detect_duplicate_beyond_date_tolerance()
    {
        // Create existing round
        FundingRound::factory()->create([
            'company_id' => $this->company->id,
            'announced_date' => '2023-01-15',
            'amount' => 1000000.00
        ]);

        // Test round 40 days later (beyond 30 day tolerance)
        $duplicate = $this->service->findPotentialDuplicate(
            $this->company->id,
            '2023-02-24', // 40 days later
            1000000.00
        );

        $this->assertNull($duplicate);
    }

    public function test_detects_duplicate_with_amount_within_tolerance()
    {
        FundingRound::factory()->create([
            'company_id' => $this->company->id,
            'announced_date' => '2023-01-15',
            'amount' => 1000000.00
        ]);

        // Test with 5% amount difference (within 10% tolerance)
        $duplicate = $this->service->findPotentialDuplicate(
            $this->company->id,
            '2023-01-20',
            1050000.00 // 5% higher
        );

        $this->assertNotNull($duplicate);
    }

    public function test_does_not_detect_duplicate_beyond_amount_tolerance()
    {
        FundingRound::factory()->create([
            'company_id' => $this->company->id,
            'announced_date' => '2023-01-15',
            'amount' => 1000000.00
        ]);

        // Test with 15% amount difference (beyond 10% tolerance)
        $duplicate = $this->service->findPotentialDuplicate(
            $this->company->id,
            '2023-01-20',
            1150000.00 // 15% higher
        );

        $this->assertNull($duplicate);
    }

    public function test_handles_null_amounts()
    {
        FundingRound::factory()->create([
            'company_id' => $this->company->id,
            'announced_date' => '2023-01-15',
            'amount' => null
        ]);

        // Both null amounts should be considered potential duplicates by date only
        $duplicate = $this->service->findPotentialDuplicate(
            $this->company->id,
            '2023-01-20',
            null
        );

        $this->assertNotNull($duplicate);
    }

    public function test_does_not_detect_duplicate_for_different_company()
    {
        $otherCompany = Company::factory()->create();
        
        FundingRound::factory()->create([
            'company_id' => $otherCompany->id,
            'announced_date' => '2023-01-15',
            'amount' => 1000000.00
        ]);

        $duplicate = $this->service->findPotentialDuplicate(
            $this->company->id,
            '2023-01-20',
            1000000.00
        );

        $this->assertNull($duplicate);
    }

    public function test_finds_potential_duplicates_across_multiple_rounds()
    {
        // Create multiple rounds for same company
        FundingRound::factory()->create([
            'company_id' => $this->company->id,
            'announced_date' => '2023-01-15',
            'amount' => 1000000.00
        ]);

        FundingRound::factory()->create([
            'company_id' => $this->company->id,
            'announced_date' => '2023-03-15', // Different date range
            'amount' => 2000000.00
        ]);

        $duplicates = $this->service->findDuplicatesForCompany($this->company);
        
        // Should find no duplicates since rounds are in different date ranges
        $this->assertEmpty($duplicates);
    }

    public function test_edge_case_same_date_same_amount()
    {
        FundingRound::factory()->create([
            'company_id' => $this->company->id,
            'announced_date' => '2023-01-15',
            'amount' => 1000000.00
        ]);

        // Exact same date and amount - definitely a duplicate
        $duplicate = $this->service->findPotentialDuplicate(
            $this->company->id,
            '2023-01-15',
            1000000.00
        );

        $this->assertNotNull($duplicate);
    }

    public function test_handles_very_small_amounts()
    {
        FundingRound::factory()->create([
            'company_id' => $this->company->id,
            'announced_date' => '2023-01-15',
            'amount' => 1000.00 // $1k
        ]);

        // 10% of $1k is $100 - test boundary
        $duplicate = $this->service->findPotentialDuplicate(
            $this->company->id,
            '2023-01-20',
            1100.00 // Exactly 10% higher
        );

        $this->assertNotNull($duplicate);
    }
}