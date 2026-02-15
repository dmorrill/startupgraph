<?php

namespace Tests\Unit;

use App\Models\FundingRound;
use App\Services\FundingRoundDeduplicationService;
use Carbon\Carbon;
use Tests\TestCase;

class FundingRoundDeduplicationServiceTest extends TestCase
{
    private FundingRoundDeduplicationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // Bypass config() call in constructor by creating and configuring manually
        $this->service = new class extends FundingRoundDeduplicationService {
            public function __construct()
            {
                $this->dateTolerance = 30;
                $this->amountTolerance = 0.10;
            }
        };
    }

    public function test_dates_within_tolerance(): void
    {
        $round = $this->makeFundingRound('2025-01-15', 1_000_000);

        $this->assertTrue(
            $this->service->isPotentialDuplicate($round, '2025-01-20', 1_000_000)
        );
    }

    public function test_dates_outside_tolerance(): void
    {
        $round = $this->makeFundingRound('2025-01-01', 1_000_000);

        $this->assertFalse(
            $this->service->isPotentialDuplicate($round, '2025-03-15', 1_000_000)
        );
    }

    public function test_amounts_within_tolerance(): void
    {
        $round = $this->makeFundingRound('2025-06-01', 10_000_000);

        // 5% difference - within 10% tolerance
        $this->assertTrue(
            $this->service->isPotentialDuplicate($round, '2025-06-05', 10_500_000)
        );
    }

    public function test_amounts_outside_tolerance(): void
    {
        $round = $this->makeFundingRound('2025-06-01', 10_000_000);

        // 50% difference - outside 10% tolerance
        $this->assertFalse(
            $this->service->isPotentialDuplicate($round, '2025-06-05', 15_000_000)
        );
    }

    public function test_both_amounts_null_is_potential_duplicate(): void
    {
        $round = $this->makeFundingRound('2025-06-01', null);

        $this->assertTrue(
            $this->service->isPotentialDuplicate($round, '2025-06-10', null)
        );
    }

    public function test_one_amount_null_flags_conservatively(): void
    {
        $round = $this->makeFundingRound('2025-06-01', 5_000_000);

        $this->assertTrue(
            $this->service->isPotentialDuplicate($round, '2025-06-05', null)
        );
    }

    public function test_set_custom_tolerances(): void
    {
        $this->service->setDateTolerance(7);
        $this->service->setAmountTolerance(0.05);

        $this->assertEquals(7, $this->service->getDateTolerance());
        $this->assertEquals(0.05, $this->service->getAmountTolerance());

        $round = $this->makeFundingRound('2025-06-01', 10_000_000);

        // 10 days apart - outside 7-day tolerance
        $this->assertFalse(
            $this->service->isPotentialDuplicate($round, '2025-06-11', 10_000_000)
        );
    }

    /**
     * Helper to create a mock FundingRound with the needed properties.
     */
    private function makeFundingRound(string $date, ?float $amount): FundingRound
    {
        $round = new FundingRound();
        $round->announced_date = Carbon::parse($date);
        $round->amount = $amount;

        return $round;
    }
}
