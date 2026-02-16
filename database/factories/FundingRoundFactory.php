<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\FundingRound;
use Illuminate\Database\Eloquent\Factories\Factory;

class FundingRoundFactory extends Factory
{
    protected $model = FundingRound::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'round_type' => fake()->randomElement(['Seed', 'Series A', 'Series B', 'Series C']),
            'amount' => fake()->randomFloat(2, 1000000, 500000000),
            'currency' => 'USD',
            'announced_date' => fake()->dateTimeBetween('-3 years', 'now'),
        ];
    }
}
