<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class HeadcountHistorySeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();
        $seeded = 0;

        foreach ($companies as $company) {
            $foundedYear = $company->founded_date?->year ?? 2020;
            $startYear = max($foundedYear, 2018);
            $endYear = 2025;

            if ($startYear >= $endYear) {
                continue;
            }

            // Determine current headcount: use existing or generate realistic one
            $current = $company->current_headcount;
            if (! $current || $current <= 0) {
                // Generate based on company age and a seed
                $age = 2025 - $foundedYear;
                $seed = crc32($company->name);
                $current = (int) round(max(5, min(5000, ($age * 20) + ($seed % 500))));
            }

            $years = $endYear - $startYear;
            $growthRate = 0.15 + (crc32($company->name) % 65) / 100;

            // Work backwards from current
            $annualCounts = [$current];
            for ($i = 1; $i <= $years; $i++) {
                $factor = 1 + $growthRate * (0.7 + (crc32($company->name.$i) % 60) / 100);
                $prev = end($annualCounts) / $factor;
                $annualCounts[] = max(1, (int) round($prev));
            }
            $annualCounts = array_reverse($annualCounts);

            // Generate quarterly data points
            $history = [];
            foreach ($annualCounts as $i => $count) {
                $year = $startYear + $i;
                foreach ([1, 4, 7, 10] as $month) {
                    if ($year == $endYear && $month > 6) {
                        break;
                    }
                    $date = Carbon::create($year, $month, 1)->format('Y-m-d');
                    $variance = 1 + ((crc32($date.$company->id) % 10) - 5) / 100;
                    $history[] = ['date' => $date, 'headcount' => max(1, (int) round($count * $variance))];
                }
            }

            usort($history, fn ($a, $b) => strcmp($a['date'], $b['date']));

            $company->update([
                'headcount_history' => $history,
                'current_headcount' => $current,
            ]);
            $seeded++;
        }

        $this->command->info("Seeded headcount history for {$seeded} companies.");
    }
}
