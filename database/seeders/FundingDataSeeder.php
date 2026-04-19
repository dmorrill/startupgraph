<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\FundingRound;
use App\Models\Investor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FundingDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create investors first
        $investors = $this->createInvestors();

        // Add funding rounds for key companies
        $this->seedAnthropicFunding($investors);
        $this->seedOpenAIFunding($investors);
        $this->seedPerplexityFunding($investors);
        $this->seedStripeFunding($investors);
        $this->seedSpaceXFunding($investors);
    }

    private function createInvestors(): array
    {
        $investorData = [
            // Mega tech
            ['name' => 'Google', 'type' => 'corporate', 'website' => 'https://google.com'],
            ['name' => 'Microsoft', 'type' => 'corporate', 'website' => 'https://microsoft.com'],
            ['name' => 'Amazon', 'type' => 'corporate', 'website' => 'https://amazon.com'],
            ['name' => 'Nvidia', 'type' => 'corporate', 'website' => 'https://nvidia.com'],

            // Major VCs
            ['name' => 'Sequoia Capital', 'type' => 'vc', 'website' => 'https://sequoiacap.com'],
            ['name' => 'Andreessen Horowitz', 'type' => 'vc', 'website' => 'https://a16z.com'],
            ['name' => 'Lightspeed Venture Partners', 'type' => 'vc', 'website' => 'https://lsvp.com'],
            ['name' => 'Founders Fund', 'type' => 'vc', 'website' => 'https://foundersfund.com'],
            ['name' => 'Thrive Capital', 'type' => 'vc', 'website' => 'https://thrivecap.com'],
            ['name' => 'Tiger Global', 'type' => 'vc', 'website' => 'https://tigerglobal.com'],
            ['name' => 'Coatue Management', 'type' => 'vc', 'website' => 'https://coatue.com'],
            ['name' => 'General Catalyst', 'type' => 'vc', 'website' => 'https://generalcatalyst.com'],
            ['name' => 'Khosla Ventures', 'type' => 'vc', 'website' => 'https://khoslaventures.com'],
            ['name' => 'Spark Capital', 'type' => 'vc', 'website' => 'https://sparkcapital.com'],
            ['name' => 'Menlo Ventures', 'type' => 'vc', 'website' => 'https://menlovc.com'],
            ['name' => 'New Enterprise Associates', 'type' => 'vc', 'website' => 'https://nea.com'],
            ['name' => 'IVP', 'type' => 'vc', 'website' => 'https://ivp.com'],
            ['name' => 'Bessemer Venture Partners', 'type' => 'vc', 'website' => 'https://bvp.com'],
            ['name' => 'Accel', 'type' => 'vc', 'website' => 'https://accel.com'],
            ['name' => 'Index Ventures', 'type' => 'vc', 'website' => 'https://indexventures.com'],
            ['name' => 'ICONIQ Capital', 'type' => 'vc', 'website' => 'https://iconiqcapital.com'],
            ['name' => 'D1 Capital Partners', 'type' => 'vc', 'website' => 'https://d1cap.com'],
            ['name' => 'Valor Equity Partners', 'type' => 'vc', 'website' => 'https://valorep.com'],

            // Growth/Late Stage
            ['name' => 'SoftBank Vision Fund', 'type' => 'vc', 'website' => 'https://softbank.com'],
            ['name' => 'Fidelity Investments', 'type' => 'growth', 'website' => 'https://fidelity.com'],
            ['name' => 'T. Rowe Price', 'type' => 'growth', 'website' => 'https://troweprice.com'],
            ['name' => 'Baillie Gifford', 'type' => 'growth', 'website' => 'https://bailliegifford.com'],
            ['name' => 'Dragoneer Investment Group', 'type' => 'growth', 'website' => 'https://dragoneer.com'],

            // Corporate VCs
            ['name' => 'Google Ventures', 'type' => 'corporate', 'website' => 'https://gv.com'],
            ['name' => 'Salesforce Ventures', 'type' => 'corporate', 'website' => 'https://salesforceventures.com'],
            ['name' => 'Cisco Investments', 'type' => 'corporate', 'website' => 'https://cisco.com'],

            // International
            ['name' => 'Databricks', 'type' => 'corporate', 'website' => 'https://databricks.com'],
            ['name' => 'SK Telecom', 'type' => 'corporate', 'website' => 'https://sktelecom.com'],

            // Notable angels/individuals (represented as entities)
            ['name' => 'Jeff Bezos', 'type' => 'angel', 'website' => null],
            ['name' => 'Reid Hoffman', 'type' => 'angel', 'website' => null],
        ];

        $investors = [];
        foreach ($investorData as $data) {
            $data['slug'] = Str::slug($data['name']);
            $investor = Investor::firstOrCreate(['slug' => $data['slug']], $data);
            $investors[$data['name']] = $investor;
        }

        return $investors;
    }

    private function seedAnthropicFunding(array $investors): void
    {
        $company = Company::where('slug', 'anthropic')->first();
        if (! $company) {
            return;
        }

        $rounds = [
            [
                'round_type' => 'series_a',
                'amount' => 124000000,
                'announced_date' => '2021-05-01',
                'investors' => ['Google'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 580000000,
                'announced_date' => '2022-04-01',
                'pre_money_valuation' => 4000000000,
                'investors' => [],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 450000000,
                'announced_date' => '2023-05-01',
                'investors' => ['Spark Capital'],
            ],
            [
                'round_type' => 'strategic',
                'amount' => 4000000000,
                'announced_date' => '2023-09-01',
                'investors' => ['Amazon'],
            ],
            [
                'round_type' => 'strategic',
                'amount' => 2000000000,
                'announced_date' => '2023-10-01',
                'investors' => ['Google'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 750000000,
                'announced_date' => '2024-02-01',
                'pre_money_valuation' => 18100000000,
                'investors' => ['Menlo Ventures'],
            ],
            [
                'round_type' => 'strategic',
                'amount' => 4000000000,
                'announced_date' => '2024-11-01',
                'investors' => ['Amazon'],
            ],
            [
                'round_type' => 'series_e',
                'amount' => 3500000000,
                'announced_date' => '2025-03-01',
                'pre_money_valuation' => 61500000000,
                'investors' => ['Lightspeed Venture Partners', 'Bessemer Venture Partners', 'Cisco Investments', 'General Catalyst', 'Salesforce Ventures'],
            ],
            [
                'round_type' => 'series_f',
                'amount' => 13000000000,
                'announced_date' => '2025-10-01',
                'pre_money_valuation' => 183000000000,
                'investors' => ['ICONIQ Capital', 'Fidelity Investments', 'Lightspeed Venture Partners'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedOpenAIFunding(array $investors): void
    {
        $company = Company::where('slug', 'openai')->first();
        if (! $company) {
            return;
        }

        $rounds = [
            [
                'round_type' => 'seed',
                'amount' => 1000000000,
                'announced_date' => '2015-12-01',
                'investors' => ['Reid Hoffman'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 1000000000,
                'announced_date' => '2019-07-01',
                'investors' => ['Microsoft'],
            ],
            [
                'round_type' => 'strategic',
                'amount' => 10000000000,
                'announced_date' => '2023-01-01',
                'investors' => ['Microsoft'],
            ],
            [
                'round_type' => 'series_e',
                'amount' => 6600000000,
                'announced_date' => '2024-10-01',
                'pre_money_valuation' => 150000000000,
                'investors' => ['Thrive Capital', 'Microsoft', 'Nvidia', 'SoftBank Vision Fund', 'Khosla Ventures', 'Fidelity Investments', 'Tiger Global'],
            ],
            [
                'round_type' => 'series_f',
                'amount' => 40000000000,
                'announced_date' => '2025-03-01',
                'pre_money_valuation' => 300000000000,
                'investors' => ['SoftBank Vision Fund'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedPerplexityFunding(array $investors): void
    {
        $company = Company::where('slug', 'perplexity')->first();
        if (! $company) {
            return;
        }

        $rounds = [
            [
                'round_type' => 'series_a',
                'amount' => 25600000,
                'announced_date' => '2023-03-01',
                'investors' => ['New Enterprise Associates'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 73600000,
                'announced_date' => '2024-01-01',
                'pre_money_valuation' => 520000000,
                'investors' => ['Nvidia', 'Databricks', 'Bessemer Venture Partners', 'Jeff Bezos'],
            ],
            [
                'round_type' => 'series_b_extension',
                'amount' => 62700000,
                'announced_date' => '2024-04-01',
                'pre_money_valuation' => 1000000000,
                'investors' => ['IVP', 'Jeff Bezos'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 250000000,
                'announced_date' => '2024-06-01',
                'pre_money_valuation' => 3000000000,
                'investors' => ['SoftBank Vision Fund'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 500000000,
                'announced_date' => '2024-12-01',
                'pre_money_valuation' => 9000000000,
                'investors' => ['IVP'],
            ],
            [
                'round_type' => 'series_e',
                'amount' => 200000000,
                'announced_date' => '2025-09-01',
                'pre_money_valuation' => 20000000000,
                'investors' => [],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedStripeFunding(array $investors): void
    {
        $company = Company::where('slug', 'stripe')->first();
        if (! $company) {
            return;
        }

        $rounds = [
            [
                'round_type' => 'seed',
                'amount' => 2000000,
                'announced_date' => '2010-06-01',
                'investors' => ['Sequoia Capital'],
            ],
            [
                'round_type' => 'series_a',
                'amount' => 18000000,
                'announced_date' => '2012-02-01',
                'investors' => ['Sequoia Capital', 'Andreessen Horowitz', 'Founders Fund'],
            ],
            [
                'round_type' => 'series_b',
                'amount' => 20000000,
                'announced_date' => '2013-03-01',
                'investors' => ['Sequoia Capital', 'General Catalyst', 'Founders Fund'],
            ],
            [
                'round_type' => 'series_c',
                'amount' => 80000000,
                'announced_date' => '2014-12-01',
                'pre_money_valuation' => 3500000000,
                'investors' => ['Thrive Capital', 'Founders Fund', 'Sequoia Capital'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 150000000,
                'announced_date' => '2016-11-01',
                'pre_money_valuation' => 9000000000,
                'investors' => ['General Catalyst', 'Sequoia Capital'],
            ],
            [
                'round_type' => 'series_e',
                'amount' => 245000000,
                'announced_date' => '2018-09-01',
                'pre_money_valuation' => 20000000000,
                'investors' => ['Tiger Global', 'Sequoia Capital', 'Andreessen Horowitz'],
            ],
            [
                'round_type' => 'series_f',
                'amount' => 100000000,
                'announced_date' => '2019-01-01',
                'pre_money_valuation' => 22500000000,
                'investors' => ['Tiger Global'],
            ],
            [
                'round_type' => 'series_g',
                'amount' => 600000000,
                'announced_date' => '2020-04-01',
                'pre_money_valuation' => 36000000000,
                'investors' => ['Andreessen Horowitz', 'Sequoia Capital', 'General Catalyst'],
            ],
            [
                'round_type' => 'series_h',
                'amount' => 600000000,
                'announced_date' => '2021-03-01',
                'pre_money_valuation' => 95000000000,
                'investors' => ['Fidelity Investments', 'Baillie Gifford', 'Sequoia Capital'],
            ],
        ];

        $this->createFundingRounds($company, $rounds, $investors);
    }

    private function seedSpaceXFunding(array $investors): void
    {
        $company = Company::where('slug', 'spacex')->first();
        if (! $company) {
            return;
        }

        $rounds = [
            [
                'round_type' => 'series_a',
                'amount' => 100000000,
                'announced_date' => '2008-08-01',
                'investors' => ['Founders Fund'],
            ],
            [
                'round_type' => 'series_d',
                'amount' => 200000000,
                'announced_date' => '2012-06-01',
                'investors' => ['Founders Fund', 'Dragoneer Investment Group'],
            ],
            [
                'round_type' => 'series_e',
                'amount' => 1000000000,
                'announced_date' => '2015-01-01',
                'investors' => ['Google', 'Fidelity Investments'],
            ],
            [
                'round_type' => 'series_h',
                'amount' => 500000000,
                'announced_date' => '2019-05-01',
                'investors' => [],
            ],
            [
                'round_type' => 'series_i',
                'amount' => 1900000000,
                'announced_date' => '2020-08-01',
                'investors' => ['Sequoia Capital', 'Fidelity Investments', 'Founders Fund'],
            ],
            [
                'round_type' => 'series_j',
                'amount' => 1160000000,
                'announced_date' => '2021-02-01',
                'pre_money_valuation' => 74000000000,
                'investors' => ['Sequoia Capital', 'Valor Equity Partners', 'Coatue Management', 'Fidelity Investments'],
            ],
            [
                'round_type' => 'series_j_extension',
                'amount' => 1680000000,
                'announced_date' => '2022-06-01',
                'pre_money_valuation' => 127000000000,
                'investors' => [],
            ],
            [
                'round_type' => 'series_k',
                'amount' => 750000000,
                'announced_date' => '2023-01-01',
                'pre_money_valuation' => 137000000000,
                'investors' => ['Andreessen Horowitz'],
            ],
            [
                'round_type' => 'secondary',
                'amount' => 1250000000,
                'announced_date' => '2024-12-01',
                'pre_money_valuation' => 350000000000,
                'investors' => [],
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
                [
                    'company_id' => $roundData['company_id'],
                    'announced_date' => $roundData['announced_date'],
                    'round_type' => $roundData['round_type'] ?? null,
                ],
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
