<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\FundingRound;
use App\Models\Investor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MoreFundingDataSeeder extends Seeder
{
    public function run(): void
    {
        $investors = $this->createInvestors();

        $this->seedDatabricksFunding($investors);
        $this->seedCanvaFunding($investors);
        $this->seedFigmaFunding($investors);
        $this->seedNotionFunding($investors);
        $this->seedRipplingFunding($investors);
    }

    private function createInvestors(): array
    {
        $investorData = [
            // Additional VCs not in original seeder
            ['name' => 'DST Global', 'type' => 'vc', 'website' => 'https://dst-global.com'],
            ['name' => 'Insight Partners', 'type' => 'vc', 'website' => 'https://insightpartners.com'],
            ['name' => 'Greenoaks', 'type' => 'vc', 'website' => 'https://greenoakscap.com'],
            ['name' => 'Greylock', 'type' => 'vc', 'website' => 'https://greylock.com'],
            ['name' => 'Kleiner Perkins', 'type' => 'vc', 'website' => 'https://kleinerperkins.com'],
            ['name' => 'Blackbird Ventures', 'type' => 'vc', 'website' => 'https://blackbird.vc'],
            ['name' => 'Felicis Ventures', 'type' => 'vc', 'website' => 'https://felicis.com'],
            ['name' => 'Durable Capital Partners', 'type' => 'growth', 'website' => 'https://durablecapital.com'],
            ['name' => 'Alkeon Capital Management', 'type' => 'growth', 'website' => 'https://alkeon.com'],
            ['name' => 'Y Combinator', 'type' => 'accelerator', 'website' => 'https://ycombinator.com'],
            ['name' => 'Sands Capital', 'type' => 'growth', 'website' => 'https://sandscapital.com'],
            ['name' => 'GIC', 'type' => 'sovereign', 'website' => 'https://gic.com.sg'],
            ['name' => 'Goldman Sachs Growth', 'type' => 'growth', 'website' => 'https://gs.com'],
            ['name' => 'MGX', 'type' => 'sovereign', 'website' => 'https://mgx.ae'],
            ['name' => 'BlackRock', 'type' => 'growth', 'website' => 'https://blackrock.com'],
            ['name' => 'Blackstone', 'type' => 'growth', 'website' => 'https://blackstone.com'],
            ['name' => 'Temasek', 'type' => 'sovereign', 'website' => 'https://temasek.com.sg'],
            ['name' => 'Ontario Teachers Pension Plan', 'type' => 'pension', 'website' => 'https://otpp.com'],
            ['name' => 'J.P. Morgan Asset Management', 'type' => 'growth', 'website' => 'https://am.jpmorgan.com'],
            ['name' => 'WCM Investment Management', 'type' => 'growth', 'website' => 'https://wcminvest.com'],

            // Individuals
            ['name' => 'Elad Gil', 'type' => 'angel', 'website' => null],
            ['name' => 'Daniel Gross', 'type' => 'angel', 'website' => null],
            ['name' => 'Lachy Groom', 'type' => 'angel', 'website' => null],
            ['name' => 'Bob Iger', 'type' => 'angel', 'website' => null],
        ];

        // First get existing investors
        $investors = [];
        $existingInvestors = Investor::all();
        foreach ($existingInvestors as $investor) {
            $investors[$investor->name] = $investor;
        }

        // Create new ones
        foreach ($investorData as $data) {
            $data['slug'] = Str::slug($data['name']);
            $investor = Investor::firstOrCreate(['slug' => $data['slug']], $data);
            $investors[$data['name']] = $investor;
        }

        return $investors;
    }

    private function seedDatabricksFunding(array $investors): void
    {
        $company = Company::where('slug', 'databricks')->first();
        if (! $company) {
            return;
        }
        if ($company->fundingRounds()->exists()) {
            return;
        }

        $rounds = [
            [
                'round_type' => 'series_a',
                'amount' => 14000000,
                'announced_date' => '2013-09-01',
                'investors' => ['Andreessen Horowitz'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 33000000,
                'announced_date' => '2014-12-01',
                'investors' => ['Andreessen Horowitz', 'New Enterprise Associates'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 60000000,
                'announced_date' => '2016-12-01',
                'investors' => ['Andreessen Horowitz', 'New Enterprise Associates'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 140000000,
                'announced_date' => '2017-08-01',
                'investors' => ['Andreessen Horowitz'],
            ],
            [
                'round_type' => 'series_e',
                'amount' => 250000000,
                'announced_date' => '2019-02-01',
                'pre_money_valuation' => 2500000000,
                'investors' => ['Andreessen Horowitz', 'Coatue Management'],
            ],
            [
                'round_type' => 'series_f',
                'amount' => 400000000,
                'announced_date' => '2020-02-01',
                'pre_money_valuation' => 6200000000,
                'investors' => ['Andreessen Horowitz', 'T. Rowe Price'],
            ],
            [
                'round_type' => 'series_g',
                'amount' => 1000000000,
                'announced_date' => '2021-02-01',
                'pre_money_valuation' => 28000000000,
                'investors' => ['Fidelity Investments', 'T. Rowe Price', 'Tiger Global'],
            ],
            [
                'round_type' => 'series_h',
                'amount' => 1600000000,
                'announced_date' => '2021-08-01',
                'pre_money_valuation' => 38000000000,
                'investors' => ['Fidelity Investments', 'T. Rowe Price', 'Morgan Stanley'],
            ],
            [
                'round_type' => 'series_i',
                'amount' => 500000000,
                'announced_date' => '2023-09-01',
                'pre_money_valuation' => 43000000000,
                'investors' => ['T. Rowe Price'],
            ],
            [
                'round_type' => 'series_j',
                'amount' => 10000000000,
                'announced_date' => '2024-12-01',
                'pre_money_valuation' => 52000000000,
                'investors' => ['Thrive Capital', 'Andreessen Horowitz', 'DST Global', 'Insight Partners'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedCanvaFunding(array $investors): void
    {
        $company = Company::where('slug', 'canva')->first();
        if (! $company) {
            return;
        }
        if ($company->fundingRounds()->exists()) {
            return;
        }

        $rounds = [
            [
                'round_type' => 'seed',
                'amount' => 3000000,
                'announced_date' => '2013-03-01',
                'investors' => ['Blackbird Ventures'],
            ],
            [
                'round_type' => 'series_a',
                'amount' => 15000000,
                'announced_date' => '2015-04-01',
                'investors' => ['Felicis Ventures', 'Blackbird Ventures'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 40000000,
                'announced_date' => '2018-01-01',
                'pre_money_valuation' => 1000000000,
                'investors' => ['Sequoia Capital', 'Blackbird Ventures'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 85000000,
                'announced_date' => '2019-05-01',
                'pre_money_valuation' => 2500000000,
                'investors' => ['General Catalyst', 'Sequoia Capital'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 60000000,
                'announced_date' => '2020-06-01',
                'pre_money_valuation' => 6000000000,
                'investors' => ['Blackbird Ventures', 'Sequoia Capital'],
            ],
            [
                'round_type' => 'series_e',
                'amount' => 71000000,
                'announced_date' => '2021-04-01',
                'pre_money_valuation' => 15000000000,
                'investors' => ['Dragoneer Investment Group', 'Blackbird Ventures'],
            ],
            [
                'round_type' => 'series_f',
                'amount' => 200000000,
                'announced_date' => '2021-09-01',
                'pre_money_valuation' => 40000000000,
                'investors' => ['T. Rowe Price', 'Dragoneer Investment Group'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedFigmaFunding(array $investors): void
    {
        $company = Company::where('slug', 'figma')->first();
        if (! $company) {
            return;
        }
        if ($company->fundingRounds()->exists()) {
            return;
        }

        $rounds = [
            [
                'round_type' => 'seed',
                'amount' => 3800000,
                'announced_date' => '2013-06-01',
                'investors' => ['Index Ventures'],
            ],
            [
                'round_type' => 'series_a',
                'amount' => 14000000,
                'announced_date' => '2015-12-01',
                'investors' => ['Greylock'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 25000000,
                'announced_date' => '2018-02-01',
                'investors' => ['Kleiner Perkins', 'Greylock', 'Index Ventures'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 40000000,
                'announced_date' => '2019-02-01',
                'pre_money_valuation' => 400000000,
                'investors' => ['Coatue Management', 'Sequoia Capital'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 50000000,
                'announced_date' => '2020-04-01',
                'pre_money_valuation' => 2000000000,
                'investors' => ['Durable Capital Partners', 'Andreessen Horowitz'],
            ],
            [
                'round_type' => 'series_e',
                'amount' => 200000000,
                'announced_date' => '2021-06-01',
                'pre_money_valuation' => 10000000000,
                'investors' => ['Durable Capital Partners', 'Sequoia Capital', 'Andreessen Horowitz'],
            ],
            [
                'round_type' => 'series_f',
                'amount' => 416000000,
                'announced_date' => '2024-05-01',
                'pre_money_valuation' => 12500000000,
                'investors' => ['Coatue Management', 'Alkeon Capital Management', 'General Catalyst', 'Sequoia Capital', 'Thrive Capital', 'Andreessen Horowitz', 'Kleiner Perkins', 'Fidelity Investments'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedNotionFunding(array $investors): void
    {
        $company = Company::where('slug', 'notion')->first();
        if (! $company) {
            return;
        }
        if ($company->fundingRounds()->exists()) {
            return;
        }

        $rounds = [
            [
                'round_type' => 'seed',
                'amount' => 2000000,
                'announced_date' => '2013-06-01',
                'investors' => [],
            ],
            [
                'round_type' => 'series_a',
                'amount' => 10000000,
                'announced_date' => '2019-07-01',
                'pre_money_valuation' => 800000000,
                'investors' => ['Index Ventures'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 50000000,
                'announced_date' => '2020-04-01',
                'pre_money_valuation' => 2000000000,
                'investors' => ['Index Ventures'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 67700000,
                'announced_date' => '2020-09-01',
                'pre_money_valuation' => 2000000000,
                'investors' => ['Index Ventures', 'Elad Gil', 'Daniel Gross', 'Lachy Groom'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 275000000,
                'announced_date' => '2021-10-01',
                'pre_money_valuation' => 10000000000,
                'investors' => ['Coatue Management', 'Sequoia Capital'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedRipplingFunding(array $investors): void
    {
        $company = Company::where('slug', 'rippling')->first();
        if (! $company) {
            return;
        }
        if ($company->fundingRounds()->exists()) {
            return;
        }

        $rounds = [
            [
                'round_type' => 'series_a',
                'amount' => 10000000,
                'announced_date' => '2017-06-01',
                'investors' => ['Y Combinator'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 145000000,
                'announced_date' => '2020-08-01',
                'pre_money_valuation' => 1350000000,
                'investors' => ['Founders Fund', 'Kleiner Perkins', 'Sequoia Capital', 'Y Combinator'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 250000000,
                'announced_date' => '2021-10-01',
                'pre_money_valuation' => 6250000000,
                'investors' => ['Kleiner Perkins', 'Sequoia Capital'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 250000000,
                'announced_date' => '2022-05-01',
                'pre_money_valuation' => 11250000000,
                'investors' => ['Kleiner Perkins', 'Greenoaks'],
            ],
            [
                'round_type' => 'series_e',
                'amount' => 500000000,
                'announced_date' => '2023-03-01',
                'pre_money_valuation' => 11000000000,
                'investors' => ['Greenoaks', 'Kleiner Perkins', 'Sequoia Capital'],
            ],
            [
                'round_type' => 'series_f',
                'amount' => 200000000,
                'announced_date' => '2024-04-01',
                'pre_money_valuation' => 13200000000,
                'investors' => ['Coatue Management'],
            ],
            [
                'round_type' => 'series_g',
                'amount' => 450000000,
                'announced_date' => '2025-05-01',
                'pre_money_valuation' => 16350000000,
                'investors' => ['Sands Capital', 'GIC', 'Goldman Sachs Growth', 'Baillie Gifford', 'Elad Gil', 'Y Combinator'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function createFundingRounds(Company $company, array $rounds, array $investors): void
    {
        foreach ($rounds as $roundData) {
            $investorNames = $roundData['investors'] ?? [];
            unset($roundData['investors']);

            $roundData['company_id'] = $company->id;

            $round = FundingRound::firstOrCreate(
                ['company_id' => $roundData['company_id'], 'announced_date' => $roundData['announced_date'], 'round_type' => $roundData['round_type'] ?? null],
                $roundData
            );

            foreach ($investorNames as $index => $name) {
                if (isset($investors[$name])) {
                    $round->investors()->attach($investors[$name]->id, [
                        'is_lead' => $index === 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
