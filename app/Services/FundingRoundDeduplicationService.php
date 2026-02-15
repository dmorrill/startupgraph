<?php

namespace App\Services;

use App\Models\Company;
use App\Models\FundingRound;
use Illuminate\Support\Collection;

/**
 * Service for detecting potential duplicate funding rounds.
 *
 * Logic: Same company + similar date (within 30 days) + similar amount (within 10%)
 *
 * This can be used during import or as a cleanup command.
 */
class FundingRoundDeduplicationService
{
    /**
     * Number of days within which rounds are considered potentially duplicate.
     */
    protected int $dateTolerance;

    /**
     * Percentage tolerance for amount comparison (0.10 = 10%).
     */
    protected float $amountTolerance;

    public function __construct()
    {
        $this->dateTolerance = (int) config('startupgraph.deduplication.date_tolerance_days', 30);
        $this->amountTolerance = (float) config('startupgraph.deduplication.amount_tolerance_percent', 0.10);
    }

    /**
     * Check if a funding round would be a duplicate of an existing round.
     *
     * @param int $companyId The company ID
     * @param string $announcedDate The announced date (YYYY-MM-DD)
     * @param float|null $amount The funding amount in USD
     * @return FundingRound|null Returns the potential duplicate round, or null if no duplicate found
     */
    public function findPotentialDuplicate(int $companyId, string $announcedDate, ?float $amount): ?FundingRound
    {
        $existingRounds = FundingRound::where('company_id', $companyId)->get();

        foreach ($existingRounds as $round) {
            if ($this->isPotentialDuplicate($round, $announcedDate, $amount)) {
                return $round;
            }
        }

        return null;
    }

    /**
     * Check if a specific round is a potential duplicate of new data.
     *
     * @param FundingRound $existingRound The existing funding round
     * @param string $newDate The new announced date (YYYY-MM-DD)
     * @param float|null $newAmount The new funding amount
     * @return bool
     */
    public function isPotentialDuplicate(FundingRound $existingRound, string $newDate, ?float $newAmount): bool
    {
        // Check date proximity
        if (!$this->areDatesWithinTolerance($existingRound->announced_date, $newDate)) {
            return false;
        }

        // If both amounts are null, consider it a potential duplicate based on date alone
        if ($existingRound->amount === null && $newAmount === null) {
            return true;
        }

        // If one has an amount and the other doesn't, can't determine
        if ($existingRound->amount === null || $newAmount === null) {
            // Be conservative: if dates match closely, flag as potential duplicate
            return true;
        }

        // Check amount similarity
        return $this->areAmountsWithinTolerance((float) $existingRound->amount, $newAmount);
    }

    /**
     * Find all potential duplicate pairs within a company's funding rounds.
     *
     * @param Company|int $company The company or company ID
     * @return Collection Collection of arrays with 'round1' and 'round2' keys
     */
    public function findDuplicatesForCompany(Company|int $company): Collection
    {
        $companyId = $company instanceof Company ? $company->id : $company;
        $rounds = FundingRound::where('company_id', $companyId)
            ->orderBy('announced_date')
            ->get();

        $duplicates = collect();

        for ($i = 0; $i < $rounds->count(); $i++) {
            for ($j = $i + 1; $j < $rounds->count(); $j++) {
                $round1 = $rounds[$i];
                $round2 = $rounds[$j];

                if ($this->isPotentialDuplicate(
                    $round1,
                    $round2->announced_date->format('Y-m-d'),
                    $round2->amount
                )) {
                    $duplicates->push([
                        'round1' => $round1,
                        'round2' => $round2,
                        'date_diff_days' => abs($round1->announced_date->diffInDays($round2->announced_date)),
                        'amount_diff_percent' => $this->calculateAmountDiffPercent($round1->amount, $round2->amount),
                    ]);
                }
            }
        }

        return $duplicates;
    }

    /**
     * Find all potential duplicates across all companies in the database.
     *
     * @return Collection Collection of arrays with company and duplicates info
     */
    public function findAllDuplicates(): Collection
    {
        $results = collect();

        // Get companies that have more than one funding round
        $companyIds = FundingRound::selectRaw('company_id, COUNT(*) as round_count')
            ->groupBy('company_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('company_id');

        $companiesWithRounds = Company::whereIn('id', $companyIds)->get();

        foreach ($companiesWithRounds as $company) {
            $duplicates = $this->findDuplicatesForCompany($company);
            if ($duplicates->isNotEmpty()) {
                $results->push([
                    'company' => $company,
                    'duplicates' => $duplicates,
                ]);
            }
        }

        return $results;
    }

    /**
     * Check if two dates are within the tolerance window.
     *
     * @param \Carbon\Carbon|string $date1
     * @param string $date2
     * @return bool
     */
    protected function areDatesWithinTolerance($date1, string $date2): bool
    {
        $d1 = is_string($date1) ? \Carbon\Carbon::parse($date1) : $date1;
        $d2 = \Carbon\Carbon::parse($date2);

        return abs($d1->diffInDays($d2)) <= $this->dateTolerance;
    }

    /**
     * Check if two amounts are within the tolerance percentage.
     *
     * @param float $amount1
     * @param float $amount2
     * @return bool
     */
    protected function areAmountsWithinTolerance(float $amount1, float $amount2): bool
    {
        if ($amount1 === 0.0 && $amount2 === 0.0) {
            return true;
        }

        if ($amount1 === 0.0 || $amount2 === 0.0) {
            return false;
        }

        $maxAmount = max($amount1, $amount2);
        $diff = abs($amount1 - $amount2);
        $percentDiff = $diff / $maxAmount;

        return $percentDiff <= $this->amountTolerance;
    }

    /**
     * Calculate the percentage difference between two amounts.
     *
     * @param float|null $amount1
     * @param float|null $amount2
     * @return float|null
     */
    protected function calculateAmountDiffPercent(?float $amount1, ?float $amount2): ?float
    {
        if ($amount1 === null || $amount2 === null) {
            return null;
        }

        if ($amount1 == 0 && $amount2 == 0) {
            return 0.0;
        }

        if ($amount1 == 0 || $amount2 == 0) {
            return 100.0;
        }

        $maxAmount = max($amount1, $amount2);
        $diff = abs($amount1 - $amount2);

        return round(($diff / $maxAmount) * 100, 2);
    }

    /**
     * Set the date tolerance in days.
     *
     * @param int $days
     * @return self
     */
    public function setDateTolerance(int $days): self
    {
        $this->dateTolerance = $days;
        return $this;
    }

    /**
     * Set the amount tolerance as a decimal (e.g., 0.10 for 10%).
     *
     * @param float $tolerance
     * @return self
     */
    public function setAmountTolerance(float $tolerance): self
    {
        $this->amountTolerance = $tolerance;
        return $this;
    }

    /**
     * Get the current date tolerance.
     *
     * @return int
     */
    public function getDateTolerance(): int
    {
        return $this->dateTolerance;
    }

    /**
     * Get the current amount tolerance.
     *
     * @return float
     */
    public function getAmountTolerance(): float
    {
        return $this->amountTolerance;
    }
}
