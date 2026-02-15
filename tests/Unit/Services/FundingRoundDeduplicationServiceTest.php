<?php

namespace Tests\Unit\Services;

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
        // Bypass config() in constructor by using anonymous subclass
        $this->service = new class extends FundingRoundDeduplicationService {
            public function __construct()
            {
                $this->dateTolerance = 30;
                $this->amountTolerance = 0.10;
            }

            // Expose protected methods for direct testing
            public function testAreDatesWithinTolerance($date1, string $date2): bool
            {
                return $this->areDatesWithinTolerance($date1, $date2);
            }

            public function testAreAmountsWithinTolerance(float $a1, float $a2): bool
            {
                return $this->areAmountsWithinTolerance($a1, $a2);
            }

            public function testCalculateAmountDiffPercent(?float $a1, ?float $a2): ?float
            {
                return $this->calculateAmountDiffPercent($a1, $a2);
            }
        };
    }

    // ---------------------------------------------------------------
    // isPotentialDuplicate — date proximity
    // ---------------------------------------------------------------

    public function test_dates_within_tolerance_same_amount(): void
    {
        $round = $this->makeFundingRound('2025-01-15', 1_000_000);
        $this->assertTrue($this->service->isPotentialDuplicate($round, '2025-01-20', 1_000_000));
    }

    public function test_dates_outside_tolerance_same_amount(): void
    {
        $round = $this->makeFundingRound('2025-01-01', 1_000_000);
        $this->assertFalse($this->service->isPotentialDuplicate($round, '2025-03-15', 1_000_000));
    }

    public function test_dates_exactly_at_tolerance_boundary(): void
    {
        $round = $this->makeFundingRound('2025-01-01', 5_000_000);
        // Exactly 30 days apart — should be within tolerance (<=30)
        $this->assertTrue($this->service->isPotentialDuplicate($round, '2025-01-31', 5_000_000));
    }

    public function test_dates_one_day_past_tolerance_boundary(): void
    {
        $round = $this->makeFundingRound('2025-01-01', 5_000_000);
        // 31 days apart — should be outside tolerance
        $this->assertFalse($this->service->isPotentialDuplicate($round, '2025-02-01', 5_000_000));
    }

    public function test_same_date_is_duplicate(): void
    {
        $round = $this->makeFundingRound('2025-06-15', 2_000_000);
        $this->assertTrue($this->service->isPotentialDuplicate($round, '2025-06-15', 2_000_000));
    }

    public function test_date_order_does_not_matter(): void
    {
        // New date is before existing round date
        $round = $this->makeFundingRound('2025-06-20', 3_000_000);
        $this->assertTrue($this->service->isPotentialDuplicate($round, '2025-06-01', 3_000_000));
    }

    // ---------------------------------------------------------------
    // isPotentialDuplicate — amount proximity
    // ---------------------------------------------------------------

    public function test_amounts_within_tolerance(): void
    {
        $round = $this->makeFundingRound('2025-06-01', 10_000_000);
        // 5% diff
        $this->assertTrue($this->service->isPotentialDuplicate($round, '2025-06-05', 10_500_000));
    }

    public function test_amounts_outside_tolerance(): void
    {
        $round = $this->makeFundingRound('2025-06-01', 10_000_000);
        // 50% diff
        $this->assertFalse($this->service->isPotentialDuplicate($round, '2025-06-05', 15_000_000));
    }

    public function test_amounts_exactly_at_ten_percent_boundary(): void
    {
        $round = $this->makeFundingRound('2025-06-01', 10_000_000);
        // Exactly 10% diff — should be within tolerance (<=10%)
        $this->assertTrue($this->service->isPotentialDuplicate($round, '2025-06-05', 11_000_000));
    }

    public function test_amounts_just_over_ten_percent(): void
    {
        $round = $this->makeFundingRound('2025-06-01', 10_000_000);
        // 10.01% of 11_001_000 ≈ exceeds tolerance
        $this->assertFalse($this->service->isPotentialDuplicate($round, '2025-06-05', 11_200_000));
    }

    public function test_identical_amounts(): void
    {
        $round = $this->makeFundingRound('2025-06-01', 7_500_000);
        $this->assertTrue($this->service->isPotentialDuplicate($round, '2025-06-10', 7_500_000));
    }

    // ---------------------------------------------------------------
    // isPotentialDuplicate — null amount handling
    // ---------------------------------------------------------------

    public function test_both_amounts_null_is_duplicate(): void
    {
        $round = $this->makeFundingRound('2025-06-01', null);
        $this->assertTrue($this->service->isPotentialDuplicate($round, '2025-06-10', null));
    }

    public function test_existing_amount_null_new_has_value(): void
    {
        $round = $this->makeFundingRound('2025-06-01', null);
        // Conservative: flags as duplicate
        $this->assertTrue($this->service->isPotentialDuplicate($round, '2025-06-05', 5_000_000));
    }

    public function test_existing_has_value_new_amount_null(): void
    {
        $round = $this->makeFundingRound('2025-06-01', 5_000_000);
        $this->assertTrue($this->service->isPotentialDuplicate($round, '2025-06-05', null));
    }

    public function test_null_amounts_outside_date_tolerance_not_duplicate(): void
    {
        $round = $this->makeFundingRound('2025-01-01', null);
        $this->assertFalse($this->service->isPotentialDuplicate($round, '2025-06-01', null));
    }

    // ---------------------------------------------------------------
    // Custom tolerance setters/getters
    // ---------------------------------------------------------------

    public function test_set_custom_date_tolerance(): void
    {
        $this->service->setDateTolerance(7);
        $this->assertEquals(7, $this->service->getDateTolerance());

        $round = $this->makeFundingRound('2025-06-01', 10_000_000);
        // 10 days apart — outside 7-day tolerance
        $this->assertFalse($this->service->isPotentialDuplicate($round, '2025-06-11', 10_000_000));
        // 5 days apart — within 7-day tolerance
        $this->assertTrue($this->service->isPotentialDuplicate($round, '2025-06-06', 10_000_000));
    }

    public function test_set_custom_amount_tolerance(): void
    {
        $this->service->setAmountTolerance(0.05);
        $this->assertEquals(0.05, $this->service->getAmountTolerance());

        $round = $this->makeFundingRound('2025-06-01', 10_000_000);
        // 8% diff — outside 5% tolerance
        $this->assertFalse($this->service->isPotentialDuplicate($round, '2025-06-05', 10_800_000));
        // 3% diff — within 5% tolerance
        $this->assertTrue($this->service->isPotentialDuplicate($round, '2025-06-05', 10_300_000));
    }

    public function test_setter_returns_self_for_chaining(): void
    {
        $result = $this->service->setDateTolerance(14)->setAmountTolerance(0.20);
        $this->assertSame($this->service, $result);
        $this->assertEquals(14, $this->service->getDateTolerance());
        $this->assertEquals(0.20, $this->service->getAmountTolerance());
    }

    // ---------------------------------------------------------------
    // areDatesWithinTolerance (direct)
    // ---------------------------------------------------------------

    public function test_dates_within_tolerance_with_string_input(): void
    {
        $this->assertTrue($this->service->testAreDatesWithinTolerance('2025-03-01', '2025-03-15'));
    }

    public function test_dates_within_tolerance_with_carbon_input(): void
    {
        $this->assertTrue($this->service->testAreDatesWithinTolerance(Carbon::parse('2025-03-01'), '2025-03-15'));
    }

    // ---------------------------------------------------------------
    // areAmountsWithinTolerance (direct)
    // ---------------------------------------------------------------

    public function test_both_amounts_zero(): void
    {
        $this->assertTrue($this->service->testAreAmountsWithinTolerance(0.0, 0.0));
    }

    public function test_one_amount_zero_other_nonzero(): void
    {
        $this->assertFalse($this->service->testAreAmountsWithinTolerance(0.0, 100.0));
        $this->assertFalse($this->service->testAreAmountsWithinTolerance(100.0, 0.0));
    }

    public function test_very_small_amounts(): void
    {
        // 1.00 vs 1.05 = 5% diff, within tolerance
        $this->assertTrue($this->service->testAreAmountsWithinTolerance(1.00, 1.05));
    }

    public function test_very_large_amounts(): void
    {
        // 1B vs 1.05B = 5% diff, within tolerance
        $this->assertTrue($this->service->testAreAmountsWithinTolerance(1_000_000_000, 1_050_000_000));
    }

    // ---------------------------------------------------------------
    // calculateAmountDiffPercent (direct)
    // ---------------------------------------------------------------

    public function test_diff_percent_both_null(): void
    {
        $this->assertNull($this->service->testCalculateAmountDiffPercent(null, null));
    }

    public function test_diff_percent_one_null(): void
    {
        $this->assertNull($this->service->testCalculateAmountDiffPercent(100.0, null));
        $this->assertNull($this->service->testCalculateAmountDiffPercent(null, 100.0));
    }

    public function test_diff_percent_both_zero(): void
    {
        $this->assertEquals(0.0, $this->service->testCalculateAmountDiffPercent(0.0, 0.0));
    }

    public function test_diff_percent_one_zero(): void
    {
        $this->assertEquals(100.0, $this->service->testCalculateAmountDiffPercent(0.0, 500.0));
        $this->assertEquals(100.0, $this->service->testCalculateAmountDiffPercent(500.0, 0.0));
    }

    public function test_diff_percent_normal_values(): void
    {
        // 10M vs 11M = 1M/11M ≈ 9.09%
        $result = $this->service->testCalculateAmountDiffPercent(10_000_000, 11_000_000);
        $this->assertEqualsWithDelta(9.09, $result, 0.01);
    }

    public function test_diff_percent_identical_values(): void
    {
        $this->assertEquals(0.0, $this->service->testCalculateAmountDiffPercent(5_000_000, 5_000_000));
    }

    // ---------------------------------------------------------------
    // Edge cases
    // ---------------------------------------------------------------

    public function test_zero_date_tolerance_requires_exact_date(): void
    {
        $this->service->setDateTolerance(0);
        $round = $this->makeFundingRound('2025-06-15', 1_000_000);

        $this->assertTrue($this->service->isPotentialDuplicate($round, '2025-06-15', 1_000_000));
        $this->assertFalse($this->service->isPotentialDuplicate($round, '2025-06-16', 1_000_000));
    }

    public function test_zero_amount_tolerance_requires_exact_amount(): void
    {
        $this->service->setAmountTolerance(0.0);
        $round = $this->makeFundingRound('2025-06-01', 1_000_000);

        $this->assertTrue($this->service->isPotentialDuplicate($round, '2025-06-05', 1_000_000));
        $this->assertFalse($this->service->isPotentialDuplicate($round, '2025-06-05', 1_000_001));
    }

    public function test_very_large_tolerance_matches_broadly(): void
    {
        $this->service->setDateTolerance(365);
        $this->service->setAmountTolerance(0.50);

        $round = $this->makeFundingRound('2025-01-01', 10_000_000);
        $this->assertTrue($this->service->isPotentialDuplicate($round, '2025-12-01', 14_000_000));
    }

    // ---------------------------------------------------------------
    // Helper
    // ---------------------------------------------------------------

    private function makeFundingRound(string $date, ?float $amount): FundingRound
    {
        $round = new FundingRound();
        $round->announced_date = Carbon::parse($date);
        $round->amount = $amount;

        return $round;
    }
}
